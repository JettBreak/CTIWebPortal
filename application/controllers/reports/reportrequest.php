<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ReportRequest extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(REPORTPROCLIST_NO);
    
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
    $this->load->model('coresys/reports_model');
    $reports = $this->reports_model;
    
    //branches combobox
    if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
      $this->load->model('coreapp/branch_model');
      $result = $this->branch_model->getBranchList();
    
      $branches = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      $this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
    }
    
    $branchList = NULL;
    foreach ($branches as $row) {
      $branchList .= '<option value="'. $row['brseqno'] .'" brname="'. $row['brname'] .'">'. $row['brname'] .'</option>';
    }
    
    $data['branches'] = NULL;
    if ($this->core->canRep()) {
      $data['branches'] = '<tr>
        <td><label for="branchList">Branch:</label></td>
        <td><select name="branch" id="branchList" style="width:170px">
            <option value="0" brname="ALL BRANCHES">ALL</option>
            '. $branchList .'
          </select></td>
      </tr>';
    } else {
      $data['branches'] = '<tr>
        <td><label for="branchList">Branch:</label></td>
        <td><select name="branch" id="branchList" style="width:170px">
            <option value="'.$this->core->getBranchID().'" brname="'.$this->core->getBranchName().'">'.$this->core->getBranchName().'</option>
          </select></td>
      </tr>';
    }
    //$result = $reports->getProcessList();
    $result = $reports->getTrxListForReport();

    $result->free_result();
    $result->next_result();
    
    $result = $reports->getreportslist();

    $data['procList'] = NULL;
    foreach ($result->result_array() as $row) {
      $reportid   = $row['ReportID'];
      $reportdesc = substr($row['Description'], 7);
      $reportType = $row['ReportType'];

      if (in_array($reportid, array(34,37,39,40,66,9998,9999)) ) {
        continue;
      }
      
      $data['procList'] .= '<option rkey="'.$reportType.'" value="'. $reportid .'">'. $reportdesc .'</option>';
    }
/*    if ($this->core->getBankCode() == '811') {
      $data['procList'] .= '<option rkey="1" value="0">Data Process</option>';
    }*/

    $result->free_result();
    $result->next_result();

    $result = $reports->getreporttype();

    $data['procType'] = NULL;
    foreach ($result->result_array() as $row) {
      $reporttype = $row['ReportProcessTypeID'];
      $reporttypedesc = $row['Description'];

      if ($reporttype == 0) {
        continue;
      }
      
      $data['procType'] .= '<option value="'. $reporttype .'">'. $reporttypedesc .'</option>';
    }

    $result->free_result();
    $result->next_result();
    
    $data['date'] = date('m/d/Y');
    $this->load->view('reports/reportrequest', $data);
  }
  
  function submit()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coresys/reports_model');
    $this->load->library('core');
    $this->load->library('shortxml');

    $reports = $this->reports_model;
    $xmldata = $this->shortxml;

    $proctype   = $this->input->post('procType', TRUE);
    $datefrom   = $this->input->post('procDateFrom', TRUE);
    $dateto   = $this->input->post('procDateTo', TRUE);
    $reportid   = $this->input->post('trxcode', TRUE);
    $fileType   = $this->input->post('fileType', TRUE);
    $userid   = $this->core->getUserID();
    $userAudit   = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();
    $brseqno     = $this->core->getBranchID();

    $branchid   = $this->input->post('branch', TRUE);

    switch ($proctype) {
      case '1':
        $datefrom = date("Y-m-d", strtotime($datefrom));
        $dateto = date("Y-m-d", strtotime($datefrom));
        break;
      
      case '2':
        $datefrom = date("Y-m-1", strtotime($datefrom));
        $dateto = date("Y-m-t", strtotime($datefrom));
        break;

      case '3':
        $datefrom = date("Y-m-d", strtotime($datefrom));
        $dateto = date("Y-m-d", strtotime($dateto));
        break;
    }

    $result = $reports->getreportinfo($reportid);

    $row = $result->row_array();

    $result->free_result();
    $result->next_result();

    $ext = intval($fileType) == 1 ? 'PDF' : 'CSV';

/*    if ($reportid == 0) {
      $fileName = 'DataProcess' . date("_mdY_H:i:s");
      $reportname = 'Data Process';
    } else {*/
      if (in_array($reportid, array(34,37))) {
        $fileName = trim(substr($row['Description'], 0, 6)) . date("mdY", strtotime($datefrom));
        $reportname = trim(substr($row['Description'], 6));
      } else {
        $fileName = substr($row['Description'], 0, 6) . date("_mdY_H:i:s");
        $reportname = substr($row['Description'], 6);
      }

    $parameter = '<ReportID>'.$reportid.'</><userid>'.$userid.'</><proctype>'.$proctype.'</>'.
          '<reportType>1</><datefrom>'.$datefrom.'</><dateto>'.$dateto.'</>'.
          '<branchid>'.$branchid.'</><fileName>'.$fileName.'</>'.'<ext>'.$ext.'</>';

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
    
      if ($reportid > 0) {

        $out = '<MSGTYPE>50</>' .
                 '<TRXCODE>955955</>' . 
                 '<XML>' . $parameter .
                 '</>' .
                 "\x00";

            } else {

              $out = '<MSGTYPE>50</>'.
            '<TRXCODE>955999</>'.
            '<XML>'.
              '<EventID>1</>' .
              '<UserID>'.$userid.'</>' .
              '<BranchCode>'.$branchid.'</>'.
          '<DateFrom>'.$datefrom.'</>'.
          '<DateTo>'.$dateto.'</>'.
            '</>'."\x00";
      }
    
      fwrite($fp, $out);
    
      $msg = '';

      $starttime = time();

      $message = '';

      while (TRUE) {
        $x = fgets($fp, 2);
        $time = time() - $starttime;
        if ($x === "\x00" ) {
          $success = TRUE;
          break;
        } elseif ($time > 5) {
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


    if ($success) {
      $msgtype = intval($xmldata->getValue('MSGTYPE'));

      if ( in_array($msgtype, array(53, 73)) ) {
        $success = FALSE;
        $message = $xmldata->getValue('SYSVDESC');
      } else {
        //$success = $this->cache->save($this->core->getSessionID() .'cust', $custInfo, CACHE_TTL);

        if (!$success) {
          $message = 'Unable to save data.';
        } else {
          $message = 'Request successfully submitted.';
          $auditXML = '';

          $auditXML .= '<old></><new></><field>Generate Report</><details>Report Name: '.$reportname.'</>';
          $this->reports_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'REPO','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

        }
      }
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));

  }
}
/* End of file branchlog.php */
/* Location: ./application/reports/branchlog.php */
