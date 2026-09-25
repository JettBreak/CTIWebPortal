<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search2 extends CI_Controller {
  
  private $fileVersion = '1.10.00';

  function index()
  {   
    
    $this->load->library('core');
    $this->core->checkUserAllows(CUSTNEW_NO);
    
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

    $this->load->library('version');

    $file = basename(__DIR__) . '/' . basename(__FILE__);

    $verified = $this->version->validate($file, $this->fileVersion);

    if (!$verified) {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'Module is out of date. Please contact software administrator.'
      ));
      exit();
    } else {
      $data['sessionExp'] = $this->core->getSessionExp();
      $this->load->view('customer/search2', $data);
    }

  }
  
  function submit()
  {
    $this->load->model('coreapp/card_model');
    
    $success = TRUE;
    
    echo json_encode(array(
      'success' => $success
    ));
  }
  
  function cache()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/card_model');
    $this->load->library('shortxml');
    
    $input   = $this->input;
    $xmldata  = $this->shortxml;
    $core   = $this->core;

    $cifseqno = $input->post('cifseqno', TRUE);
    $brseqno = $core->getBranchID();
    $userAudit = $core->getUserID();
    $workstation = $core->getWorkstation();
    
    $fp = NULL;
    $rport = 17003;
    $rhost = 'localhost';

    try {
      if (@fsockopen($rhost, $rport, $errno, $errstr, 10)) {
        $fp = fsockopen($rhost, $rport, $errno, $errstr, 10);
      } else {
        $message = "Warning: Unable to connect to port [".$rport."]";
        $success = FALSE;


        //fclose($fp);

        echo json_encode(array(
          'success' => $success,
          'message' => $message
        ));

        exit();
      }
    } catch (Exception $e) {
      
      $message = $errno !== NULL ? $errno : $e;
      $success = FALSE;

    }

    if (!$fp) {
      $success = FALSE;
    } else {
    
      $out = '<MSGTYPE>50</>' .
               '<TRXCODE>660011</>' . 
               '<XML>' .
               '<OCIF>'.$cifseqno.'</>' .
               '</>' .
               "\x00";
    
      fwrite($fp, $out);
    
      $msg = '';

      $_SESSION['request_time'] = time();

      $starttime = $_SESSION['request_time'];
      $endtime = time();

      $message = '';

      while (TRUE) {
        $x = fgets($fp, 2);
        $time = time() - $_SESSION['request_time'];
        if ($x === "\x00" ) {
          $success = TRUE;
          break;
        } elseif ($time > 10) {
          $success = FALSE;
          $message = "Connection Timeout (timelapse: ".$time." Starttime: ".$starttime." Endtime: ".time().")";
          break;
        } else {
          $success = TRUE;
        }

        $msg .= $x;
      }

      $xmlstring = $msg;
      $xmldata->setXML($msg);
      
      fclose($fp);
    }
    
    
    $custInfo = array(
      'cifseqno' => $cifseqno,
      'xmlstring'=> $xmlstring
    );

    if ($success) {
      $msgtype = intval($xmldata->getValue('MSGTYPE'));

      if ( in_array($msgtype, array(53, 73)) ) {
        $success = FALSE;
        $message = $xmldata->getValue('SYSVDESC');
        $auditXML = '';

        $auditXML .= '<old></><new></><field>Customer Request</><details>'.$message.'</>';
        $this->card_model->insertAuditLogclixx(43,'990317',$brseqno,0,0,'CUST','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        
      } else {
        $success = $this->cache->save($this->core->getSessionID() .'cust', $custInfo, CACHE_TTL);

        if (!$success) {
          $message = 'Unable to save data.';
        } else {

          $message = utf8_decode($xmlstring);
        }
        $auditXML = '';

        $auditXML .= '<old></><new></><field>Customer Request</><details>'.$cifseqno.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CUST','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
          
      }
    }
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }
}