<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FileReports extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(INPUTFILESREP_NO);
		
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
				<td><select name="branch" id="branchList" style="width:170px" disabled>
						<option value="0" brname="ALL BRANCHES">ALL</option>
						'. $branchList .'
					</select></td>
			</tr>';
		}
		//$result = $reports->getProcessList();
		$result = $reports->getTrxListForReport();

		$result->free_result();
		$result->next_result();

		$replist = array(
			1 => array(
				'name' => 'BARTS File',
				'id' => 'barts'
				//'id' => 'reports/barts/process'//BARTSFILE_NO
			),
			2 => array(
				'name' => 'Bills Payment Report',
				'id' => 'bpay'
				//'id' => 'reports/billspaymentrep/process'
			),
			3 => array(
				'name' => 'Loan Payment File',
				'id' => 'loanpayrep'
			),
			4 => array(
				'name' => 'On-Us File',
				'id' => 'onusrep'
			)
		);

		//if (intval($this->core->getBankCode()) == 49) {
			array_push($replist,
				array(
				'name' => 'Export Card History',
				'id' => 'xcard'));
		//}
		
		$result = $reports->getreportslist();

		$data['procList'] = NULL;
		foreach (/*$result->result_array()*/$replist as $row) {
			$reportid = $row['id'];//$row['ReportID'];
			$reportdesc = $row['name'];//substr($row['Description'], 7);
			
			$data['procList'] .= '<option value="'. $reportid .'">'. $reportdesc .'</option>';
		}

		$result->free_result();
		$result->next_result();

		$result = $reports->getreporttype();

		$data['procType'] = NULL;
		foreach ($result->result_array() as $row) {
			$reporttype = $row['ReportProcessTypeID'];
			$reporttypedesc = $row['Description'];
			
			$data['procType'] .= '<option value="'. $reporttype .'">'. $reporttypedesc .'</option>';
		}

		$result->free_result();
		$result->next_result();
		
		$data['date'] = date('m/d/Y');
		$data['formAction'] = NULL;
		$this->load->view('reports/filereports', $data);
	}
	
	function submit()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/reports_model');
		$this->load->library('core');
		$this->load->library('shortxml');

		$reports = $this->reports_model;
		$xmldata = $this->shortxml;

		$proctype 	= $this->input->post('procType', TRUE);
		$datefrom 	= $this->input->post('procDate', TRUE);
		$dateto 	= $this->input->post('procDateTo', TRUE);
		$reportid 	= $this->input->post('trxcode', TRUE);
		$userid 	= $this->core->getUserID();

		$branchid 	= $this->input->post('branch', TRUE);

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

		$fileName = substr($row['Description'], 0, 6) . date("_mdY_H:i:s");

		$parameter = '<ReportID>'.$reportid.'</><userid>'.$userid.'</><proctype>'.$proctype.'</>'.
					'<reportType>1</><datefrom>'.$datefrom.'</><dateto>'.$dateto.'</>'.
					'<branchid>'.$branchid.'</><fileName>'.$fileName.'</>';

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
               '<TRXCODE>955955</>' . 
               '<XML>' . $parameter .
               '</>' .
               "\x00";
		
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