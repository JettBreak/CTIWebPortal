<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONPOS_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/area_model');
		$this->load->model('coreapp/branch_model');
		$this->load->model('coresys/pos_model');

		$pos   = $this->pos_model;
		$core  = $this->core;
		$input = $this->input;
		
		//get POS list
		if ($core->canMon()) {
			$branchCode = $input->get('brcode');
			$locCode = $input->get('loccode');
		} else {
			$branchCode = $core->getBranchCode();
			$locCode = 0;
		}
		$status = $input->get('status');
		
		$result  = $pos->getPOSList($branchCode, $locCode, $status);	
		
		$posList = '';
		$info = NULL;
		$selected = NULL;
		
		if ($result->num_rows() > 0) {
			if ($input->get('isFilter') !== '0') {
				$selected = $result->row_array();
			}
			
			$posList = $core->showPOSList($result);
			$posList = $core->compressOutput($posList);
			
			foreach ($result->result_array() as $row) {
				if ($input->get('poscode') === $row['termcode']) {
					$info = $row;
					//get area name
					$res = $this->area_model->getAreaByBranch($row['brcode']);
					$rw  = $res->row_array();
					
					if ($res->num_rows() > 0) {
						$areaName = $rw['areaname'];//$core->getAreaName();
					} else {
						$areaName = 'Unknown';
					}
					
					$res->free_result();
					$res->next_result();
					//end
					
					//get branchname
					$res = $this->branch_model->getBranchNameValue($row['brcode']);
					
					if ($res->num_rows() > 0) {
						$rw  = $res->row_array();
					
						$branchName = $rw['brname'];//$core->getAreaName();
					} else {
						$branchName = 'Unknown';
					}
					
					$res->free_result();
					$res->next_result();
					//end
					
					$info['areaname'] = $areaName;
					$info['brname'] = $branchName;
					break;
				}
			}
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success'	 => TRUE,
			'dataTables' => FALSE,
			'tableIndex' => NULL,
			'pos' 		 => $posList,
			'details' 	 => NULL,
			'selected'	 => $selected,
			'info'		 => $info
		));
	}
}
/* End of file info.php */
/* Location: ./application/contollers/postabs/info.php */