<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Replacement extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(REPLACEMENTREQ_NO);
	}
	
	function index()
	{	
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$card = $this->card_model;
		$cache = $this->cache;
		$core = $this->core;
		$input = $this->input;
		
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		$data['cifseqno'] 	= $info['cifseqno'];
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardNo'] 	= $info['cardNo'];
		$data['custName'] 	= $info['custName'];
		$data['cardStatus'] = $info['cardStatDesc'];
		$data['cardType'] 	= $info['cardType'];
		$data['primary'] 	= $info['primary'];
		
		if (!$cardBIN = $cache->get($this->core->getSessionID() . 'cardBINWithFormat')) {
			$result = $card->getCardBINWithFormat();
			$cardBIN = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$cache->save($this->core->getSessionID() . 'cardBINWithFormat', $cardBIN, CACHE_TTL);
		}
		
		$data['cardBIN'] = NULL;
		$cardNoMask = NULL;
		$cardNoPlaceholder = NULL;
		
		foreach ($cardBIN as $row)
		{			
			$val = $row['codevalue'];
			$format = $row['formatvalue'];
			
			$replace = array('-', 'I');
			
			$bCount = NULL;
			foreach (count_chars($format, 1) as $i => $cnt) {
				if (chr($i) === 'B') {
					$bCount = $cnt;
				}
			}
			
			$format = str_replace($replace, '', $format);
			$format = str_replace(array('C', 'P'), 'N', $format);
			//$format = str_replace(str_repeat('B', $bCount), str_pad($this->core->getBranchCode(), '0', $bCount, STR_PAD_LEFT), $format);
			
			if ($cardNoMask === NULL) {
				$mask = str_replace(str_repeat('B', $bCount), str_pad($this->core->getBranchCode(), '0', $bCount, STR_PAD_LEFT), $format);
				$cardNoMask = $mask;
				$cardNoPlaceholder = str_replace('N', '_', $mask);
			}
			
			$data['cardBIN'] .= '<option value="'. $val .'" format="'. $format .'">'. $val .'</option>';
		}
		
		$data['cardNoMask'] = $cardNoMask;
		$data['cardNoPlaceholder'] = $cardNoPlaceholder;
			
		$this->load->view('card/replacement', $data);
	}
	
	function verify()
	{
		$this->load->model('coreapp/card_model');
		
		$card  = $this->card_model;
		$input = $this->input;

		$branchID = $this->core->getBranchID();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$prkey = $input->post('cardBIN', TRUE) . $input->post('cardNo', TRUE);

		if ($this->core->isCoreEncrypt()) {
			$result = $card->getCardToken($prkey,$branchID,$userAudit,$sessionID);

			$row = $result->row_array();

			$result->free_result();
			$result->next_result();

			$tokenid = NULL;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
			if ($row['errno'] > 0) {
				echo json_encode(array(
					'success' => FALSE,
					'message' => $row['errmsg'],
					'errorno' => $row['errno']
				));
				exit();
			} else {
				$tokenid = $row['tokenid'];
				$prkey = $tokenid;
			}
		}
		
		$result = $card->getCardForReplace($prkey,$this->core->isCoreEncrypt());
		
		$row = $result->row_array();
		
		$pseqnoLink = $row['prseqno'];
		$acctDesc   = $row['acctdesc'];
		$errNo      = $row['errno'];
		$errMsg     = $row['errmsg'];
		
		if ($errNo !== '0') {
			$success = FALSE;
			
			switch ($errNo) {
				case '-1':
					$message = 'Card not found';
					break;	
				case '4':
					$message = 'New card already activated. Must be "For Activation" status';
					break;
				default:
					$message = $errMsg .'. Must be "For Activation" status';
					break;
			}
			$cardType = NULL;
		} else {
			$success  = TRUE;
			$cardType = $acctDesc;
			$message  = NULL;
		}
		
		echo json_encode(array(
			'verified' 	 => $success,
			'cardType' 	 => $cardType,
			'pseqnoLink' => $pseqnoLink,
			'message' 	 => $message
		));
	}
	
	function replace()
	{
		$this->load->model('coreapp/card_model');	
		
		$card  = $this->card_model;
		$core  = $this->core;
		$input = $this->input;
		
		$prseqno 	= $input->post('prseqno', TRUE);
		$pseqnoLink = $input->post('pseqnoLink', TRUE);
		$cifseqno 	= $input->post('cifseqno', TRUE);
		$cifseqno	= $cifseqno != '' ? $cifseqno : 0;
		$cifName	= $input->post('custName', TRUE);
		$status 	= $input->post('cboReason', TRUE);
		$dtstat 	= $core->formatDate('H:i:s', 'NOW');
		
		$xml = '<LASTCIF>'. $cifseqno .'</>';
		$xml .= '<LASTNAME>'. $cifName .'</>';
		$xml .= '<DTSTAT>'. $dtstat .'</>';
		
		$prKey2 = $input->post('cardBIN', TRUE) . $input->post('cardNo', TRUE);
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		
		$userAudit  = $core->getUserID();
		$userOverride = $this->session->userdata('userOverride');
		$sessionID	= $core->getSessionID();
		
		$result = $card->cardReplace($prseqno, $pseqnoLink, $cifseqno, $status, $xml, $prKey2, $brseqno, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID);
		$this->session->unset_userdata('userOverride');
		$row = $result->row_array();
		
		$errNo  = $row['errno'];
		$errMsg = $row['errmsg'];
		
		if ($errNo !== '0') {
			$success = FALSE;
			$message = $errMsg;
		} else {
			$success = TRUE;
			$message = 'Card replacement successful';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}
/* End of file replacement.php */
/* Location: ./application/controllers/card/replacement.php */