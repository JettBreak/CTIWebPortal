<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplies extends CI_Controller {
	
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
		$result = $atm->getTerminalSupplies($terminalCode);
		
		$details = array();
		foreach ($result->result_array() as $row)
		{
			if (isset($row['dtlastcommand'])) {
				$dtLastCmd = $core->formatDate('m/d/Y h:i:s A', $row['dtlastcommand']);
			} else {
				$dtLastCmd = 'None';
			}
			
			if (isset($row['dtlastupdated'])) {
				$dtLastUpdated = $core->formatDate('m/d/Y h:i:s A', $row['dtlastupdated']);
			} else {
				$dtLastUpdated = 'None';
			}

			$info = $row['info'];
			$status = $row['status'];
			$statCode = $row['statcode'];
			
			$details[] = array($info, $status, $statCode);
		}
		
		$toolbar = $core->compressOutput('<span class="dataTables_custom floatLeft">
				<span class="info"><span class="label">Date Last Command:</span> '. $dtLastCmd .'</span>
				<span class="info"><span class="label">Date Last Updated:</span> '. $dtLastUpdated .'</span>
			</span>');
		
		echo json_encode(array(
			'success' 	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 3,
			'toolbar' 	 => $toolbar,
			'atm' 		 => $atmList,
			'details' 	 => $details
		));
	}
}
/* End of file supplies.php */
/* Location: ./application/contollers/atmtabs/supplies.php */