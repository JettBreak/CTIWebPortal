<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EditApproval extends CI_Controller {
	
	private $fileVersion = '1.10.00';
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CUSTAPPROVAL_NO);
		
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
		
		$this->load->library('version');

		$file = basename(__DIR__) . '/' . basename(__FILE__);

		$verified = $this->version->validate($file, $this->fileVersion);

		if (!$verified) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Module is out of date. Please contact software administrator.'
			));
			exit();
		}
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		//get branches
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$data['branches'] = NULL;
		
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				$data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		
		if ($this->core->isHeadOffice()) {
			$data['uiToolbar'] = "$('.ui-toolbar:even').append($('#customToolbar .top').html());";
		} else {
			$data['uiToolbar'] = NULL;
		}
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('customer/editapproval', $data);
	}
	
	function getData()
	{
		$this->load->model('coreapp/customer_model');
		
		if ($this->core->isHeadOffice()) {
			$brseqno = $this->input->get('brseqno', TRUE);
		} else {
			$brseqno = $this->core->getBranchID();
		}
		
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$override = '';
		$sessionID = $this->core->getSessionID();

		if ($brseqno == -1) {
			$result = $this->customer_model->getCustomerEditForApprovalHO();
		} else {
			$result = $this->customer_model->getCustomerEditForApproval($brseqno);
		}
		
		$details = array();
		
		foreach ($result->result_array() as $row) {
			$details[] = array(
				'<input type="checkbox" name="customers[]" id="'. $row['cifseqno'] .'" value="'. $row['cifseqno'] .':'. $row['custkey'] .'"/>',
				$row['custkey'],
				($row['cifseqno'].' : '.$row['lastname'] .', '. $row['firstname'] .' '. $row['middlename'])
			);
		}
		
		echo json_encode(array(
			'success' => TRUE,
			'details' => $details
		));
	}

	function cache()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		$input = $this->input;
		
		$cifseqno = $input->post('cifseqno', TRUE);
		$custappr = $input->post('custappr', TRUE);
		
		$custInfo = array(
			'cifseqno' => $cifseqno,
			'custappr' => $custappr,
			'tabledata'=> 2
		);
		
		$success = $this->cache->save($this->core->getSessionID() .'cust', $custInfo, CACHE_TTL);
		
		echo json_encode(array(
			'success' => $success,
			'custinfo'=> $custInfo
		));
	}

	function reject()
	{
		$this->load->model('coreapp/customer_model');
		
		$customer = $this->customer_model;
		$core = $this->core;
		$customers = $this->input->post('customers');
		
		$customerX = array();
		
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userID = $core->getUserID();
		$override = '';
		$sessionID = $core->getSessionID();
		
		$customer->db->trans_begin();
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
			
		/*$row = $card
			->checkLogin($userAudit, $sessionID)
			->row_array();
		
		$errno = $row['errno'];
		
		if ($errno !== '8') {
		*/
		$query = array();
		$params = array();
		foreach ($customers as $custinfo) {
			$custkey = substr($custinfo, strpos($custinfo, ':') + 1);
			$cifseqno = substr($custinfo, 0, strpos($custinfo, ':'));
			
			$customersX[] = $cifseqno;
			
			/*$result = $card->verifyAccount(
				$prseqno,
				$prKey,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$override,
				$sessionID
			);*/
			
			$result = $customer->removeRejectedCustomerEdit(
				$custkey,
				$ipAddress,
				$workstation,
				$cifseqno,
				$userAudit,
				$sessionID
			);	

			$row = $result->row_array();

			$params['info'][] = array(
					'custkey' => $custkey,
					'ipAddress' => $ipAddress,
					'workstation' => $workstation,
					'cifseqno' => $cifseqno,
					'userAudit' => $userAudit,
					'sessionID' => $sessionID
				);

			$result->free_result();
			$result->next_result();
		}
		
		if ($customer->db->trans_status() === FALSE) {
			$success = FALSE;
			$message = 'An error has occured';
			$customer->db->trans_rollback();
		} else {
			$success = TRUE;
			$message = 'Customer update successfully removed.';
			$customer->db->trans_commit();
		}
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => 0,//$errno,
			'customers' => $customersX,
			'custlist' => $customers,
			'params' => $params
		));
	}
	
	function verify()
	{
		$this->load->model('coreapp/customer_model');
		
		$customer = $this->customer_model;
		$core = $this->core;
		$customers = $this->input->post('customers');
		
		$customerX = array();
		
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userID = $core->getUserID();
		$override = '';
		$sessionID = $core->getSessionID();
		
		$customer->db->trans_begin();
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
			
		/*$row = $card
			->checkLogin($userAudit, $sessionID)
			->row_array();
		
		$errno = $row['errno'];
		
		if ($errno !== '8') {
		*/
		$query = array();
		$params = array();
		$errcnt = 0;
		foreach ($customers as $custinfo) {
			$custkey = substr($custinfo, strpos($custinfo, ':') + 1);
			$cifseqno = substr($custinfo, 0, strpos($custinfo, ':'));
			
			$customersX[] = $cifseqno;
			
			/*$result = $card->verifyAccount(
				$prseqno,
				$prKey,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$override,
				$sessionID
			);*/
			
			$result = $customer->approvedCustomerUpdate(
				$custkey,
				$ipAddress,
				$workstation,
				$cifseqno,
				$userAudit,
				$sessionID
			);	

			$params['info'][] = array(
					'custkey' => $custkey,
					'ipAddress' => $ipAddress,
					'workstation' => $workstation,
					'cifseqno' => $cifseqno,
					'userAudit' => $userAudit,
					'sessionID' => $sessionID
				);

			$row = $result->row_array();

			if ($row['errno'] > 0) {
				$errcnt++;
			}

			$result->free_result();
			$result->next_result();
		}
		
		if ($customer->db->trans_status() === FALSE || $errcnt > 0) {
			$success = FALSE;
			$message = 'An error has occured';
			$customer->db->trans_rollback();
		} else {
			$success = TRUE;
			$message = 'Customer approved';
			$customer->db->trans_commit();
		}
		/*} else {
			$success = FALSE;
			$message = $row['errmsg'];
		}*/
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => 0,//$errno,
			'customers' => $customersX,
			'custlist' => $customers,
			'params' => $params
		));
	}
}