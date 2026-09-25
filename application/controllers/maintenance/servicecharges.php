<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ServiceCharges extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(SVCCHARGES_NO);
    
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
    $this->load->model('coresys/misc_model');
    $this->load->model('coreapp/card_model');
    
    $brcode = $this->core->getBranchCode();
    
    $result = $this->misc_model->getTermListForFeeList($brcode);
    
    $data['termList'] = '<option value="zzzz">zzzz - GLOBAL</option>';
    
    if ($result->num_rows() > 0) {
      foreach ($result->result_array() as $row) {
        $data['termList'] .= '<option value="'. $row['termcode'] .'">'. $row['termcode'] .' - '. $row['description'] .'</option>';
      }
    }

    $result->free_result();
    $result->next_result();

    //account types
    $result = $this->card_model->getCardTypeFees();
    
    $accountTypes = '';//<option value="0">ALL</option>';
    
    //if ($this->core->isISOCustomer()) {
      if ($result->num_rows() > 0) {
        foreach ($result->result_array() as $row) {
          $accountTypes .= '<option value="'. $row['accttype'] .'">'. $row['description'] .'</option>';
        }
      } else {
        $accountTypes = '<option value="">No Data Defined</option>';
      }
    //}
    
    $data['accountTypes'] = $accountTypes;
    
    $row = $result->row_array();
    $firstAcctType = $row['accttype'];
    
    $result->free_result();
    $result->next_result();

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Service Charge List</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/servicecharges', $data);
  }
  
  function getData()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coresys/misc_model');
    $this->load->library('shortxml');
    
    $xml = $this->shortxml;
    
    $termCode = $this->input->get('termCode', TRUE);
    $accttype = $this->input->get('accttype', TRUE);
    
    $result = $this->misc_model->getFeeList($termCode, $accttype);
    
    $details = array();
    $resultArr = $result->result_array();
    if ($result->num_rows() > 0) {
      foreach ($resultArr as $row) {
        
        $brseqno = $row['brseqno'];
        // Keep an identifiable fallback if this branch is missing from the cache/list.
        $branch = $brseqno;
        
        $xml->setXML($row['servdesc']);
        
        if ($row['brseqno'] === '9999') {
          $branch = '(Global)';
        } else {
          //branches
          if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
            $this->load->model('coreapp/branch_model');
            $result = $this->branch_model->getBranchList();
          
            $branches = $result->result_array();
            $this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
          }
    
          foreach ($branches as $b) {
            if ($brseqno == $b['brseqno']) {
              $branch = $b['brname'];
              break;
            }
          }
          $result->free_result();
          $result->next_result();
        }
        
        $details[] = array(
          //hidden
          $row['feeseqno'],
          $brseqno,
          $row['servtype'],
          $row['feetype'],
          $row['authname'],
          $row['nettype'],
          $row['chargetype'],
          $row['trxcode2'],
          //visible
          $row['mnemonic'],
          $branch,
          $this->core->currency($row['feeval']),
          $row['termtype'],
          $xml->getValue('DESC'),
          $row['netdesc'],
          $row['trandesc'],
          $row['feetypedesc'],
          $row['chargedesc'],
          $this->core->currency($row['minrange']),
          $this->core->currency($row['maxrange']),
          $row['description'],
          $row['accttype']
        );
      }
    }
    
    $result->free_result();
    $result->next_result();
    
    echo json_encode(array(
      'success' => TRUE,
      'details' => $details
    ));
  }
  
  function cache()
  {
    $_SESSION['serviceCharge'] = $_POST;
    
    echo json_encode(array(
      'success' => TRUE
    ));
  }
  
  function cache2()
  {
    $_SESSION['scAccttype'] = $this->input->post('accttype',TRUE);
    
    echo json_encode(array(
      'success' => TRUE,
      'accttype'=> $_SESSION['scAccttype']
    ));
  }
  
  function delete()
  {
    $this->load->model('coresys/misc_model');
    
    $core = $this->core;
    
    $feeseqno = $this->input->post('feeseqno', TRUE);
    $brseqno = $core->getBranchID();
    $ipAddress = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $userID = $core->getUserID();
    $override = '';
    
    $result = $this->misc_model->deleteFee(
      $feeseqno,
      $brseqno,
      $ipAddress,
      $workstation,
      $userID,
      $override
    );
    
    $success = TRUE;
    $message = 'Service Charge removed successfully';
    
    $row = $result->row_array();
    
    if ($row['errno'] > 0) {
      $success = FALSE;
      $message = $row['errmsg'];
    }
    
    $result->free_result();
    $result->next_result();
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }
}
