<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class StatHistory extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONATM_NO);
	}
	
	function index()
	{
		$this->load->model('coresys/atm_model');

		$atm   = $this->atm_model;
		$core  = $this->core;
		$input = $this->input;
		
		//for ATM list
		if ($input->get('list', TRUE) === '1') {
			if ($core->canMon()) {
				$branchCode = $input->get('brcode');
				$locCode = $input->get('loccode');
			} else {
				$branchCode = $core->getBranchCode();
				$locCode = 0;
			}
			$status = $input->get('status');
			
			//get ATM list
			$result  = $atm->getATMList($branchCode, $locCode, $status);	
			$atmList = $core->showATMList($result);
			$atmList = $core->compressOutput($atmList);
			
			$result->free_result();
			$result->next_result();
		} else {
			$atmList = NULL;
		}
		//end
		
		$terminalCode = $input->get('terminalcode');
		$result = $atm->getTerminalStatusHistory($terminalCode);	
		
		$details = array();
		foreach ($result->result_array() as $row) {
			$details[] = array(
				$core->formatDate('m/d/Y h:i:s A', $row['dtlog']), //Transaction Date/Time
				$row['deviceid'], //Device ID
				$row['tstatusdesc'], //Status
				$row['mstatusdesc'] ? $row['mstatusdesc'] : 'None', //Additional Status
				$row['severity'], //Severity
				$row['logseqno'] //Ref No.
			);
		}
		
		echo json_encode(array(
			'success' 	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 5,
			'atm' 		 => $atmList,
			'details' 	 => $details
		));
	}
}
/* End of file stathistory.php */
/* Location: ./application/contollers/atmtabs/stathistory.php */