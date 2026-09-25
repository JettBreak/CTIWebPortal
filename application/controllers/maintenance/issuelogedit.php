<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class IssueLogEdit extends CI_Controller {
	
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
	
	function index($issueseqno)
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/atm_model');
		
		$result = $this->atm_model->getIssueInfo($issueseqno);
		
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		//check userid
		if ($this->core->getUserID() !== $row['userid']) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$issNo = $row['issueseqno'];
		$termCode = $row['termcode'];
		$hardwareType = $row['hardwaretype'];
		$issueType = $row['issuetype'];
		$issueseqno = $row['issueseqno'];
		$others = $row['others'];
		$data['downtime'] = $this->_minutesToHHmm($row['downtime'], TRUE);
		$dtRevolved = $row['dtresolved'];
		$dtLog = $row['dtlog'];
		$data['exDowntime'] = $this->_minutesToHHmm($row['exdowntime'], TRUE);
		$data['reason'] = $row['reason'];
		
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
			$selected = $issueType === $row['codeseqno'] ? ' selected' : NULL;
			$data['issueTypes'] .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
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
			$selected = $hardwareType === $row['codeseqno'] ? ' selected' : NULL;
			$data['hardwareTypes'] .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
		}
		//end
			
		$data['hardwareTypeRow'] = ' class="hidden"';
		if ($issueType === '1') {
			$data['hardwareTypeRow'] = NULL;
		}
		
		$data['exDowntimeRow'] = ' class="hidden"';
		$data['reasonRow'] = ' class="hidden"';
		if ($dtRevolved) {
			$data['exDowntimeRow'] = NULL;
			$data['reasonRow'] = NULL;
		}
		
		$data['othersRow'] = ' class="hidden"';
		$data['othersDefault'] = 0;
		
		$data['others'] = NULL;
		if ($others) {
			$data['othersRow'] = NULL;
			$data['othersDefault'] = 1;
			$data['others'] = $others;
		}
		
		if ($dtRevolved) {
			$data['dtResolved'] = $this->core->formatDate('m/d/Y', $dtRevolved);
			$data['dtResolvedDefault'] = 1;
		} else {
			$data['dtResolved'] = 'Unresolved';
			$data['dtResolvedDefault'] = 0;
		}
		
		$data['hiddenInput'] = '<input type="hidden" name="issueseqno" value="'. $issueseqno .'"/>';
		$data['currentDT'] = $this->core->formatDate('m/d/Y', $dtLog);
		$data['termCode'] = $termCode;
		$data['title'] = 'Edit Issue No. '. $issNo;
		$data['waitMsg'] = 'Updating...';
		$data['formAction'] = 'maintenance/issuelogedit/submit';
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
		
		$issueseqno = $input->post('issueseqno', TRUE);
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
		
		$result = $this->atm_model->updateIssueLog(
			$issueseqno,
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
	
	function _minutesToHHmm($minutes, $padHours = false) {
		$hm = '';
	 
		// there are 60 minutes in an hour, so if we
		// divide total minutes by 60 and throw away
		// the remainder, we've got the number of hours
		$hours = intval(intval($minutes) / 60); 
	 
		// add to $hms, with a leading 0 if asked for
		$hm .= ($padHours) 
			  ? str_pad($hours, 2, '0', STR_PAD_LEFT). ':'
			  : $hours. ':';
	 
		// minutes are simple - just divide the total
		// minutes by 60 and keep the remainder
		$minutes = intval($minutes % 60); 
	 
		// add to $hms, again with a leading 0 if needed
		$hm .= str_pad($minutes, 2, '0', STR_PAD_LEFT);
	 
		return $hm;
	}
}