<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AreaEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->load->model('coreapp/area_model');
		$this->core->checkUserAllows(AREA_NO);
		
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
		 		
		if (!$area = $this->cache->get($this->core->getSessionID() . 'area')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$data['title'] = 'Edit Area';
		$data['submitBtnMsg'] = 'Update area?';
		$data['formAction'] = 'maintenance/areaedit/submit';
		$data['hiddenInput'] = '<input type="hidden" name="oldAreaCode" id="oldAreaCode" value="'. $area['areaCode'] .'"/>';
		$data['areacode'] = $area['areaCode'];
		$data['areaname'] = $area['areaName'];
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/area', $data);
	}
	
	function submit()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/user_model');
			
		$row = $this
			->user_model
			->checkLogin(
				$this->core->getUserID(),
				$this->core->getSessionID()
			)
			->row_array();
		
		if ($row['errno'] !== '8') { //if session valid
		
			$this->load->model('coreapp/area_model');
			 
			$brseqno     = $this->core->getBranchID();
			$ipaddress   = $this->core->getIPAddress();
			$workstation = $this->core->getWorkstation();
			$userAudit   = $this->core->getUserID();
			$sessionID   = $this->core->getSessionID();
	
			$result = $this->area_model->updateArea(
				$this->input->post('oldAreaCode', TRUE),
				$this->input->post('areaCode', TRUE),
				strtoupper($this->input->post('areaName', TRUE)),
				$brseqno,
				$ipaddress,
				$workstation,
				$userAudit,
				$sessionID
			);
			
			$row = $result->row_array();
			
			if ($row['errno'] > 0) {
				$success = FALSE;
				$message = $row['errmsg'];
			} else {
				$success = TRUE;
				$message = 'Area updated successfully';
				$this->cache->delete($this->core->getSessionID() .'areaList');
			}
	
		} else {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => $row['errno']
		));
	}
}