<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BillsPayment extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(BILLSPAYMENT_NO);
	}
	
	function index()
	{
		$this->load->view('card/billspayment');
	}
	
	function getData()
	{
		$this->load->model('coreapp/card_model');
		
		$info = $_SESSION['cardInfo'];
		
		$prseqno = $info['prseqno'];
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$result = $this->card_model->getCardBillsLink($prseqno, $userAudit, $sessionID);
		
		$row = $result->row_array();
		
		if ($row['errno'] != 8) {
			
			$success = TRUE;
			
			$details = array();
			foreach ($result->result_array() as $row) {			
				$details[] = array(
					$row['bpayseqno'],
					$row['parent'],
					$row['bpayptr'],
					$row['subscriberno'],
					$row['subscribername'],
					$row['statdesc'],
					$row['status'],
					$row['blistseqno']
				);
			}
		} else {
			$success = FALSE;
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => $success,
			'details' => $details
		));
	}
	
	function cache()
	{
		$_SESSION['billCache'] = $_POST;
				
		echo json_encode(array(
			'success' => TRUE
		));
	}
	
	function delete()
	{
		$this->load->model('coreapp/card_model');
		
		$info = $_SESSION['cardInfo'];
		
		$prseqno = $info['prseqno'];
		$blistseqno = $this->input->post('blistseqno', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$result = $this->card_model->deleteBillsPayment($prseqno, $blistseqno, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID);
		
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => $row['errmsg']
		));
	}
}