<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SetPW2 extends CI_Controller {
  private $userID;
  
  function __construct()
  {
    parent::__construct();
    
    //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    if (isset($_SESSION['userIDx']) && !empty($_SESSION['userIDx'])) {
      $this->userID = $_SESSION['userIDx'];
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
    $this->load->view('setpw2', $data);
  }
  
  function submit()
  {
    //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/user_model');
    $this->load->library('shortxml');
    $this->load->library('core');
    
    $xml = $this->shortxml;
    $userID = $_SESSION['userIDx'];//$this->userID;
    if (!isset($_SESSION['userBranchIDx']) || !isset($_SESSION['userGroupSeqnox'])) {
      echo json_encode(array(
        'success' => FALSE,
        'message' => 'Your password reset session has expired. Please log in again.'
      ));
      return;
    }

    $brseqno = $_SESSION['userBranchIDx'];
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $grpseqno = $_SESSION['userGroupSeqnox'];
    $userAudit = '';
    $sessionID = '';
    $userPW = $this->core->encrypt($userID, $this->input->post('newPassword', TRUE));
    
    $result = $this->user_model->getUserPassCycleCount($userID, $grpseqno);
    $row = $result->row_array();

    $result->free_result();
    $result->next_result();

    if ($_SESSION['sysPwd'] == $this->input->post('newPassword', TRUE)) {
      echo json_encode(array(
        'success' => FALSE,
        'message' => 'You are not allowed to use the system generated password as your new password. Please try again.'
      ));
      exit();
    }

    $userXML = '';
    if (COUNT($row) == 0) {
      $passCycle = 0;
    } else {
      $xml->setXML($row['grpXML']);
      $passCycle = $xml->getValue('PWCYCLE');

      $xml->setXML($row['userXML']);
    }

    $success = TRUE;
    $pwArr = array();
    for ($i = 1; $i < $passCycle; $i++) {
      $pwSet = 'PWSET'.$i;
      if ($userPW == $xml->getValue($pwSet)) {
        $success = FALSE;
        $message = 'Password has already been used. Please enter a new password.';

        break;
      }
      $pwArr[] = $xml->getValue($pwSet);
    }

/*    if (COUNT($pwArr) == COUNT($passCycle)) {
      array_pop($pwArr);
    }
    
    array_unshift($pwArr, $xml->getValue('WEBPWD'));*/

    $pwXML = '';

    for ($i = 1; $i < $passCycle; $i++) {
      if ($i > COUNT($pwArr)) {
        $pwXML .= '<PWSET'.$i.'></>';
      } else {
        $idx = $i - 1;
        $pwXML .= '<PWSET'.$i.'>'.$pwArr[$idx].'</>';
      }
    }
    
    if ($success) {
      $result = $this->user_model->setUserPass($userID, $userPW, NULL, FALSE, '', '', FALSE, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID, $grpseqno, $pwXML);
      $row = $result->row_array();

      $result->free_result();
      $result->next_result();
    }

    //$this->cache->clean();
    if ($success) {
      if ($row['errno'] == 0) {
        unset($_SESSION['userIDx']);
        unset($_SESSION['userBranchIDx']);
        unset($_SESSION['userGroupSeqnox']);
        unset($_SESSION['sysPwd']);
      } else {
        $success = FALSE;
        $message = $row['errmsg'];
      }
    }
    
    echo json_encode(array(
      'success' => isset($success) ? $success : TRUE,
      'message' => isset($message) ? $message : ' User information updated'
    ));
  }
}
