<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Verification extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDVERIFICATION_NO);

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
			$data['showToolbarFilters'] = TRUE;
		} else {
			$data['showToolbarFilters'] = FALSE;
		}
		$data['sessionExp'] = $this->core->getSessionExp();
		
		$this->load->view('card/verification', $data);
	}
	
	function getData()
	{
		$this->load->model('coreapp/card_model');
		
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
		
		$result = $this->card_model->getCardForVerification(
			$brseqno,
			$ipAddress,
			$workstation,
			$userAudit,
			$override,
			$sessionID
		);
		
		$details = array();
		
		foreach ($result->result_array() as $row) {
			$details[] = array(
				'<input type="checkbox" name="cards[]" id="'. $row['prseqno'] .'" value="'. $row['prkey'] .':'. $row['prseqno'] .'"/>',
				$row['prkey'],
				$row['description'],
				$row['cifseqno'] .': '. $row['lastname'] .', '. $row['firstname'] .' '. $row['middlename'],
				$row['brname'],
				$row['prseqno']
			);
		}
		
		echo json_encode(array(
			'success' => TRUE,
			'details' => $details
		));
	}
	
	function verify()
	{
		$this->load->model('coreapp/card_model');
		
		$card = $this->card_model;
		$core = $this->core;
		$cards = $this->input->post('cards');
		
		$cardsX = array();
		
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userID = $core->getUserID();
		$override = '';
		$sessionID = $core->getSessionID();
		
		$card->db->trans_begin();
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
			
		$row = $card
			->checkLogin($userAudit, $sessionID)
			->row_array();
		
		$errno = $row['errno'];
		
		if ($errno !== '8') {

			$query = array();
			foreach ($cards as $cardNo) {
				$prseqno = substr($cardNo, strpos($cardNo, ':') + 1);
				$prKey = substr($cardNo, 0, strpos($cardNo, ':'));
				
				$cardsX[] = $prseqno;
				
				$result = $card->verifyCard(
					$prseqno,
					$prKey,
					$brseqno,
					$ipAddress,
					$workstation,
					$userAudit,
					$override
				);
				$result->free_result();
				$result->next_result();
			}
			
			if ($card->db->trans_status() === FALSE) {
				$success = FALSE;
				$message = 'An error has occured';
				$card->db->trans_rollback();
			} else {
				$success = TRUE;
				$message = 'Card verification successful';
				$card->db->trans_commit();
			}
		} else {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => $errno,
			'cards' => $cardsX
		));
	}
}