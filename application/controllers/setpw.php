<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SetPW extends CI_Controller {
  private $userID;
  
  function __construct()
  {
    parent::__construct();
    
    //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
      $this->userID = $_SESSION['userID'];
    } else {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'You are trying to access a forbidden page'
      ));
      exit();
    }
  }
  
  function index()
  {
    $data['minChar'] = $_SESSION['minChar'];
    $this->load->view('setpw', $data);
  }
  
  function submit()
  {
    //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/user_model');
    $this->load->library('core');
    
    $userPW = $this->core->encrypt($this->userID, $this->input->post('newPassword', TRUE));
    $question = $this->input->post('secretQuestion', TRUE);
    $answer = $this->core->encrypt($this->userID, $this->input->post('secretAnswer', TRUE));

    if ($_SESSION['sysPwd'] == $this->input->post('newPassword', TRUE)) {
      echo json_encode(array(
        'success' => FALSE,
        'message' => 'You are not allowed to use the system generated password as your new password. Please try again.'
      ));
      exit();
    }

    $result = $this->user_model->setInitUserPass(
      $this->userID,
      $userPW,
      $question,
      $answer
    );
    
    $result->free_result();
    $result->next_result();
    
    //$this->cache->clean();
    unset($_SESSION['userID']);
    unset($_SESSION['sysPwd']);
    
    echo json_encode(array(
      'success' => TRUE,
      'message' => 'User information updated'
    ));
  }
}