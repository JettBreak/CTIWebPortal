<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SvcCodeEdit extends CI_Controller {
	
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
		$info = $_SESSION['svccode'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$types = NULL;
		foreach ($this->core->getChargeTypes() as $val => $desc) {
			$selected = $info['type'] === $val ? ' selected' : NULL;
			$types .= '<option value="'. $val .'"'. $selected .'>'. $desc .'</option>';
		}
		
		$data['chargeTypeAttr'] = ' disabled';
		$data['trxcodeAttr'] = ' disabled';
		
		$data['chargeTypes'] = $types;
		
		$data['description'] = $info['desc'];
		$data['mnemonic'] = $info['mnemonic'];
		$data['trxcode'] = $info['trxcode'];
		
		$data['title'] = 'Update Service Code';
		$data['submitBtnMsg'] = 'Update?';
		$data['waitMsg'] = 'Updating...';
		$data['formAction'] = 'maintenance/svccodeedit/submit';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/svccodex', $data);
	}
	
	function submit()
	{
		$info = $_SESSION['svccode'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$this->load->model('coreapp/svccode_model');
		
		$trxcode = $info['trxcode'];
		$desc = $this->input->post('desc', TRUE);
		$mnemonic = strtoupper($this->input->post('mnemonic', TRUE));
		$type = $info['type'];
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$result = $this->svccode_model->updateServiceCode(
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
			$message = 'Updated successfully';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}