<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ResetPIN extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(RESETPIN);
	}
	
	function index()
	{		
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardNo'] 	= $info['cardBIN'];
		$data['custName'] 	= $info['custName'];
		$data['cardStatus'] = $info['cardStatDesc'];
		$data['cardType'] 	= $info['cardType'];
		$data['primary'] 	= $info['primary'];

		$this->load->view('card/resetpin', $data);
	}
	
	function process()
	{
		$this->load->model('coresys/misc_model');
		$this->load->library('shortxml');
		$this->load->library('session');
		
		$xml = $this->shortxml;
		
		$status = 1;
		$msgtype = 20; //processing
		$trxcode = 938889; //reset PIN
		$prtype = 'CARD';
		$prkey = $this->input->post('prkey', TRUE);
		$xml1 = '';
		$userAudit = $this->core->getUserID();
		$override = $this->session->userdata('userOverride');
		$workstation = $this->core->getWorkstation();
		
		$result = $this->misc_model->insertDefaultPINGenBatch($status, $msgtype, $trxcode, $prtype, $prkey, $xml1, $userAudit, $override, $workstation);
		
		$row = $result->row_array();
			
		$bchxno = $row['batchseqno'];
		
		$ctr = 0;
		while ($msgtype === 20) {
			$ctr++;
			$result = $this->misc_model->getBatchStatus($bchxno);
			$row = $result->row_array();
			
			$xml->setXML($row['xml1']);
			
			$msgtype = intval($row['msgtype']);
			
			$result->free_result();
			$result->next_result();
			
			if ($ctr > 100) {
				echo json_encode(array(
					'success' => FALSE,
					'message' => 'An error has occured while validating subscriber no. Please try again',
					'msgType' => $msgtype
				));
				exit;
			}
			
			usleep(100000);
		}
		
		switch($msgtype) {
			case 21:
				$success = TRUE;
				$message = 'PIN was successfully reset';
				break;
			case 23:
				$success = FALSE;
				$message = $xml->getValue('SYSVDESC');
				break;
			default:
				$success = FALSE;
				$message = $xml->getValue('SYSVDESC');
				break;
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}