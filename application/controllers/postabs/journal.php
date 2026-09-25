<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Journal extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONPOS_NO);
	}
	
	function index()
	{
		$this->load->model('coresys/pos_model');

		$pos   = $this->pos_model;
		$core  = $this->core;
		$input = $this->input;
		
		if ($core->canMon()) {
			$branchCode = $input->get('brcode');
			$locCode = $input->get('loccode');
		} else {
			$branchCode = $core->getBranchCode();
			$locCode = 0;
		}
		$status = $input->get('status');
		
		$result = $pos->getPOSList($branchCode, $locCode, $status);
		
		if ($input->get('isFilter') !== '0') {
			$selected = $result->row_array();
			$terminalCode = $selected['termcode'];
		} else {
			$selected = NULL;
			$terminalCode = $input->get('poscode');
		}
		
		$posList = $core->showPOSList($result);
		
		$result->free_result();
		$result->next_result();
		
		$result = $pos->getPOSJournal($terminalCode);	
		
		$details = array();//do not set to NULL. Must be an empty array
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$dtlog	  = $core->formatDate('F j, Y h:i:s A', $row['dtime']);
				$info	  = $row['information'];
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
			'tableIndex' => 1,
			'pos' 		 => $posList,
			'details' 	 => $details,
			'selected'	 => $selected
		));
	}
}
/* End of file journal.php */
/* Location: ./application/contollers/postabs/journal.php */