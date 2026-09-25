<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class IssueLogNew extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(ISSUELIST_NO);
		
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
	
	function index($termCode)
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/atm_model');
		
		$data['hiddenInput'] = NULL;
		
		//issue types
		if (!$issueTypes = $this->cache->get($this->core->getSessionID() . 'issueTypes')) {
			$result = $this->atm_model->getIssueTypes();
			
			$issueTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'issueTypes', $issueTypes, CACHE_TTL);
		} 
		
		$data['issueTypes'] = NULL;
		foreach ($issueTypes as $row) {
			$data['issueTypes'] .= '<option value="'. $row['codeseqno'] .'">'. $row['codevalue'] .'</option>';
		}
		//end
		
		//hardware types
		if (!$hardwareTypes = $this->cache->get($this->core->getSessionID() . 'hardwareTypes')) {
			$result = $this->atm_model->getHardwareTypes();
			
			$hardwareTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'hardwareTypes', $hardwareTypes, CACHE_TTL);
		} 
		
		$data['hardwareTypes'] = NULL;
		foreach ($hardwareTypes as $row) {
			$data['hardwareTypes'] .= '<option value="'. $row['codeseqno'] .'">'. $row['codevalue'] .'</option>';
		}
		//end
		$data['hardwareTypeRow'] = NULL;
		$data['downtime'] = '01:00';
		$data['othersRow'] = ' class="hidden"';
		$data['othersDefault'] = 0;
		$data['exDowntimeRow'] = ' class="hidden"';
		$data['reasonRow'] = ' class="hidden"';
		$data['others'] = NULL;
		$data['dtResolved'] = 'Unresolved';
		$data['dtResolvedDefault'] = 0;
		$data['exDowntime'] = '00:00';
		$data['reason'] = NULL;
		
		$data['currentDT'] = date('m/d/Y');
		$data['termCode'] = $termCode;
		$data['title'] = 'Report Issue';
		$data['waitMsg'] = 'Submitting...';
		$data['formAction'] = 'maintenance/issuelognew/submit';
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/issuelogx', $data);
	}
	
	function submit()
	{
		$this->load->model('coresys/atm_model');
		
		$input = $this->input;
		$core = $this->core;
		
		$brseqno = $core->getBranchID();
		$userAudit = $core->getUserID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		
		$termCode = $input->post('termCode', TRUE);
		$dtLog = $core->formatDate('Y-m-d', $input->post('dtLog', TRUE));
		$issueType = $input->post('issueType', TRUE);
		$hardwareType = $input->post('hardwareType', TRUE);
		$others = $input->post('others', TRUE);
		$downtime = $input->post('downtime', TRUE);
		
		//downtime
		$minutes = substr($downtime, -2);
		$hours = substr($downtime, 0, 2);
		
		$downtime = ($hours * 60) + $minutes;
		//end
		
		if ($input->post('dtResolved', TRUE) !== 'Unresolved') {
			$dtResolved = $core->formatDate('Y-m-d', $input->post('dtResolved', TRUE));
		} else {
			$dtResolved = NULL;
		}
		
		$exDowntime = $input->post('exDowntime', TRUE);
		
		//exDowntime
		$minutes = substr($exDowntime, -2);
		$hours = substr($exDowntime, 0, 2);
		
		$exDowntime = ($hours * 60) + $minutes;
		//end
		
		$reason = $input->post('reason', TRUE);
		
		//termCode, dtlog, dtresolved, issuetype, hardwaretype, remarks 
		$result = $this->atm_model->insertIssueLog(
			$termCode,
			$dtLog,
			$issueType,
			$hardwareType,
			$others,
			$downtime,
			$dtResolved,
			$exDowntime,
			$reason,
			$brseqno,
			$userAudit,
			$ipAddress,
			$workstation
		);
		
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		$success = $row['errno'] > 0 ? FALSE : TRUE;
		$message = $row['errmsg'];
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}