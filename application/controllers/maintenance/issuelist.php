<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class IssueList extends CI_Controller {
	
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
		$data['userID'] = $this->core->getUserID();
		$data['termCode'] = $termCode;
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/issuelist', $data);
	}
	
	function getData()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/atm_model');
		
		$core = $this->core;
		
		$termCode = $this->input->get('termCode', TRUE);
		$brseqno = $core->getBranchID();
		$userAudit = $core->getUserID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		
		//termCode, brseqno, userID, ipAddress, workstation
		$result = $this->atm_model->getIssueList($termCode, $brseqno, $userAudit, $ipAddress, $workstation);
		
		$resultArr = $result->result_array();
		
		$result->free_result();
		$result->next_result();
						
		$details = array();
		
		if (!$issueTypes = $this->cache->get($this->core->getSessionID() . 'issueTypes')) {
			$result = $this->atm_model->getIssueTypes();
			
			$issueTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'issueTypes', $issueTypes, CACHE_TTL);
		}
		
		if (!$hardwareTypes = $this->cache->get($this->core->getSessionID() . 'hardwareTypes')) {
			$result = $this->atm_model->getHardwareTypes();
			
			$hardwareTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'hardwareTypes', $hardwareTypes, CACHE_TTL);
		}
									
		foreach ($resultArr as $row) {
			$hardwareType = intval($row['hardwaretype']);
			$issueType = intval($row['issuetype']);
			$issueLog = $row['issueseqno'];
			$dtLog = $row['dtlog'];
			$userID = $row['userid'];
			
			if ($row['dtresolved']) {
				$dtResolved = $core->formatDate('F j, Y', $row['dtresolved']);
			} else {
				$dtResolved = 'Unresolved';
			}
			
			$issue = $row['others'];
			
			if ($issueType !== 4) {
				if ( !in_array($issueType, array(1, 4)) ) {
					//issue types
					foreach ($issueTypes as $row) {
						if ($issueType === intval($row['codeseqno'])) {
							$issue = $row['codevalue'];
							break;
						}
					}
				} else {
					if ($hardwareType !== 6) {
						//hardware types
						foreach ($hardwareTypes as $row) {
							if ($hardwareType === intval($row['codeseqno'])) {
								$issue = $row['codevalue'];
								break;
							}
						}
					}
				}
			}
			
			$details[] = array(
				$issueLog,
				$core->formatDate('F j, Y', $dtLog),
				$issue,
				$dtResolved,
				$userID
			);
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => TRUE,
			'details' => $details
		));
	}
	
	function remove()
	{
		$this->load->model('coresys/atm_model');
		
		$core = $this->core;
		
		$issueseqno = $this->input->post('issueseqno', TRUE);
		$termcode = $this->input->post('termcode', TRUE);
		$brseqno = $core->getBranchID();
		$userAudit = $core->getUserID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		
		$result = $this->atm_model->deleteIssueLog(
			$issueseqno,
			$termcode,
			$brseqno,
			$userAudit,
			$ipAddress,
			$workstation
		);
		
		$row = $result->row_array();
		
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = NULL;
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}