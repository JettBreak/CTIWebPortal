<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AllowsEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->load->model('coreapp/allows_model');
		
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
		//$this->core->checkUserAllows(MAINTENANCEATM_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		$info = $_SESSION['allows'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		//product Types
		if (!$productTypes = $this->cache->get($this->core->getSessionID() . 'prTypes')) {			
			$result = $this->allows_model->getProductTypes();
			$productTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'prTypes', $productTypes, CACHE_TTL);
		}
		
		$prTypes = NULL;
		$js = array();
		
		if (count($productTypes) > 0) {
			foreach ($productTypes as $row) {
				$prType = $row['prtype'];
				$selected = $prType === $info['prType'] ? ' selected' : NULL;
				$prTypes .= '<option value="'. $prType .'"'. $selected .'>'. $row['description'] .'</option>';
				
				//transaction
				$result = $this->allows_model->getTransactionAllows($prType, $info['trxcode']);
				
				$transaction = NULL;
				if ($result->num_rows() > 0) {
					foreach ($result->result_array() as $r) {
						
						if ($r['trxcode'] === $info['trxcode'] && $prType === $info['prType']) {
							$selected = ' selected';
						} else {
							$selected = NULL;
						}
						
						$transaction .= '<option value="'. $r['trxcode'] .'"'. $selected .'>'. $r['description'] .'</option>';
					}
				} else {
					$transaction = '<option value="">No Transactions Defined</option>';
				}
				
				if (!array_key_exists($prType, $js)) {
					$js[$prType] = NULL;
				}
				
				$js[$prType] = $transaction;
				
				$result->free_result();
				$result->next_result();
				//end
			}
		} else {
			$prTypes = '<option value="">No Product Types Defined</option>';
		}
		$data['prTypes'] = $prTypes;
		//end
		
		$script = '';
		foreach ($js as $key => $val) {
			$script .= "prtype['" . $key . "'] = '" . $val . "';";
		}
		
		$data['js'] = $script;
		$data['bitNo'] = $info['bitNo'];
		$data['transaction'] = $js[$info['prType']];
		$data['disabled'] = ' disabled';
		
		$data['title'] = 'Update Allows Entry';
		$data['waitMsg'] = 'Updating...';
		$data['submitBtnMsg'] = 'Update current entry?';
		$data['formAction'] = 'maintenance/allowsedit/submit';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/allowsx', $data);
	}
	
	function submit()
	{
		$bitNo = $this->input->post('bitNo', TRUE);
		$trxcodex = $this->input->post('transaction', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$result = $this->allows_model->updateAllowsSetup(
			$_SESSION['allows']['prType'],
			$bitNo,
			$trxcodex,
			$_SESSION['allows']['trxcode'],
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
		
		$result->free_result();
		$result->next_result();
				
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}