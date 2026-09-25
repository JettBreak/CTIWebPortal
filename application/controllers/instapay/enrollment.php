<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Enrollment extends CI_Controller {

	private $fileVersion = '1.10.00';
  private $coreencrypt = FALSE;

	function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(IPAY_CARDENRL);

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

  function newIPayCard()
  {
    $success = TRUE;
    $message = '';
    $errors = array();

    $this->load->model('coreapp/ipay_model');
    $this->load->library('coreconverters');
    $core = $this->core;

    try{

      $input = $this->input;
    
      $cardBIN  = $input->post('cardBIN', TRUE);
      $cardNo   = $input->post('cardNo', TRUE);

      $prkey    = $cardBIN . $cardNo;
      $prType   = $input->post('prType', TRUE);
      $acctType = $input->post('acctType', TRUE);

      $merchantID = $input->post('merchantID');
      $mallID  = $input->post('mallID');

      $userID  = $input->post('userID');
      $userPwd = $input->post('userPwd');

      $cifseqno = 0;
      $emboss = '';
      
      $allows = $this->input->post('allows');
      
      if ($allows) {
        $max = max($allows);
        $bin = '';
        for ($i = 1; $i <= $max; $i++) {  
          if (in_array($i, $allows)) {
            $bin .= '1';
          } else {
            $bin .= '0';
          }
        }
      } else {
        $bin = '0';
      }

      $hex = $this->coreconverters->asciiBinToHex($bin);

      $xml = NULL;
      $fCash = 0; //format 0.00
      $fType = NULL;
      $fPtr = 0;

      //if INST has card product code
      if ($this->core->hasProductCode()) {
        $prcdcode = $input->post('productCode', TRUE);
        
        $xml .= '<PRCDCODE>'. $prcdcode .'</>';
      }
      
      if ($core->isHeadOffice()) {
        $brseqno = $input->post('brseqno', TRUE);
      } else {
        $brseqno = $core->getBranchID();
      }

      $override    = '';

      $ipAddress   = $core->getIPAddress();
      $workstation = $core->getWorkstation();
      $userAudit   = $core->getUserID();
      $sessionID   = $core->getSessionID();

      $result = $this->ipay_model->insertIPayCard(
        $core->isCoreEncrypt(),
        $cardBIN,
        $prkey,
        $prType,
        str_pad($acctType,2,'0',STR_PAD_LEFT),
        intval($cifseqno),
        $emboss,
        $hex,
        $fCash,
        $fType,
        $fPtr,
        $xml,
        $brseqno,
        $ipAddress,
        $workstation,
        $userAudit,
        $override,
        $sessionID
      );

      $row = $result->row_array();
      $result->free_result();
      $result->next_result();

      if ($row['errno'] > 0) {
        throw new Exception(isset($row['errmsg']) ? $row['errmsg'].'+'.$brseqno.':'.$row['errno'] : 'An error has occured.');
      }

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYMERC',$merchantID, NULL
      );

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYMALL',$mallID, NULL
      );

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYUSER',$userID, NULL
      );

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYPASS',$this->core->encrypt($userID, $userPwd), NULL
      );

      $message = 'Instapay Card successfully enrolled.<br/>'; //Please verify your Card Number

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
      'isCoreEncrypt' => $core->isCoreEncrypt(),
      'prkey' => $prkey,
      'hex' => $hex,
      'xml' => $xml
    ));
  }

  function updateIPayCard()
  {
    $success = TRUE;
    $message = '';
    $errors = array();

    $this->load->model('coreapp/ipay_model');
    $this->load->library('coreconverters');
    $core = $this->core;

    try{

      $input = $this->input;
    
      $cardBIN  = $input->post('cardBIN', TRUE);
      $cardNo   = $input->post('cardNo', TRUE);

      $prkey    = $cardBIN . $cardNo;
      $prType   = $input->post('prType', TRUE);
      $acctType = $input->post('acctType', TRUE);

      $merchantID = $input->post('merchantID');
      $mallID  = $input->post('mallID');

      $cifseqno = 0;
      $emboss = '';
      
      $allows = $this->input->post('allows');
      
      if ($allows) {
        $max = max($allows);
        $bin = '';
        for ($i = 1; $i <= $max; $i++) {  
          if (in_array($i, $allows)) {
            $bin .= '1';
          } else {
            $bin .= '0';
          }
        }
      } else {
        $bin = '0';
      }

      $hex = $this->coreconverters->asciiBinToHex($bin);

      $xml = NULL;
      $fCash = 0; //format 0.00
      $fType = NULL;
      $fPtr = 0;

      $override    = '';

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYMERC',$merchantID, NULL
      );

      $this->ipay_model->insertConfigxx(
        'zzzz','IPAYMALL',$mallID, NULL
      );

      $message = 'Instapay Card successfully updated.<br/>'; //Please verify your Card Number

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
      'isCoreEncrypt' => $core->isCoreEncrypt(),
      'prkey' => $prkey,
      'hex' => $hex,
      'xml' => $xml
    ));
  }


}