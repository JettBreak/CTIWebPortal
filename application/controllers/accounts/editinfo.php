<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EditInfo extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    if ($this->core->checkUserAllowsBtn(ACCNTEDIT_NO)) {
      $this->core->checkUserAllows(ACCNTEDIT_NO);
    } elseif ($this->core->checkUserAllowsBtn(ACCTUPDATE_NO)) {
      $this->core->checkUserAllows(ACCTUPDATE_NO);
    }

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
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    
    $info = $_SESSION['accountInfo'];
    if (!$info) {
      $this->load->helper('url');
      redirect('welcome');
      exit();
    }
    
    $this->load->model('coreapp/card_model');
    $this->load->library('coreconverters');
    
    $data['cifseqno'] = $info['cifseqno'];
    $data['branchName'] = $info['brname'];
    $data['brseqno'] = $info['brseqno'];
    $data['accountNo'] = $info['prkey'];
    $data['accountDesc'] = strtoupper($info['acctdesc']);
    $data['owner'] = $info['cifseqno'] ? $info['lastname'] .', '. $info['firstname'] .' '. $info['middlename'] : 'NONE';
    $data['authMode'] = strtoupper($info['authmode']);
    $data['accountStat'] = strtoupper($info['statdesc']);
    $data['status'] = $info['status'] != 8 ? 'disabled':NULL;
    
    //tran allows   
    $result = $this->card_model->getDefaultTranAllows('ACCT');
    
    $data['tranAllows'] = NULL;
    
    $allows = $this->coreconverters->asciiHexToBin($info['allows']);
    
    foreach ($result->result_array() as $row) {
      $checked = substr($allows, $row['bitno'] - 1, 1) === '1' ? ' checked' : NULL;
      $data['tranAllows'] .= '<input type="checkbox" id="bit'. $row['bitno'] .'" name="allows[]" value="'. $row['bitno'] .'"'. $checked .' disabled />'. $row['description'] .'<br />';
    }
    
    $result->free_result();
    $result->next_result();
    //end

    //get branches
    //if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
    $this->load->model('coreapp/branch_model');
    $result = $this->branch_model->getBranchList();

    $branches = $result->result_array();
    
    $result->free_result();
    $result->next_result();
    //$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
    //}
    
    $data['branches'] = NULL;
    $currentBrCode = NULL;
    
    if (count($branches) > 0) {
      foreach ($branches as $row) {
        //if user branch is not allowed to monitor users from other branches
        $matched = $row['brseqno'] === $data['brseqno'] ? TRUE : FALSE;
        
        if (!$this->core->isHeadOffice() && $matched) {
          $data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
          break;
        }
        
        if ($matched) {
          $selected = ' selected';
          $currentBrCode = $row['brcode'];
        } else {
          $selected = NULL;
        }
        
        $data['branches'] .= '<option value="'. $row['brseqno'] .'"'. $selected .'>'. $row['brname'] .'</option>';
      }
    } else {
      $data['branches'] = '<option value="">No Branches Defined</option>';
    }
    //end
    
    $result = $this->card_model->getAcctCardLink($info['prseqno']);
    
    $cardLink = array();
    $cardsLinked = NULL;
    
    foreach ($result->result_array() as $row) {
      //add zeros to cifseqno
      /*$len = 8 - strlen($row['cifseqno']);
      $cifseqno = NULL;
      for ($i = 1; $i <= $len; $i++) {
        $cifseqno .= '0'; 
      }
      $cifseqno .= $row['cifseqno'];*/
      //end
      
      /*$cardLink[] = array(
        $row['prkey'],
        $cifseqno,
        $row['description']
      );*/
      $cardsLinked .= '<tr>'.
        '<td>'. $row['prkey'] .'</td>'.
        '<td>'. $row['cifseqno'] .'</td>'.
        '<td>'.$row['description'] .'</td>'.
      '</tr>';
    }
    
    $data['cardsLinked'] = $cardsLinked;


    $allowUpdate = $this->core->checkUserAllowsBtn(ACCTUPDATE_NO);

    if ($allowUpdate) {
      $data['title'] = 'Update Account Information';
      $data['allowUpdate'] = NULL;
    } else {
      $data['title'] = 'View Account Information';
      $data['allowUpdate'] = 'disabled';
    }

    $data['formAction'] = 'accounts/editinfo/submit';
    $data['accntDel'] = 'accounts/editinfo/delete';
      
    //$this->output->cache(CACHE_TTL);
      $auditXML = '';
      $brseqno = $this->core->getBranchID();
      $userAudit = $this->core->getUserID();
      $workstation = $this->core->getWorkstation();

      $auditXML .= '<old></><new></><field>Account Information</><details>Account No: '.$data['accountNo'].'</>';
      $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'ACCT','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('accounts/editinfo', $data);
  }
  
  function getFormat($formatValue, $brChar)
  {
    $branchCode = $this->core->getBranchCode();
    
    $padCnt = substr_count($formatValue, $brChar);
    $get = str_repeat($brChar, $padCnt); //BBBBBB
    $brVal = str_pad($branchCode, $padCnt, '0', STR_PAD_LEFT);
    $newFormat = str_replace($get, $brVal, $formatValue);
    
    return $newFormat;
  }
  
  function submit()
  {
    $this->load->model('coreapp/card_model');
    $this->load->library('coreconverters');
    
    $card  = $this->card_model;
    $core  = $this->core;
    $input = $this->input;
    
    $info = $_SESSION['accountInfo'];
    if (!$info) {
      echo json_encode(array(
        'success' => FALSE,
        'message' => 'An error has occured'
      ));
      exit;
    }
    
    $prseqno   = $info['prseqno'];
    $cifseqno  = $input->post('cifseqno', TRUE);
    $oldcif    = $info['cifseqno'];
    $brseqno     = $input->post('branch', TRUE);//$info['brseqno'];
    $prKey       = $info['prkey'];
    $acctType    = $info['accttype'];
    $status      = $info['status'];
    
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
    
    $result = $this->card_model->getDefaultTranAllows('ACCT');
    
    $hex = $this->coreconverters->asciiBinToHex($bin);

    $allows = $this->coreconverters->asciiHexToBin($info['allows']);

    $xml1 = '';
    foreach ($result->result_array() as $row) {
      if (substr($bin, $row['bitno'] - 1, 1) !== substr($allows, $row['bitno'] - 1, 1)) {
        $checked = substr($bin, $row['bitno'] - 1, 1) === '1' ? 'Y' : 'N';
        $xml1 .= '<'.str_replace(' ', '', $row['description']).'>'.$checked.'</>';
      }
    }

    $result->free_result();
    $result->next_result();
    
    $ipAddress   = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $userAudit   = $core->getUserID();
    $override  = $this->session->userdata('userOverride');
    $sessionID   = $core->getSessionID();
    
    $result = $card->updateAccount(
      $prseqno,
      $cifseqno,
      $brseqno,
      $prKey,
      $acctType,
      $status,
      $info['allows'],//$hex,
      $ipAddress,
      $workstation,
      $userAudit,
      $override,
      $sessionID,
      $xml1
    );
    
    $this->session->unset_userdata('userOverride');
    $row = $result->row_array();
    $errno = isset($row['errno']) ? (string) $row['errno'] : '';
    
    if ($errno === '0') {
      $success = TRUE;
      $message = 'Account successfully updated';
      $msgtype = 41;
    } else {
      $success = FALSE;
      $message = $row['errmsg'];
      $msgtype = 43;
    }

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $this->load->model('coreapp/customer_model');

    if ($oldcif != $cifseqno) {

      $result = $this->customer_model->getCustomerById($oldcif);
      $row = $result->row_array();

      if (count($row) > 0) {
        $oldname = $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'];
      }

      $result->free_result();
      $result->next_result();

      $result = $this->customer_model->getCustomerById($cifseqno);
      $row = $result->row_array();

      if (count($row) > 0) {
        $newname = $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'];
      }

      $result->free_result();
      $result->next_result();

      $auditXML = '<old>'.$oldname.'</><new>'.$newname.'</><field>Account Information</><details>Edit Account info: '.$prKey.'</><BRSEQNO>'.$brseqno.'</>';
      $this->card_model->insertAuditLogclixx($msgtype,'990207',$brseqno,0,0,'ACCT','',$prKey,'','','','','','','',$userAudit,$override,'WEB','WEB',$workstation,$auditXML);
    }

    $this->load->model('coreapp/branch_model');

    if ($info['brseqno'] != $brseqno) {

      $result = $this->branch_model->getBranchDetails($info['brseqno']);
      $row = $result->row_array();

      if (count($row) > 0) {
        $oldbranch = $row['brname'];
      }

      $result->free_result();
      $result->next_result();

      $result = $this->branch_model->getBranchDetails($brseqno);
      $row = $result->row_array();

      if (count($row) > 0) {
        $newbranch = $row['brname'];
      }

      $result->free_result();
      $result->next_result();

      $auditXML = '<old>'.$oldbranch.'</><new>'.$newbranch.'</><field>Account Information</><details>Edit Account info: '.$prKey.'</><BRSEQNO>'.$brseqno.'</>';
      $this->card_model->insertAuditLogclixx($msgtype,'990207',$brseqno,0,0,'ACCT','',$prKey,'','','','','','','',$userAudit,$override,'WEB','WEB',$workstation,$auditXML);
    }

    
    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'hex' => $xml1
    ));
  }
  
  function delete()
  {
    $this->load->model('coreapp/card_model');
    $this->load->library('coreconverters');
    
    $card  = $this->card_model;
    $core  = $this->core;
    $input = $this->input;
    
    $info = $_SESSION['accountInfo'];
    if (!$info) {
      echo json_encode(array(
        'success' => FALSE,
        'message' => 'An error has occured'
      ));
      exit;
    }
    
    $acctNo    = $info['prkey'];
    $acctType  = $info['accttype'];
    
    $ipAddress   = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $userAudit   = $core->getUserID();
    $override  = $this->session->userdata('userOverride');
    
    $result = $card->removeAcct(
      $acctNo,
      $acctType,
      $ipAddress,
      $workstation,
      $userAudit,
      $override
    );
    
    $this->session->unset_userdata('userOverride');
    $row = $result->row_array();
    $errno = isset($row['errno']) ? (string) $row['errno'] : '';
    
    if ($errno === '0') {
      $success = TRUE;
      $message = 'Account <strong>['. $acctNo .']</strong> removed successfully';
    } else {
      $success = FALSE;
      $message = $row['errmsg'];
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }
}
/* End of file newentry.php */
/* Location: ./application/contollers/accounts/newentry.php */
