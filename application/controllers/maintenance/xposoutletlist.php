<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class XPOSOutletList extends CI_Controller {	

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MAINTENANCEPOS_NO);
		
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
		
		//get branches
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$data['branches'] = NULL;
		$currentBrCode = NULL;
		
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				$matched = $row['brseqno'] === $this->core->getBranchID() ? TRUE : FALSE;
				
				if (!$this->core->canMon() && $matched) {
					$data['branches'] = '<option value="'. $row['brcode'] .'">'. $row['brname'] .'</option>';
					break;
				}
				
				if ($matched) {
					$currentBrCode = $row['brcode'];
				} else {
					$selected = NULL;
				}
				
				$data['branches'] .= '<option value="'. $row['brcode'] .'">'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		//end
		
		if ($this->core->canMon()) {
			$data['uiToolbar'] = "$('.ui-toolbar:even').append($('#customToolbar .top').html());";
			$data['initbrcode'] = 0;
		} else {
			$data['uiToolbar'] = NULL;
			$data['initbrseqno'] = $this->core->getBranchID();
		}
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/xposoutletlist', $data);
	}
	
	function getData()
	{
		$this->load->model('coresys/pos_model');
		$this->load->model('coreapp/branch_model');
			
		/*if ($this->core->canMon()) {
			$branchCode = $this->input->get('brcode', TRUE);
		} else {
			$branchCode = $this->core->getBranchCode();
		}*/

		if ($this->core->isHeadOffice()) {
			$brseqno = 0;
		} else {
			$brseqno = $this->core->getBranchID();
		}

		$result = $this->branch_model->getOutlets();
		
		$details = array();
		if ($result->num_rows() > 0) {
			$success = TRUE;
			foreach ($result->result_array() as $row)
			{
				$details[] = array(
					$row['outletseqno'],
					$row['outletid'],
					$row['outletname'],//,
					$row['instname']
					//isset($row['statdesc']) ? $row['statdesc'] : $row['status'] .'-Unknown' //if NULL display Unknown
				);
			}
		} else {
			$success = FALSE;
		}
		
		echo json_encode(array(
			'success' => $success,
			'details' => $details,
			'xpos' => 'xpos'
		));
	}
	
	function remove()
	{
		$this->load->model('coresys/pos_model');
		$this->load->library('shortxml');

		$xml = $this->shortxml;
		$success = TRUE;
		$message = '';
		
		$outletid = $this->input->post('outletid', TRUE);
		$outletcode = $this->input->post('outletcode', TRUE);	

		$result = $this->pos_model->checkInstitution($outletid);
		$list = $result->result_array();

		$result->free_result();
		$result->next_result();

		$cntx = 0;
		foreach ($list AS $row) {
			$xml->setXML($row['xml']);
			$id = $row['xml'] ? $xml->getValue('OUTLET') : '';

			if ($id == $outletid) {
				$cntx++;
			}
		}

		if ($cntx > 0) {
			$success = FALSE;
			$message = 'Outlet has existing link';
		} else {
			$this->pos_model->deleteOutlet($outletid);
		}
		
		echo json_encode(array(
			'removed' => $success,
			'message' => $message,
			'outletid' => $outletid,
			'outletcode' => $outletcode,
			'cntr' => $cntx
		));
	}
}