<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SvcCodeNew extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(MAINTENANCEATM_NO);
		
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
		$types = NULL;
		foreach ($this->core->getChargeTypes() as $val => $desc) {
			$types .= '<option value="'. $val .'">'. $desc .'</option>';
		}
		
		$data['chargeTypeAttr'] = NULL;
		$data['trxcodeAttr'] = NULL;
		
		$data['chargeTypes'] = $types;
		
		$data['description'] = NULL;
		$data['mnemonic'] = NULL;
		
		$newtrxcode = $_SESSION['lastTrxcode'];
		if ($newtrxcode > 19999) {
			$newtrxcode = $_SESSION['lastTrxcode'];
		}
		
		$data['trxcode'] = $newtrxcode;
		
		$data['title'] = 'New Service Code';
		$data['submitBtnMsg'] = 'Create?';
		$data['waitMsg'] = 'Creating...';
		$data['formAction'] = 'maintenance/svccodenew/submit';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/svccodex', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/svccode_model');
		
		$trxcode = $this->input->post('trxcode', TRUE);
		$desc = $this->input->post('desc', TRUE);
		$mnemonic = strtoupper($this->input->post('mnemonic', TRUE));
		$type = $this->input->post('chargeType', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$result = $this->svccode_model->insertServiceCode(
			$trxcode,
			$desc,
			$mnemonic,
			$type,
			$brseqno,
			$ipAddress,
			$workstation,
			$userAudit,
			$sessionID
		);
		
		$row = $result->row_array();
		
		if (intval($row['errno']) > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Created successfully';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}