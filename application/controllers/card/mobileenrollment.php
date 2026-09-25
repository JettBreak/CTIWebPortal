<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MobileEnrollment extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MOBILEENROLL_NO);
	}
	
	function index()
	{		
		$this->load->model('coreapp/card_model');
		//$this->load->library('session');
		$this->load->library('shortxml');
		
		$card 	 = $this->card_model;
		//$session = $this->session;
		$core 	 = $this->core;
		$xml 	 = $this->shortxml;
		
		/*if (!$info = $session->userdata('cardInfo')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		};*/

		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardBIN']	= $info['cardBIN'];
		$data['custName'] 	= $info['custName'];
		$data['cardStatus'] = $info['cardStatDesc'];
		$data['cardType'] 	= $info['cardType'];
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->getCardMobileLink($data['cardBIN'], $userAudit, $sessionID);
		$row 	= $result->row_array();
		
		$data['cellseqno'] = $row['prseqno'];
		$data['mobileNo']  = $row['cellno'];
		$data['lastTranx'] = NULL;
		$data['cellxml']   = NULL;
		$data['visibility1'] = NULL;
		$data['visibility2'] = NULL;
		$data['tranAllows'] = NULL;
		
		$mobileStats = strtoupper($row['cellstat']);
		$lastTranx = $core->formatDate('F j, Y g:i A', $row['celldtlst']);
		$cellXML = $row['cellxml'];
		$allows = $row['cellallows'];
		
		$result->free_result();
		$result->next_result();
		
		if ($data['mobileNo']) {		
			$data['mobileStats'] = $mobileStats;
			$data['lastTranx']	 = $lastTranx;
			
			$this->load->library('coreconverters');
			$allows = $this->coreconverters->asciiHexToBin($allows);
			
			$xml->setXML($cellXML);
			$data['cellxml'] = $xml->getValue('LASTTRAN');
		
			$data['visibility2'] = ' class="hidden"';
			
			//mobile allows / notification		
			$result = $card->getDefaultTranAllows('CELL');
	
			foreach ($result->result_array() as $row) {
				$checked = substr($allows, $row['bitno'] - 1, 1) === '1' ? ' checked' : NULL;
				$data['tranAllows'] .= '<input type="checkbox" id="bit'. $row['bitno'] .'" name="allows[]" value="'. $row['bitno'] .'"'. $checked .'/>'. $row['description'] .'<br />';
			}
			
			$result->free_result();
			$result->next_result();
			//end
		} else {
			$data['mobileNo']	 = 'NONE';
			$data['mobileStats'] = 'N/A';
			
			$data['visibility1'] = ' class="hidden"';
		}
		
		$this->load->view('card/mobileenrollment', $data);
	}
	
	function remove()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		
		$card 	 = $this->card_model;
		$core 	 = $this->core;
		$input 	 = $this->input;
				
		$prseqno	= $input->post('prseqno', TRUE);
		$pseqnoLink = $input->post('cellseqno', TRUE);
		$prKey 	 	 = $input->post('mobileNo', TRUE);
		$cardBIN	 = $input->post('cardBIN', TRUE);
		$branchID	 = $core->getBranchID();
		$ipAddress	 = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit	 = $core->getUserID();
		$userOverride = $this->session->userdata('userOverride');
		$sessionID  = $core->getSessionID();
		
		$result = $card->deleteCardMobileLink(
			$pseqnoLink,
			$prseqno,
			$prKey,
			$cardBIN,
			$branchID,
			$ipAddress,
			$workstation,
			$userAudit,
			$userOverride,
			$sessionID
		);
		
		$row = $result->row_array();
		
		if ($row['errno'] === '0') {
			$success = TRUE;
			$message = 'Mobile link successfully removed';
		} else {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}
/* End of file mobileenrollment.php */
/* Location: ./application/controllers/card/mobileenrollment.php */