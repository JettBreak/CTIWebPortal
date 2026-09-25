<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Issuance extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDISSUANCE_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		
		$card = $this->card_model;
		
		if ($cust = $this->cache->get($this->core->getSessionID() . 'cust')) {
			
			$cache = $this->cache;
			$core = $this->core;
			$input = $this->input;
					
			$data['cifseqno'] = $cust['cifseqno'];
			$data['custName'] = $cust['fullName'];
			
			$data['newCardNo'] = NULL;

			$cache = $this->cache;
			$core = $this->core;
			$input = $this->input;
			
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
			$currentBrCode = NULL;
			
			if (count($branches) > 0) {
				foreach ($branches as $row) {
					//if user branch is not allowed to monitor users from other branches
					$matched = $row['brseqno'] === $this->core->getBranchID() ? TRUE : FALSE;
					
					if (!$this->core->isHeadOffice() && $matched) {
						$data['branches'] = '<option value="'. $row['brcode'] .'">'. $row['brname'] .'</option>';
						break;
					}
					
					if ($matched) {
						$selected = ' selected';
						$currentBrCode = $row['brcode'];
					} else {
						$selected = NULL;
					}
					
					$data['branches'] .= '<option value="'. $row['brcode'] .'"'. $selected .'>'. $row['brname'] .'</option>';
				}
			} else {
				$data['branches'] = '<option value="">No Branches Defined</option>';
			}
			//end
			
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
			
			$this->load->view('card/issuance', $data);
		} else {
			$this->load->helper('url');
			redirect('welcome');
		}
	}
	
	function verify()
	{	
		$this->load->model('coreapp/card_model');
		
		$card  = $this->card_model;
		$input = $this->input;
		
		$prKey = $input->post('cardBIN', TRUE) . $input->post('cardNo', TRUE);
		
		$result = $card->getValidCardForActivation($prKey);
		$row = $result->row_array();
		
		$errNo = $row['errno'];
		
		switch ($errNo) {
			case '-1':
				$verified = FALSE;
				$message  = $row['errmsg'];
				$prseqno  = NULL;
				$acctType = NULL;
				$cardStat = NULL;
				$cardType = NULL;
				break;
			case '0':
				$verified = TRUE;
				$prseqno  = $row['prseqno'];
				$acctType = $row['accttype'];
				$cardStat = strtoupper($row['statdesc']);
				$cardType = $row['acctdesc'];
				$message  = NULL;
				break;
			default:
				$verified = FALSE;
				$message  = $row['errmsg'] .'. must be "For Activation" status';
				$prseqno  = NULL;
				$acctType = NULL;
				$cardStat = NULL;
				$cardType = NULL;
				break;
		}
		
		echo json_encode(array(
			'verified' => $verified, 
			'message'  => $message,
			'prseqno'  => $prseqno,
			'acctType' => $acctType,
			'cardStat' => $cardStat,
			'cardType' => $cardType
		));
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$card  = $this->card_model;
		$core  = $this->core;
		$input = $this->input;
		
		$prseqno   = $input->post('prseqno', TRUE);
		$prKey	   = $input->post('cardBIN', TRUE) . $input->post('cardNo', TRUE);
		$cifseqno  = $input->post('cifseqno', TRUE);
		$custName  = $input->post('custName', TRUE);
		$acctType  = $input->post('acctType', TRUE);
		$branchID  = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
				
		$result = $card->activateCard(
			$prseqno,
			$prKey,
			$cifseqno,
			$custName,
			$acctType,
			$branchID,
			$ipAddress,
			$workstation,
			$userAudit,
			$sessionID
		);
		
		$row = $result->row_array();
		
		$errNo = $row['errno'];
		
		if ($errNo === '0') {
			$success = TRUE;
			$message = 'Card successfully activated';
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
?>