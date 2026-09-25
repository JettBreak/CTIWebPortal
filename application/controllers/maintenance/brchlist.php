<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Brchlist extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->load->model('coreapp/branch_model');
    $this->core->checkUserAllows(BRANCH_NO);
    
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

    $auditXML .= '<old></><new></><field>Branch List</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/brchlist', $data);
  }
  
  function cache()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->cache->delete($this->core->getSessionID() .'brchlist');
    
    $brchlist = array(
      'brchCode' => $this->input->post('code', TRUE),
      
      'brchName' => $this->input->post('name', TRUE) ,
      'brchId' => $this->input->post('id', TRUE),
      'brchSeq' => $this->input->post('seq', TRUE),
      'address' => $this->input->post('addr', TRUE),
      'telNo' => $this->input->post('tel', TRUE),
      'areaname' => $this->input->post('area', TRUE),
      'headOfc' => $this->input->post('head', TRUE),
      'isRep' => $this->input->post('isRep', TRUE),
      'isMon' => $this->input->post('isMon', TRUE),
      'isUser' => $this->input->post('isUser', TRUE)
    );
    
    $success = $this->cache->save($this->core->getSessionID() .'brchlist', $brchlist, CACHE_TTL);
    
    echo json_encode(array(
      'success' => $success
    ));
  }
  
  function getData()
  {
    $this->load->library('shortxml');
    
    $xml = $this->shortxml;
    
    $result = $this->branch_model->getBranchList();
    
    $details = array();
    foreach ($result->result_array() as $row) {
      
      // GET DATA 
      $xml->setXML($row['xml1']);
      $details[] = array(
        $row['brcode'],
        $row['brname'],
        $row['brid'],
        $row['brseqno'],
        $row['address'],
        $row['telno'],
        $row['regioncode'],
        $row['ishead'],
        $xml->getValue('ISREP'),
        $xml->getValue('ISMON'),
        $xml->getValue('ISUSER')
      );
    }
    
    echo json_encode(array(
      'success' => TRUE,
      'details' => $details
    ));
  }
  
  function delete()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    
    $brseq       = $this->input->post('brchSeq', TRUE);
    $brcode      = $this->input->post('brchCode', TRUE);
    $brseqno     = $this->core->getBranchID();
    $ipaddress   = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit   = $this->core->getUserID();
    $sessionID   = $this->core->getSessionID();
   
    $result = $this->branch_model->deleteBranch(
      $brseq,
      $brcode,
      $brseqno,
      $ipaddress,
      $workstation,
      $userAudit,
      $sessionID
    );
    
    $row = $result->row_array();
    
    $success = TRUE;
    
    $this->cache->delete($this->core->getSessionID() .'branches');
    
    if (intval($row['errno']) > 0) {
      $success = FALSE;
    }
    
    echo json_encode(array(
      'removed' => $success,
      'errno' => $row['errno'],
      'message' => $row['errmsg']
    ));
  }
  
  function getcardlist()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/card_model');
    $this->load->library('zip');
    
    $data1 = 'data1xxdata';
    $data2 = 'data2xxdata';

    $card      = $this->card_model;
    $core    = $this->core;
    
    $userAudit = $core->getUserID();
    $sessionID = $core->getSessionID();
    
    $result = $card->checkLogin($userAudit, $sessionID);
    $row = $result->row_array();
    
    $result->free_result();
    $result->next_result();

    $result = $card->exportcardacct(); 
    $resultx = $result->result_array();

    $refno = '';
    $data2 = '';
    foreach ($resultx as $row) {
      $cardx = $row['cardno'];
      $acctref = $row['acct'];
      
      $acctx = $card->getacctdtlx($acctref);
      $rowx = $acctx->row_array();


      $acctx->free_result();
      $acctx->next_result();
      
      if (!isset($rowx['acctno'])) {
        continue;
      } else {
        $acctno = $rowx['acctno'];
      }

      if($refno != $row['refno']) {
        $data2 .= "[".$row['refno']."] :\r\n\r\n";
        $data2 .= "= ".$acctno."\r\n\r\n";
      } else {
        $data2 .= "= ".$acctno."\r\n\r\n";
      }
      $refno = $row['refno'];
    }

    $result->free_result();
    $result->next_result();

    $result = $card->exportcardlist(); 
    $resultx = $result->result_array();

    $data1 = '';
    foreach ($resultx AS $row) {
      $card = $row['cardno'];
      $customer = $row['custname'];
      $branch = $row['brname'];
      $refno = $row['refno'];

      $data1 .= $card.','.$refno.','.$customer.','.$branch."\r\n";
    }

    $data = array(
      'cardlist'.date("Ymd").'.csv' => $data1,
      'cardacct'.date("Ymd").'.txt' => $data2
    );
    
    $this->zip->add_data($data);
    $this->zip->download('CARDLIST'. date("Ymd") .'.zip');
  }
}