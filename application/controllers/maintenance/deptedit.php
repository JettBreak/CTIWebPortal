<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DeptEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->load->model('coreapp/dept_model');
		$this->core->checkUserAllows(DEPT_NO);
		
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
		
		//$this->cache->clean();
		if (!$department = $this->cache->get($this->core->getSessionID() . 'department')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$data['title'] = 'Edit Department';
		$data['submitBtnMsg'] = 'Update department?';
		$data['formAction'] = 'maintenance/deptedit/submit';
		$data['deptCode'] = $department['deptCode'];
		$data['deptName'] = $department['deptName'];
		$data['hiddenInput'] = '<input type="hidden" name="deptCode" id="deptCode" value="'. $department['deptCode'] .'"/>'.
			'<input type="hidden" name="isEdited" id="isEdited" value="0"/>';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/deptx', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/user_model');
			
		$row = $this
			->user_model
			->checkLogin(
				$this->core->getUserID(),
				$this->core->getSessionID()
			)
			->row_array();
		
		if ($row['errno'] !== '8') { //if session valid
		
			$result = $this->dept_model->updateDepartment(
				$this->input->post('deptCode', TRUE),
				$this->input->post('newDeptCode', TRUE),
				$this->input->post('deptName', TRUE),
				$this->core->getBranchID(),
				$this->core->getIPAddress(),
				$this->core->getWorkstation(),
				$this->core->getUserID(),
				$this->core->getSessionID(),
				$this->input->post('isEdited', TRUE)
			);
			
			$row = $result->row_array();
			
			if ($row['errno'] > 0) {
				$success = FALSE;
			} else {
				$success = TRUE;
			}
			
		} else {
			$success = FALSE;
			//$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $row['errmsg'],
			'errorno' => $row['errno']
		));
	}
}