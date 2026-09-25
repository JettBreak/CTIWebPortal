<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AllowsNew extends CI_Controller {
	
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
				
				$prTypes .= '<option value="'. $prType .'">'. $row['description'] .'</option>';
				
				//transaction
				$result = $this->allows_model->getTransactionAllows($prType, 0);
				
				$transaction = NULL;
				if ($result->num_rows() > 0) {
					foreach ($result->result_array() as $r) {
						$transaction .= '<option value="'. $r['trxcode'] .'">'. $r['description'] .'</option>';
					}
				} else {
					$transaction = '<option value="">No Transactions Defined</option>';
				}
				
				if (!array_key_exists($prType, $js)) {
					$js[$prType] = NULL;
				}
				
				$js[$prType] = $transaction;
				$data['transaction'] = $js['ACCT'];
				$result->free_result();
				$result->next_result();
				//end
			}
		} else {
			$data['transaction'] = NULL;
			$prTypes = '<option value="">No Product Types Defined</option>';
		}
		$data['prTypes'] = $prTypes;
		//end
		
		$script = '';
		foreach ($js as $key => $val) {
			$script .= "prtype['" . $key . "'] = '" . $val . "';";
		}
		
		$data['js'] = $script;
		
		$newbitno = $_SESSION['lastBitNo'] + 1;
		if ($newbitno > 64) {
			$newbitno = $_SESSION['lastBitNo'];
		}
		$data['bitNo'] = $newbitno;
		
		$data['disabled'] = NULL;
		
		$data['title'] = 'New Allows Entry';
		$data['waitMsg'] = 'Submitting form...';
		$data['submitBtnMsg'] = 'Are all entries correct?';
		$data['formAction'] = 'maintenance/allowsnew/submit';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/allowsx', $data);
	}
	
	function submit()
	{
		$prtype = $this->input->post('prtype', TRUE);
		$bitNo = $this->input->post('bitNo', TRUE);
		$trxcodex = $this->input->post('transaction', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$result = $this->allows_model->insertAllowsSetup(
			$prtype,
			$bitNo,
			$trxcodex,
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
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}