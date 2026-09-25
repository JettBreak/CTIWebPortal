<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChangePW extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(CHANGEPW_NO);
    
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
    $data['minChar'] = $this->core->getPasswordMinChar();
    $data['secretQuestions'] = NULL;
    foreach ($this->core->getSecretQuestions() as $question) {
      $selected = $this->core->getSecretQuestion() === $question ? ' selected' : NULL;
      $data['secretQuestions'] .= '<option '.$selected.'>'. $question .'</option>';
    }
    
    $this->load->model('coreapp/card_model');

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Change Password</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/changepw', $data);
  }
  
  function verify()
  {
    $userID = $this->core->getUserID();
    $currentPW = $this->core->getUserPW();
    $xxx = $this->core->encrypt($userID, $this->input->post('currentPW', TRUE));
    
    if ($xxx === $currentPW) {
      $verified = '1';
    } else {
      $verified = '0';
    }
      
    echo $verified;
  }
  
  function submit()
  {
    $this->load->model('coreapp/user_model');
    $this->load->library('shortxml');
    $this->load->library('session');

    $required = array('currentPassword', 'newPassword', 'certificate');

    $result =   $this->core->validateParams($required, $_POST);
      
    if ($result['success']) {
      $params = $result['params'];
    } else {
      echo json_encode($result);
      exit();
    }

    if ($params['certificate'] > 188) {
      $xml = $this->shortxml;
      $brseqno = $this->core->getBranchID();
      $ipAddress = $this->core->getIPAddress();
      $workstation = $this->core->getWorkstation();
      $userAudit = $userID = $this->core->getUserID();
      $sessionID = $this->core->getSessionID();
      $grpseqno = $this->core->getUserGroup();
      $userPW = $this->core->encrypt($userAudit, $this->input->post('newPassword', TRUE));
      $oldPW = $this->core->encrypt($userAudit, $this->input->post('currentPassword', TRUE));
      
      $secretQ = $this->input->post('secretQuestion', TRUE);
      $secretA = $this->input->post('secretAnswer', TRUE);
      
      $changeSecretQ = FALSE;
      if ($secretA !== '') {
        $secretA = $this->core->encrypt($userID, sha1($this->input->post('secretAnswer', TRUE)));
        $changeSecretQ = TRUE;
      }

      $result = $this->user_model->getUserPassCycleCount($userID, $grpseqno);
      $row = $result->row_array();

      $result->free_result();
      $result->next_result();

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

      if (COUNT($pwArr) == COUNT($passCycle)) {
        array_pop($pwArr);
      }
      
      array_unshift($pwArr, $xml->getValue('WEBPWD'));

      $pwXML = '';

      for ($i = 1; $i < $passCycle; $i++) {
        if ($i > COUNT($pwArr)) {
          $pwXML .= '<PWSET'.$i.'></>';
        } else {
          $idx = $i - 1;
          $pwXML .= '<PWSET'.$i.'>'.$pwArr[$idx].'</>';
        }
      }
      //var_dump(array($userID, $userPW, $oldPW, FALSE, $secretQ, $secretA, $changeSecretQ, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID, $grpseqno, $pwXML));
      if ($success) {
        $result = $this->user_model->setUserPass($userID, $userPW, $oldPW, FALSE, $secretQ, $secretA, $changeSecretQ, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID, $grpseqno, $pwXML);
      }
      
      $row = $result->row_array();
      $this->session->set_userdata(array(
        'userPW' => $userPW,
        'secretQ' => $secretQ
      ));
    } else {
      $success = FALSE;
      $message = 'Your new password strength must be strong. <br>System alert. Please relogin.';
      $forcelogout = TRUE;

      $this->load->model('coreapp/card_model');

      $auditXML = '';
      $brseqno = $this->core->getBranchID();
      $userAudit = $this->core->getUserID();
      $workstation = $this->core->getWorkstation();

      $auditXML .= '<old></><new></><field>Change Password</><details>Your new password strength must be strong.</>';
      $this->card_model->insertAuditLogclixx(43,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    }

    echo json_encode(array(
      'success' => isset($success) ? $success : TRUE,
      'message' => isset($message) ? $message : $row['errmsg'],
      'status' => isset($forcelogout) ? $forcelogout : FALSE
    ));
  }
}