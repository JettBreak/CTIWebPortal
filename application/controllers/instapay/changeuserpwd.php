<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChangeUserPwd extends CI_Controller {

  private $fileVersion = '1.10.00';

  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(IPAY_CARDCHANGEUSERPWD);

    $this->coreencrypt = $this->core->isCoreEncrypt();

    $this->load->model('coreapp/user_model');

    $result = $this->user_model->checkLogin($this->core->getUserID(), $this->core->getSessionID());

    $row = $result->row_array();

    if (intval($row['errno']) > 0) {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'Invalid Login Session. Please relogin'
      ));
      exit();
    }

  }

  function index()
  {
    $this->load->library('version');

    $file = basename(__DIR__) . '/' . basename(__FILE__);

    $verified = $this->version->validate($file, $this->fileVersion);

    if (!$verified) {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'Module is out of date. Please contact software administrator.'
      ));
      exit();
    }

    //load cache driver
    $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'apc'));

    //load model
    $this->load->model('coreapp/card_model');
    $this->load->model('coreapp/ipay_model');
    $this->load->library('coreconverters');

    //declare loaded model
    $ipay   = $this->ipay_model;
    $cache  = $this->cache;

    $data = array();
    $data['isHeadOffice'] = $this->core->isHeadOffice();
   
    $result   = $ipay->getConfigxx('IPAYUSER');
    $userInfo = $result->row_array();
    $result->free_result();
    $data['userID'] = $userInfo['description'];

    $auditXML = '';
    $brseqno     = $this->core->getBranchID();
    $userAudit   = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Instapay Card Change User and Password</><details>Open Module</>';
    $this->ipay_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'IPAY','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    $data['minChar'] = $this->core->getPasswordMinChar();

    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('instapay/changeuserpwd', $data);
  }

  function verify()
  {
    $success = TRUE;
    $message = '';
    $errors = array();

    $this->load->model('coreapp/ipay_model');
    $this->load->library('coreconverters');
    $core = $this->core;

    $input = $this->input;

    $ipay  = $this->ipay_model;
    $result  = $ipay->getIPayCardParameters();
    $ipayParams = $result->row_array();
    $result->free_result();
    $result->next_result();

    $currentPW = $ipayParams['userpass'];
    $xxx = $this->core->encrypt($ipayParams['userid'], $this->input->post('currentPW', TRUE));

    if ($xxx != $currentPW) {
      $verified = 0;
    } else{
      $verified = 1;
    }

    echo $verified;
  }

  function submit()
  {
    $success = TRUE;
    $message = '';
    $errors = array();

    $this->load->model('coreapp/ipay_model');
    $this->load->library('coreconverters');
    $core = $this->core;

    try{

      $input = $this->input;
      
      $ipay  = $this->ipay_model;
      $result  = $ipay->getIPayCardParameters();
      $ipayParams = $result->row_array();
      $result->free_result();
      $result->next_result();

      $userPwd = $input->post('newPassword');

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYUSER',$ipayParams['userid'], NULL
      );

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYPASS',$this->core->encrypt($ipayParams['userid'], $userPwd), NULL
      );

      $message = 'Instapay Card Password successfully updated.<br/>'; //Please verify your Card Number

    }catch(Exception $e){
      $errors[] = $e->getMessage();
    }

    if(count($errors) > 0){
      $success = FALSE;
      $message = implode(',',$errors);
    }

    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'isCoreEncrypt' => $core->isCoreEncrypt()
    ));
  }
}