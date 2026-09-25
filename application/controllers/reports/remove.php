<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Remove extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(MAINTENANCEATM_NO);
	}
	
	function index()
	{
		$reportID = $this->input->get('increpid', TRUE);

		$head = '<tr><td><input type="hidden" name="trx[]"/><span>Remove report job [<b style="color:#FFCC00">'.$reportID.'</b>]?</span></td></tr><tr><td>&nbsp;</td></tr>';
		$includeNotFound = '<tr><td><fieldset id="checkArray"><input type="checkbox" name="incnotfound" value="0"/><span>Include All Not Found</span><fieldset></td></tr>';
		$includeFailed = '<tr><td><fieldset id="checkArray2"><input type="checkbox" name="incfailed" value="0"/><span>Include All Failed</span></fieldset></td></tr>';
		
		$data['remove'] = $head.$includeNotFound.$includeFailed;

		$this->load->view('reports/remove', $data);
	}
	
	function submit()
	{
		$this->load->library('shortxml');
		$this->load->model('coresys/reports_model');

		$reports = $this->reports_model;
		$xml 	 = $this->shortxml;
		$xml2 	 = $this->shortxml;
		$core    = $this->core;

		$reportID = $this->input->post('increpid', TRUE);
		$incNotFound = $this->input->post('incnotfound', TRUE);
		$incFailed = $this->input->post('incfailed', TRUE);

		$result = $reports->removereportrequest($reportID,$this->core->getSessionID(),$this->core->getUserID());

		$row = $result->row_array();

		$result->free_result();
		$result->next_result();
		
		$data['errno'] = array();

		if ($row['errno'] > 0) {
			$data['errno'][] = $row['errno'];
		}

		if ($incNotFound == 1 || $incFailed == 1) {
			$result = $reports->getreportrequest();
			$replist = $result->result_array();
			$details = array();

			$result->free_result();
			$result->next_result();

			$details = array();
			foreach($replist as $row) {
				$xml->setXML($row['parameter']);
				$userid = $xml->getVALUE('userid');
				$proctype = $xml->getVALUE('proctype');

				if ($userid == $this->core->getUserID()) {

					$requestid = $row['ReportRequestID'];
					$result = $reports->getrequestinfo($requestid);

					$row = $result->row_array();

					$xml2->setXML($row['parameter']);

					$status = $row['statdesc'] != NULL ? $row['ReportStatus'] : '';

					if ($incNotFound == 1 && $status == '') {
						$result = $reports->removereportrequest($requestid,$this->core->getSessionID(),$this->core->getUserID());

						$row = $result->row_array();

						$result->free_result();
						$result->next_result();

						if ($row['errno'] > 0) {
							$data['errno'][] = $row['errno'];
						}
					} 

					if ($incFailed == 1 && intval($status) == 4) {
						$result = $reports->removereportrequest($requestid,$this->core->getSessionID(),$this->core->getUserID());

						$row = $result->row_array();

						$result->free_result();
						$result->next_result();

						if ($row['errno'] > 0) {
							$data['errno'][] = $row['errno'];
						}
					}
				}
			}
		}

		if (count($data['errno']) > 0) {
			if ($incNotFound == 1 || $incFailed == 1) {
				$success = TRUE;
				$message = 'Some reports did not successfully removed.';
			} else {
				$success = FALSE;
				$message = 'Failed to remove report['.$reportID.']';
			}
		} else {
			$success = TRUE;
			$message = 'Reports successfully removed.';
		}

		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}