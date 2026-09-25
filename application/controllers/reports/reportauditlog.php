<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ReportAuditlog extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->load->model('coreapp/branch_model');
    $this->core->checkUserAllows(REPBRANCHLOG_NO);//REPORTPROCLIST_NO);
    
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
    $this->load->view('reports/reportauditlog');
  }
  
  function cache()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->cache->delete($this->core->getSessionID() .'brchlist');
    
    $brchlist = array(
      'date'    => $this->input->post('date', TRUE),
      'repid'   => $this->input->post('repid', TRUE),
      'repname'   => $this->input->post('repname', TRUE),
      'link'    => $this->input->post('link', TRUE),
      'userseqno' => $this->input->post('userseqno', TRUE),
      'brseqno'   => $this->input->post('brseqno', TRUE),
      'reportreqid'   => $this->input->post('reportreqid', TRUE)
    );
    
    $success = $this->cache->save($this->core->getSessionID() .'brchlist', $brchlist, CACHE_TTL);
    
    echo json_encode(array(
      'success' => $success
    ));
  }
  
  function getData()
  {
    $this->load->library('shortxml');
    $this->load->model('coresys/reports_model');


    $reports = $this->reports_model;
    $xml   = $this->shortxml;
    $xml2    = $this->shortxml;
    $core    = $this->core;


    $result = $reports->getreportrequest();

    $replist = $result->result_array();
    $details = array();
    
    $result->free_result();
    $result->next_result();
    
    $data['repreq'] = NULL;
    foreach($replist as $row) {
      $xml->setXML($row['parameter']);
      $userid = $xml->getVALUE('userid');
      $proctype = $xml->getVALUE('proctype');

      $jobproctype = $reports->getreporttypeinfo($proctype);

      $jobproctype = $jobproctype->row_array();

      if (($userid == $this->core->getUserID() || $userid == 'system') && in_array($xml->getVALUE('ReportID'), array(66,9999))) {
        $data['repreq'] .= $row['ReportRequestID'].',';

        $requestid = $row['ReportRequestID'];
        $result = $reports->getrequestinfo($requestid);
        
        $row = $result->row_array();
        
        $xml2->setXML($row['parameter']);
        $result->free_result();
        $result->next_result();
        
        $this->load->model('coreapp/user_model'); 

        $result = $this->user_model->getUserGroupInfo($this->core->getUserGroup());
        $grprow = $result->row_array();

        $result->free_result();
        $result->next_result();

        if ($grprow['description'] == 'Bank Audit' && $xml->getVALUE('ReportID') == 9999) {
          $fileType = 'AUDIT'.date('mdY', strtotime($row['DateRequested'])).'.zip';
        } else if ($xml->getVALUE('ReportID') != 9999) {
          $fileType = $xml->getVALUE('ext') == 'PDF' ? '.PDF' : '.CSV';
        } else {
          continue;
        }

        $https = $core->isSSL() ? 'https' : 'http';

        if (intval($row['ReportStatus']) == 3){
          $href = '<a id="xmlLink" href="'.$https.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/pdf/audit/'.$xml->getVALUE('fileName').$fileType.'" target="_blank">For Download</a>';
        } else {
          $href = $row['statdesc'] != NULL ? $row['statdesc'] : 'Not Found';
        }
        
        $repid   = substr($row['Reportname'], 0, 6);
        $repname = substr($row['Reportname'], 7);
        $reqid   = $row['ReportRequestID']; 

        if ($xml->getVALUE('ReportID') == 9999) {
          $repname = $xml->getVALUE('fileName') . ' AUDIT';
        }

        $details[] = array(
          $reqid,
          $core->formatDate('Y-m-d H:i:s', $row['DateRequested']),
          $repid,
          $repname,
          isset($jobproctype['Description']) ? $jobproctype['Description'] : 'Daily',
          $href,
          $userid,
          0,
          $row['ReportRequestID']
        );  
      } else {
        continue;
      }

      $result->free_result();
      $result->next_result();
    }

    $result->free_result();
    $result->next_result();

    //if (strlen($data['repreq']) > 0) {
    //  $reprequest = substr($data['repreq'], 0, (strlen($data['repreq']) - 1));
    //} else {
    //  $reprequest = 0;
    //}

    //$result->free_result();
    //$result->next_result();
    
    //$result = $reports->getreportjob($reprequest);
    
    /*$details = array();
    foreach ($result->result_array() as $row) {
      
      // GET DATA 
      $xml->setXML($row['xml1']);
      $details[] = array(
        $row['brcode'],
        $row['brname'],
        $row['brid'],
        $row['brseqno'],
        $row['address'],
        $row['telno']
      );
    }*/
    
    echo json_encode(array(
      'success' => TRUE,
      'details' => $details,
      'grprow' => $grprow
    ));
  }

  function dlreport() 
  {
    $this->load->library('shortxml');
    $this->load->model('coresys/reports_model');
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));

    $requestid = $this->input->post('reportreqid', TRUE);

    $reports = $this->reports_model;
    $xml   = $this->shortxml;

    $result = $reports->getrequestinfo($requestid);

    $row = $result->row_array();

    $xml->setXML($row['parameter']);


    //$file = $_SERVER['DOCUMENT_ROOT'].'/pdf/'.$xml->getVALUE('fileName').".pdf";//$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].'/pdf/'.$xml->getVALUE('fileName').".pdf";
    //file_put_contents($xml->getVALUE('fileName').".pdf", fopen($file, 'r'));
    //if ($file) {
    //$file = 'https://'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].'/pdf/'.$xml->getVALUE('fileName').".pdf";
    //$this->cache->save($this->core->getSessionID() .'dlfile', $message, CACHE_TTL);

    //$url = $file;
    //$src = fopen($url, 'r');
    //$dest = fopen('php://output', 'w');
    //$bytesCopied = stream_copy_to_stream($src, $dest);

    //header('Content-type: application/pdf');
    //header('Content-Disposition: attachment; filename='.$xml->getVALUE('fileName').'.pdf');
    //header('Pragma: no-cache');
    //header('Expires: 0');
      
    //echo $src;

    //$success = TRUE;
    //$message = "Download Completed".$file;
    //} else {
      //$success = FALSE;
      //$message = "Download Failed".$file;
    //}

    //echo json_encode(array(
    //  'success' => $success,
    //  'message' => $message
    //));
  }

  function dlfile() {

    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename='."rep.pdf");
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $this->cache->get($this->core->getSessionID() . 'dlfile');
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
}