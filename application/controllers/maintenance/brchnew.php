<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BrchNew extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->load->model('coreapp/branch_model');
		$this->core->checkUserAllows(BRANCH_NO);
		
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
		if (!$area = $this->cache->get($this->core->getSessionID() . 'area')) {
			$result = $this->branch_model->getArea();
			$area = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'area', $area, CACHE_TTL);
		}
				 		
		$data['areaname'] = NULL;
		
		if (count($area) > 0) {
			foreach ($area as $row) {
				$data['areaname'] .= '<option value="'. $row['regioncode'] .'">'. $row['areaname'] .'</option>';
			}
		} else {
			$data['areaname'] .= '<option value="">No Area Defined</option>';
		}
		
		
		$data['title'] = 'New Branch';
		$data['submitBtnMsg'] = 'Create new branch?';
		$data['formAction'] = 'maintenance/brchnew/submit';
		$data['hiddenInput'] = NULL;
		$data['brchcode'] = NULL;
		$data['brchname'] = NULL;
		$data['brchid'] = NULL;
		$data['address'] = NULL;
		$data['telno'] = NULL;
		
		$data['checked'] = NULL;
		$data['isRep'] = NULL;
		$data['isRepLabel'] = NULL;
		$data['isMon'] = NULL;
		$data['isMonLabel'] = NULL;
		$data['isUser'] = NULL;
		$data['isUserLabel'] = NULL;
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/branch', $data);
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
		
			$this->load->model('coreapp/branch_model');
			 
			$brseqno     = $this->core->getBranchID();
			$ipaddress   = $this->core->getIPAddress();
			$workstation = $this->core->getWorkstation();
			$userAudit   = $this->core->getUserID();
			$sessionID   = $this->core->getSessionID();
			
			if ($this->input->post('headoffice', TRUE)) {
				$headofc = 'Y';
				$isRep = 'Y';
				$isMon = 'Y';
				$isUser = 'Y';
			} else {
				$headofc = 'N';
				$isRep = $this->input->post('isRep', TRUE) ? 'Y' : 'N';
				$isMon = $this->input->post('isMon', TRUE) ? 'Y' : 'N';
				$isUser = $this->input->post('isUser', TRUE) ? 'Y' : 'N';
			}
			
			$xml = '<ISREP>'. $isRep .'</>'.
					'<ISMON>'. $isMon .'</>'.
					'<ISUSER>'. $isUser .'</>';
			
			$result = $this->branch_model->insertBranch(
				$this->input->post('brchcode', TRUE),
				$this->input->post('brchid', TRUE),
				strtoupper($this->input->post('brchname', TRUE)),
				$this->input->post('areaname', TRUE),
				$this->input->post('address', TRUE),
				$this->input->post('telno', TRUE),
				$headofc,
				$xml,
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
				$message = 'New branch added successfully';
				$this->cache->delete($this->core->getSessionID() .'branches');
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