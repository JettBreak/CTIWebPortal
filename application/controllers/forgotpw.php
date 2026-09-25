<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ForgotPW extends CI_Controller {
  
  function index()
  {
    $this->load->view('forgotpw');
  }
  
  function verify()
  {
    $this->load->model('coreapp/user_model');
    $this->load->library('shortxml');
    $this->load->library('session');
    
    $xml = $this->shortxml;
    $session = $this->session;
    
    $userID = $this->input->post('forgottenID', TRUE);
    $result = $this->user_model->getUserQuestion($userID);
    
    if ($result->num_rows() > 0) {
      $row = $result->row_array();
      $xml->setXML($row['xml2']. $row['grpxml']);
      
      if ($xml->getValue('WEBSTAT') !== '2') {
        echo json_encode(array(
          'verified' => FALSE,
          'message' => 'User ID not yet activated'
        ));
        exit();
      }
      
      $question = $xml->getValue('WEBQSTN');
      $answer = $xml->getValue('WEBANS');
      $minChar = $xml->getValue('MINCHAR');
      $emailAddr = $xml->getValue('EMAIL');
      $userName = isset($row['username']) ? $row['username'] : NULL;
      $instName = isset($row['instname']) ? $row['instname'] : NULL;
      $brseqno = isset($row['grpseqno']) ? $row['grpseqno'] : NULL;
      $stats = isset($row['status']) ? $row['status'] : NULL;
      
      //save userID and secretAnswer to cache
      //session_start();
      //$_SESSION['secretAnswer'] = $answer;
      //$_SESSION['userID'] = $userID;
      //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
      //$this->cache->save($this->core->getSessionID() .'secretAnswer', $answer, CACHE_TTL);
      //$this->cache->save($this->core->getSessionID() .'userID', $userID, CACHE_TTL);
      $session->unset_userdata(array(
        'secretAnswer',
        'userID',
        'username',
        'email'
      ));
      $session->set_userdata(array(
        'secretAnswer' => $answer,
        'userID' => $userID,
        'username' => $userName,
        'brseqno' => $brseqno,
        'instname' => $instName,
        'email' => $emailAddr,
        'stats' => $stats
      ));
      
      
      echo json_encode(array(
        'verified' => TRUE,
        'question' => $question,
        'minchar'  => $minChar
      ));
    } else {
      echo json_encode(array(
        'verified' => FALSE,
        'message' => 'Invalid User ID'
      ));
    }
  }
  
  function submit()
  {
    //$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/user_model');
    $this->load->library('shortxml');
    $this->load->library('session');
    
    $xml = $this->shortxml;
    $session = $this->session;
    //if ($secretAnswer = $this->cache->get($this->core->getSessionID() . 'secretAnswer')) {
    if ($secretAnswer = $session->userdata('secretAnswer')) {
      $this->load->library('core');
      
      $userID = $session->userdata('userID');
      $username = $session->userdata('username');
      $email = $session->userdata('email');
      $instname = $session->userdata('instname');
      $brseqno = $session->userdata('brseqno');
      $stats = $session->userdata('stats');
      $workstation = $this->core->getWorkstation();
      
      $answer = $this->core->encrypt($userID, $this->input->post('secretAnswer', TRUE));
      if ($secretAnswer === $answer) {


        date_default_timezone_set('Etc/UTC');

        //Create a new PHPMailer instance
        $mail = new PHPMailer;

        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->Host = 'bnetmail.bancnetonline.com';
        $mail->Port = 25;
        $mail->SMTPSecure = '';
        $mail->SMTPAuth = false;
        $mail->setFrom('noreply@bancnetonline.com', $instname.' Administrator');
        $mail->addAddress($email, $username);
        $randpass = $this->core->randomPassword();
        $mail->Subject = $instname.' Account Details.';

        $Body = "<span style='font-family:calibri; font-size:14px;'>Welcome to ".$instname." Web Portal!</span>".'<br><br>'.
                "<span style='font-family:calibri; font-size:13px;'>Your password has been reset.</span>".'<br>'.
                "<span style='font-family:calibri; font-size:13px;'>Please use the system generated password below:</span>".'<br><br>'.
                "<span style='font-family:calibri; font-size:13px;'>Password:    ".$randpass."</span><br><br>".
                "<span style='font-family:calibri; font-size:13px;'>You will be required to change your password after you log in.</span>".'<br><br>'.
                "<span style='font-family:calibri; font-size:13px;'>This is a system generated email please do not reply.</span>".'<br>'.
                "<span style='font-family:calibri; font-size:13px;'>For any question about your account, please contact your Bank Administrator.</span><br><br>";

        $Body .= "<span style='font-family:courier new; font-size:11px;'>------------------------------------------- DISCLAIMER ---------------------------------------------<br>
                 The information in this email may be Confidential and intended solely for the use of the individual or entity to whom it
                 is addressed and others authorized to receive it. If you are not the intended recipient or an authorized representative 
                 of the intended recipient, you are hereby notified that any review, dissemination or copying of this email and its 
                 attachments, if any, or the information contained herein is prohibited. If you have received this email in error, please 
                 immediately notify the sender by reply email and delete this email from your system.</span><br><br>".

     

                 "<span style='font-family:courier new; font-size:11px;'>Unless expressly stated, any opinions are the sender's and not necessarily that of BancNet, Inc.</span><br>".
                 "<span style='font-family:courier new; font-size:11px;'>Save paper, think before you print!</span><br>".
                 "<span style='font-family:courier new; font-size:11px;'>-----------------------------------------------------------------------</span>";

        $mail->Body = $Body;
        $mail->IsHTML(true);

        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');

        //send the message, check for errors
        $auditXML = '';
        if (!$mail->send()) {
          $auditXML .= '<old></><new></><field>Reset Web User</><details>'.$userID.' - '.$mail->ErrorInfo.'</>';
          $this->user_model->insertAuditLogclixx(43,'990317',$brseqno,0,0,'USER','',$userID,'','','','','','','',$userID,'','WEB','WEB',$workstation,$auditXML);
          $success = FALSE;
          $message = $mail->ErrorInfo;
        } else {

          $userPW = $this->core->encrypt($userID, sha1($randpass));
          //$userPW = $this->core->encrypt($userID, $this->input->post('newPassword', TRUE));
          $question = $this->input->post('secretQuestion', TRUE);
          $answer = $this->core->encrypt($userID, $this->input->post('secretAnswer', TRUE));
          $grpseqno = 0;

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

          if ($stats != 7) {
            if (COUNT($pwArr) == COUNT($passCycle)) {
              array_pop($pwArr);
            }
            
            array_unshift($pwArr, $xml->getValue('WEBPWD'));
          }

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
            $result = $this->user_model->setForgotPass($userID, $userPW, $question, $answer, $pwXML);
            $row = $result->row_array();
          }
          
          if (count($row) > 0) {
            //$row = $result->row_array();

            $message = 'Password was successfully reset.<br>System generated password has been emailed to the user.';
            
            //$this->cache->clean();
            if (isset($row['errno'])) {
              $success = intval($row['errno']) == 0;
              $message = $row['errmsg'];
            } else {
              $success = $success;
              $message = $success ? $row['errmsg'] : $message;
            }

          } else {
            $success = FALSE;
            $message = 'An error has occured';
          } 
        }
      } else {
        $success = FALSE;
        $message = 'Wrong answer';
      }
    } else {
      $success = FALSE;
      $message = 'Cache has expired';
    }
    
    $session->unset_userdata(array(
      'secretAnswer',
      'userID'
    ));
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }
}