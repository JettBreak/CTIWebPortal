<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Programs extends CI_Controller {
	
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
		
		$progCode = $input->get('progcode');
		$progLang = $input->get('proglang');
		$result = $atm->getTerminalPrograms($progCode, $progLang);
		
		$details = array();
		foreach ($result->result_array() as $row) {
			$tpFile = $row['tpfile'];
			$tpType = $row['tpname'];
			$tpDesc = $row['tpdesc'];
			
			$details[] = array($tpFile, $tpType, $tpDesc);
		}
		
		echo json_encode(array(
			'success' 	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 0,
			'atm' 		 => $atmList,
			'details' 	 => $details
		));
	}
}
/* End of file programs.php */
/* Location: ./application/contollers/atmtabs/programs.php */