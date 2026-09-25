<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CMDHistory extends CI_Controller {
	
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
		
		$terminalLuno = $input->get('luno', TRUE);
		$result = $atm->getTerminalCommands($terminalLuno);	
		
		$details = array();
		foreach ($result->result_array() as $row)
		{
			$details[] = array(
				$row['description'], //Command
				$core->formatDate('m/d/Y h:i:s A', $row['dtlog']), //Date Log
				$row['dtprocessed'] === '0000-00-00 00:00:00' ? '' : $core->formatDate('m/d/Y h:i:s A', $row['dtprocessed']), //Date Processed
				$row['logseqno'], //Ref No.
				$row['stat'], //Status
				$row['useraudit'] //Processed By
			);
		}
		
		echo json_encode(array(
			'success' 	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 4,
			'atm' 		 => $atmList,
			'details' 	 => $details
		));
	}
}
/* End of file cmdhistory.php */
/* Location: ./application/contollers/atmtabs/cmdhistory.php */