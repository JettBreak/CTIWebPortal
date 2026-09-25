<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AllowsSetup extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    
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
    //$this->core->checkUserAllows(GENERALSETTINGS_NO);
  }
  
  function index()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/allows_model');
    
    //product Types
    if (!$productTypes = $this->cache->get($this->core->getSessionID() . 'prTypes')) {      
      $result = $this->allows_model->getProductTypes();
      $productTypes = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      
      $this->cache->save($this->core->getSessionID() .'prTypes', $productTypes, CACHE_TTL);
    }
    
    $prTypes = NULL;
    if (count($productTypes) > 0) {
      foreach ($productTypes as $row) {
        $prTypes .= '<option value="'. $row['prtype'] .'">'. $row['description'] .'</option>';
      }
    } else {
      $prTypes = '<option value="">No Product Types Defined</option>';
    }
    $data['prTypes'] = $prTypes;
    //end

    $this->load->model('coreapp/card_model');

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Allows Setup List</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/allowssetup', $data);
  }
  
  function cache()
  {
    $_SESSION['allows'] = $_POST;
    
    echo json_encode(array(
      'success' => TRUE
    ));
  }
  
  function getData()
  {
    $this->load->model('coreapp/allows_model');
    
    $prType = $this->input->get('prType', TRUE);
    $result = $this->allows_model->getAllowsSetup($prType);
    
    $details = array();
    $success = FALSE;
    $allowRows = $result->result_array();
    
    if (count($allowRows) > 0) {
      $success = TRUE;
      foreach ($allowRows as $row) {
        $details[] = array(
          $row['bitno'],
          $row['description'],
          $row['trxcode']
        );
      }
    }
    
    //get last bitNo
    $lastAllow = end($allowRows);
    $_SESSION['lastBitNo'] = $lastAllow === FALSE ? 0 : $lastAllow['bitno'];
    //end
    
    $result->free_result();
    $result->next_result();
      
    echo json_encode(array(
      'success' => $success,
      'details' => $details
    ));
  }
  
  function delete()
  {
    $this->load->model('coreapp/allows_model');
    
    $prtype = $this->input->post('prType', TRUE);
    $trxcode = $this->input->post('trxcode', TRUE);
    $brseqno = $this->core->getBranchID();
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
    
    $result = $this->allows_model->deleteAllowsSetup($prtype, $trxcode, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID);
    
    $row = $result->row_array();
    
    if (intval($row['errno']) > 0) {
      $success = FALSE;
      $message = $row['errmsg'];
    } else {
      $success = TRUE;
      $message = 'Removed successfully';
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }
}
