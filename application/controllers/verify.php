<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Verify extends CI_Controller {
	
	function index($type)
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->library('core');
		
		$cache = $this->cache;
		$core = $this->core;
		$input = $this->input;
		
		$data['buttons'] = NULL;
		switch ($type)
		{
			case 'info':
				$menuPos = CARDINFO;
				$data['buttons'] = '<button id="browseBtn">Browse</button>';
				break;
			case 'account':
				$menuPos = ACCNTLINKING_NO;
				break;
			case 'mobile':
				$menuPos = MOBILEENROLL_NO;
				break;
			case 'replace':
				$menuPos = REPLACEMENTREQ_NO;
				break;
			case 'change':
				$menuPos = CHANGECARDSTAT_NO;
				break;
			default:
				$menuPos = CARDMGMT_NO;
				break;
		}
		
		$core->checkUserAllows($menuPos);
		
		//$type = $input->get('type', TRUE);
		$data['formAction'] = 'card/verify/submit/'. $type;
		
		if (!$cardBIN = $cache->get('cardBIN')) {
			$this->load->model('coreapp/card_model');

			$cardBIN = $this->card_model->getCardBIN()->result_array();
			$cache->save('cardBIN', $cardBIN, CACHE_TTL);
		}
		
		$branchID = $core->getBranchID();
		
		$data['cardBIN'] = NULL;
		foreach ($cardBIN as $row)
		{
			//add zeros to cardBIN
			$len = 3 - strlen($branchID);
			$zeros = NULL;
			for ($i = 1; $i <= $len; $i++) {
				$zeros .= '0'; 
			}
			//end
			
			$newCardNo = $row['codevalue'] . $zeros . $branchID;
			$data['cardBIN'] .= '<option value="'. $newCardNo .'">'. $newCardNo .'</option>';
		}
		
		//$this->output->cache(CACHE_TTL);
		$this->load->view('card/verify', $data);
	}
	
	function submit($type)
	{
		$this->load->model('coreapp/card_model');
		//$this->load->library('session');
		$this->load->library('core');
		$this->load->library('shortxml');
		
		//$session = $this->session;
		$core	 = $this->core;
		$card	 = $this->card_model;
		$xml 	 = $this->shortxml;
		$input 	 = $this->input;
		
		$branchID = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->checkLogin($userAudit, $sessionID);
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
				
		if ($row['errno'] === '8') {
			echo json_encode(array(
				'success' => FALSE,
				'message' => $row['errmsg'],
				'errorno' => $row['errno']
			));
			exit();
		}
		
		switch ($type)
		{
			case 'info':
				$menuPos = CARDINFO;
				$page = 'card/info';
				break;
			case 'account':
				$menuPos = ACCNTLINKING_NO;
				$page = 'card/accountlinking';
				break;
			case 'mobile':
				$menuPos = MOBILEENROLL_NO;
				$page = 'card/mobileenrollment';
				break;
			case 'replace':
				$menuPos = REPLACEMENTREQ_NO;
				$page = 'card/replacement';
				break;
			case 'change':
				$menuPos = CHANGECARDSTAT_NO;
				$page = 'card/statuschange';
				break;
			default:
				$menuPos = CARDMGMT_NO;
				$page = 'card/info';
				break;
		}
		
		$core->checkUserAllows($menuPos);
		
		$details = array();
		
		$cardBIN = $input->post('cardBIN1', TRUE) . $input->post('cardBIN2', TRUE);
		
		switch ($type) {
			case 'replace':
				$result = $card->getValidCardForReplacement($cardBIN);
				break;
			case 'change':
				$result = $card->getValidCardForChangeStat($cardBIN);
				break;
			default:
				$result = $card->getCardInfo($cardBIN, $branchID, $ipAddress, $workstation, $userAudit);
				break;
		}
		
		$row 	 = $result->row_array();
		$errNo 	 = $row['errno'];
		$errMsg  = $row['errmsg'];
		$brseqno = $row['brseqno'];
		
		if ($type !== 'info') {
			if ($errNo !== '0') {
				echo json_encode(array(
					'success' => FALSE,
					'message' => $errMsg
				));
				exit();
			}
		}
		
		//if customer is ***GENERIC***
		if ($type === 'mobile') {
			if ($row['cifseqno'] === NULL) {
				echo json_encode(array(
					'success' => FALSE,
					'message' => 'Customer record not found'
				));
				exit();
			}
		}
		
		//check branch ID
		
		if ($errNo !== '-1') {
			if ($brseqno !== $branchID) {
				echo json_encode(array(
					'success' => FALSE, 
					'message' => 'Card maintained by other branch. View information not allowed'
				));
				exit();
			}
		} else {
			echo json_encode(array(
				'success' => FALSE, 
				'message' => $errMsg
			));
			exit();
		}
		
		$xml->setXML($row['acctxml']);
		
		$acctno = $xml->getValue('ACCTNO');
		$acct   = $xml->getValue('ACCT');
		
		if ($acctno === 'NONE') {
			$primay = $acctno;
		} else {
			$primay = $acct .'-'. $acctno;
		}
		session_start();
		$_SESSION['info'] = array(
			'cardNo'		=> array_key_exists('prkey', $row) ? $row['prkey'] : NULL,
			'cifseqno'		=> $row['cifseqno'],
			'brseqno'		=> $row['brseqno'],
			'prseqno'		=> $row['prseqno'],
			'cardBIN'		=> $cardBIN,
			'custName' 		=> ($row['cifseqno'] == TRUE ? $row['lname'] .', '. $row['fname'] .' '. $row['mname'] : '***GENERIC***'), //if cifseqno is NULL then name = ***GENERIC***
			'cardStatus' 	=> $row['status'],
			'cardStatDesc'	=> strtoupper($row['statdesc']),
			'cardType' 		=> $row['acctdesc'],
			'dateInitIssue' => $core->formatDate('F j, Y g:i A', $row['dtenroll']),
			'issueCount' 	=> $row['issuecnt'],
			'dateActivated' => $core->formatDate('F j, Y g:i A', $row['dtactive']),
			'dateExpiry' 	=> $core->formatDate('F j, Y', $row['dtexpire']),
			'lastActivity' 	=> $core->formatDate('F j, Y g:i A', $row['lastact']),
			'primary'		=> $primay,
			'allows'		=> array_key_exists('allows', $row) ? $row['allows'] : NULL,
			'acctType'		=> $row['accttype'],
			'pinctr'		=> array_key_exists('pinctr', $row) ? $row['pinctr'] : NULL,
			'pinctrmax'		=> array_key_exists('pinctrmax', $row) ? $row['pinctrmax'] : NULL,
			'xml1'			=> array_key_exists('xml1', $row) ? $row['xml1'] : NULL
		);
		
		//$session->unset_userdata('cardInfo');
		//$session->set_userdata($info);

		echo json_encode(array(
			'success' => TRUE, 
			'page' 	  => $page
		));
	}
}
/* End of file verify.php */
/* Location: ./application/controllers/card/verify.php */