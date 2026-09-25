<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SecurityKey extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(ATMKEYMGMT_NO);
    
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
    $this->load->model('coreapp/card_model');

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Security Key Management</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/securitykey',$data);
  }
  
  function cache()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->cache->delete($this->core->getSessionID() .'atmKey');
    
    $input = $this->input;
    
    $atmKey['nodeName'] = $input->post('nodeName', TRUE);
    $atmKey['secCode'] = $input->post('secCode', TRUE);
    $atmKey['encMode'] = $input->post('encMode', TRUE);
    $atmKey['inVariant'] = $input->post('inVariant', TRUE);
    $atmKey['inMKey'] = $input->post('inMKey', TRUE);
    $atmKey['inWKey'] = $input->post('inWKey', TRUE);
    $atmKey['inEncType'] = $input->post('inEncType', TRUE);
    $atmKey['outVariant'] = $input->post('outVariant');
    $atmKey['outMKey'] = $input->post('outMKey', TRUE);
    $atmKey['outWKey'] = $input->post('outWKey', TRUE);
    $atmKey['outEncType'] = $input->post('outEncType', TRUE);
    $atmKey['xml'] = $input->post('xml');
    
    if ($this->cache->save($this->core->getSessionID() .'atmKey', $atmKey, CACHE_TTL)) {
      $success = TRUE;
    } else {
      $success = FALSE;
    }
    
    echo json_encode(array(
      'success' => $success
    ));
  }
  
  function getData()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coresys/atm_model');
    
    $result = $this->atm_model->getSecurityKeyList();
    
    $details = array();
    if ($result->num_rows() > 0) {
      
      $keyList = $result->result_array();
      
      if (!$encTypes = $this->cache->get($this->core->getSessionID() . 'encTypes')) {
        $result->free_result();
        $result->next_result();
        
        $result = $this->atm_model->getEncryptionType();
        $encTypes = $result->result_array();
        $this->cache->save($this->core->getSessionID() .'encTypes', $encTypes, CACHE_TTL);
      }
      
      
      foreach ($keyList as $row)
      {
        //get account code (TY)
        $encType = $row['enctype1'];
        $encDesc = NULL;
        foreach ($encTypes as $enc) {
          if ($enc['codevalue'] === $encType) {
            $encDesc = $enc['xml1'];
          }
        }
        //end
        $details[] = array(
          $row['seccode'],
          $encDesc,
          $row['description'],
          $row['encmode'],
          $row['pekvariant'],
          $row['pek1'],
          $row['pek2'],
          $row['enctype1'],
          $row['kekvariant'],
          $row['kek1'],
          $row['kek2'],
          $row['enctype2'],
          $row['xml1'],
          $row['nodename']
        );
      }
    }
    
    echo json_encode(array(
      'success' => TRUE,
      'details' => $details
    ));
  }
  
  function remove()
  {
    $this->load->model('coresys/atm_model');
    
    $secCode = $this->input->post('secCode', TRUE);
    
    $this->atm_model->deleteSecurityKey($secCode);
    
    echo json_encode(array(
      'removed' => TRUE/*,
      'secCode' => $secCode*/
    ));
  }
}