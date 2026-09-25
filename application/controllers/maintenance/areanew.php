<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AreaNew extends CI_Controller {
	
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
		$data['title'] = 'New Area';
		$data['submitBtnMsg'] = 'Create new area?';
		$data['formAction'] = 'maintenance/areanew/submit';
		$data['hiddenInput'] = NULL;
		$data['areacode'] = NULL;
		$data['areaname'] = NULL;
		
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
	
			$result = $this->area_model->insertArea(
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
				$message = 'New area added successfully';
				$this->cache->delete($sessionID .'areaList');
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