<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CardIssuanceApproval extends CI_Controller {
  
  private $fileVersion = '1.10.00';
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(ACCNTVERIFY_NO);
    
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
  }
  
  function index()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/card_model');
    
    //get branches
    if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
      $this->load->model('coreapp/branch_model');
      $result = $this->branch_model->getBranchList();
    
      $branches = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      $this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
    }
    
    $data['branches'] = NULL;
    
    if (count($branches) > 0) {
      foreach ($branches as $row) {
        $selected = NULL;
        //if user branch is not allowed to monitor users from other branches
        if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
          $data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
          break;
        }

        if ($row['brseqno'] === $this->core->getBranchID()) {
          $selected = ' selected';
        }
        $data['branches'] .= '<option value="'. $row['brseqno'] .'"'.$selected.'>'. $row['brname'] .'</option>';
      }
    } else {
      $data['branches'] = '<option value="">No Branches Defined</option>';
    }
    
    if ($this->core->isHeadOffice()) {
      $data['uiToolbar'] = "$('.ui-toolbar:even').append($('#customToolbar .top').html());";
    } else {
      $data['uiToolbar'] = NULL;
    }

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Card Issuance Approval</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    
    $this->load->view('card/cardissuanceapproval', $data);
  }
  
  function getData()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/card_model');
    $this->load->library('shortxml');
    
    $core   = $this->core;
    $card     = $this->card_model;
    $xmldata  = $this->shortxml;
    
    if ($this->core->isHeadOffice()) {
      $brseqno = $this->input->get('brseqno', TRUE);
    } else {
      $brseqno = $this->core->getBranchID();
    }
    
    $ipAddress    = $this->core->getIPAddress();
    $workstation  = $this->core->getWorkstation();
    $userAudit    = $this->core->getUserID();
    $override     = '';
    $sessionID    = $this->core->getSessionID();
    $coreencrypt  = $core->isCoreEncrypt();

    $result = $card->getCardIssuanceForApproval($coreencrypt,$brseqno,$userAudit,$sessionID);

    $details = array();
    if ( count($result->result_array()) > 0 )  {
      foreach ($result->result_array() as $row) {

        $cardno   = $row['cardno'];
        $cardtype = $row['cardtype'];
        $carddesc = $row['carddesc'];
        $tokenid = $coreencrypt ? $row['TokenID'] : 0;

        $customer = $row['lastname'].', '.$row['firstname'].' '.$row['middlename'];
        $prseqno  = $row['prseqno'];

        $details[] = array(
          '<input type="checkbox" name="cards[]" id="'. $prseqno .'" value="'. $prseqno .'"/>',
          $cardno,
          $carddesc,
          $customer,
          $tokenid
        );

      }
      $success = TRUE;
    } else {
      $success = FALSE;
    }

    echo json_encode(array(
      'success' => $success,
      'details' => $details
    ));
  }

  function verify()
  {
    $this->load->model('coreapp/card_model');
    $this->load->library('coreconverters');
    
    $card  = $this->card_model;
    $core  = $this->core;
    $input = $this->input;

    $cards   = $input->post('cards');
    $isPrimary   = $input->post('brancList');

    $brseqno = $this->core->getBranchID();

    $success = TRUE;
    $message = 'Card/s successfully activated';
    
    $ipAddress   = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $userAudit   = $core->getUserID();
    $sessionID   = $core->getSessionID();

    $errcnt = 0;
    foreach ($cards as $prseqno) {
      $status      = 11;//WDB is 4; //for verification

      $result = $card->approveCardIssuance(
          $prseqno,
          $status,
          $ipAddress,
          $workstation,
          $userAudit,
          $sessionID
        );

        $row = $result->row_array();

        //if (intval($row['errno']) > 0) {
        //  $errcnt++;
        //}

        $result->free_result();
        $result->next_result();     

        $result = $card->getCurrentCardInfo($prseqno);
        $row = $result->row_array();
        
        $auditXML = '';

        $auditXML .= '<old></><new></><field>Card Issuance Approval</><details>Card No: '.$row['prkey'].'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      

        $result->free_result();
        $result->next_result();  
    }
    
    if ($errcnt > 0) {
      $message = 'An error has occured';
      $success = FALSE;
    }

    $this->session->unset_userdata('userOverride');
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'cards'   => $cards
    ));
  }
  
}