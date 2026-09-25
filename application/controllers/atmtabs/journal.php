<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Journal extends CI_Controller {
	
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
		$result = $atm->getATMJournal($terminalCode);	
		
		$details = array();//do not set to NULL. Must be an empty array
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$dtlog	  = $core->formatDate('m/d/Y h:i:s A', $row['dtime']);
				$info	  = str_replace('Term Up', 'Terminal Power Up', $row['information']);
				$statCode = $row['stacode'];
				$statDesc = $row['stadesc'];
				
				$details[] = array(
					$dtlog,
					$info,
					$statCode .': '. $statDesc
				);
			}
		}
		
		echo json_encode(array(
			'success'	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 7,
			'atm' 		 => $atmList,
			'details' 	 => $details
		));
	}
}
/* End of file journal.php */
/* Location: ./application/contollers/atmtabs/journal.php */