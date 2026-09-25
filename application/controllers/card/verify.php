<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Verify extends CI_Controller {

	private $fileVersion = '1.10.00';
	private $coreencrypt = FALSE;

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');

		$this->coreencrypt = $this->core->isCoreEncrypt();

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
	
	function index($type)
	{
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

		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$card = $this->card_model;
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
			
			
			
			$data['cardBIN'] .= '<option value="'. $val .'" format="'. $format .'">'. $val .'</option>';
		}

		$result = $card->getCardType('N');
		$cardType = $result->result_array();
			
		$result->free_result();
		$result->next_result();
				
			//$cache->save($this->core->getSessionID() . 'cardType', $cardType, CACHE_TTL);
		//}
		
		$allows = NULL;
		$format = NULL;
		$format1= NULL;

		$data['cardtype'] = NULL;
		$replace = array('-', 'I');
		
		foreach ($cardType as $row)
		{
			$val  = $row['accttype'];
			$desc = $row['description'];
			//$format = $row['formatvalue'];
			$format = str_replace($replace, '', $row['formatvalue']);
			$format = str_replace('C', 'N', $format);

			$format1= $format1 == NULL ? $format : $format1 ;

			if ($this->core->hasProductCode()) {
				$format = str_replace(array('P'), 'N', $format);
			}
				
			$data['cardtype'] .= '<option value="'. $val .'" format="'.$format.'">'. $desc .'</option>';
		}

		$bCount = NULL;
		foreach (count_chars($format1, 1) as $i => $cnt) {
			if (chr($i) === 'B') {
				$bCount = $cnt;
			}
		}
		
		$format = str_replace($replace, '', $format1);
		$format = str_replace(array('C'), 'N', $format);
		//$format = str_replace(str_repeat('B', $bCount), str_pad($this->core->getBranchCode(), '0', $bCount, STR_PAD_LEFT), $format);
		//$data['prcode'] = FALSE;
		if ($this->core->hasProductCode()) {
			$format = str_replace(array('P'), 'N', $format);
		}
		if ($cardNoMask === NULL) {
			$mask = str_replace(str_repeat('B', $bCount), str_pad($this->core->getBranchCode(), '0', $bCount, STR_PAD_LEFT), $format);
			
			$pCount = NULL;
			foreach (count_chars($mask, 1) as $i => $cnt) {
				if (chr($i) === 'P') {
					$pCount = $cnt;
				}
			}
			$mask = str_replace(str_repeat('P', $pCount), str_pad($cardType[0]['accttype'], '0', $pCount, STR_PAD_LEFT), $mask);

			$cardNoMask = $mask;
			$cardNoPlaceholder = str_replace('N', '_', $mask);

		}
		
		$data['cardNoMask'] = $cardNoMask;
		$data['cardNoPlaceholder'] = $cardNoPlaceholder;

		
		$data['buttons'] = NULL;
		switch ($type)
		{
			case 'info':
				if ($core->checkUserAllowsBtn(CARDINFO)) {
			      $menuPos = CARDINFO;
			    } elseif ($core->checkUserAllowsBtn(CARDUPDATE_NO)) {
			      $menuPos = CARDUPDATE_NO;
			    }
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
			case 'resetpin':
				$menuPos = RESETPIN;
				break;
			default:
				$menuPos = CARDMGMT_NO;
				break;
		}
		
		$core->checkUserAllows($menuPos);

		$data['sessionExp'] = $this->core->getSessionExp();
		
		//$type = $input->get('type', TRUE);
		$data['formAction'] = 'card/verify/submit/'. $type;
		
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
				
		$loginErrorNo = isset($row['errno']) ? (string) $row['errno'] : '';
		if ($loginErrorNo === '8') {
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
				if ($core->checkUserAllowsBtn(CARDINFO)) {
			      $menuPos = CARDINFO;
			    } elseif ($core->checkUserAllowsBtn(CARDUPDATE_NO)) {
			      $menuPos = CARDUPDATE_NO;
			    }
				$page = 'card/info';
				break;
			case 'account':
				$menuPos = ACCNTLINKING_NO;
				$page = 'card/accountlinking';
				break;
			case 'billspayment':
				$menuPos = CARDMGMT_NO;
				$page = 'card/billspayment';
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
			case 'resetpin':
				$menuPos = RESETPIN;
				$page = 'card/resetpin';
				break;
			default:
				$menuPos = CARDMGMT_NO;
				$page = 'card/info';
				break;
		}
		
		$core->checkUserAllows($menuPos);
		
		$details = array();
		
		$cardBIN = $input->post('cardBIN', TRUE) . $input->post('cardNo', TRUE);
		$prkey = NULL;

		if ($core->isCoreEncrypt()) {
			$result = $card->getCardToken($cardBIN,$branchID,$userAudit,$sessionID);

			$row = $result->row_array();

			$result->free_result();
			$result->next_result();

			$tokenid = NULL;
			if ($row['errno'] > 0) {
				echo json_encode(array(
					'success' => FALSE,
					'message' => $row['errmsg'],
					'errorno' => $row['errno'].'xxx'
				));
				exit();
			} else {
				$tokenid = $row['tokenid'];
				$prkey = $cardBIN;
				$cardBIN = $tokenid;
			}
		}
		
		$coreencrypt = $core->isCoreEncrypt();
		
		switch ($type) {
			case 'replace':
				$result = $card->getValidCardForReplacement($cardBIN, $coreencrypt);
				break;
			case 'change':
				$result = $card->getValidCardForChangeStat($cardBIN);
				break;
			default:
				$result = $card->getCardInfo($coreencrypt, $cardBIN, $branchID, $ipAddress, $workstation, $userAudit);
				break;
		}
		
		$row 	 = $result->row_array();
		$errNo 	 = isset($row['errno']) ? (string) $row['errno'] : '';
		$errMsg  = isset($row['errmsg']) ? $row['errmsg'] : '';
		$brseqno = $row['brseqno'];

		$cardBIN = $prkey != NULL ? $prkey : $cardBIN;
		
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
			if ($brseqno !== $branchID && !$core->isHeadOffice()) {
				echo json_encode(array(
					'success' => FALSE, 
					'message' => 'Card maintained by other branch. View information not allowed'
				));
				exit();
			}
		} else {
			echo json_encode(array(
				'success' => FALSE, 
				'message' => $errMsg/*.'[/]'.$this->coreencrypt.' [] '.$tokenid.' [] '.$branchID,
				'params' => $cardBIN.' [] '.$branchID.' [] '.$userAudit.' [] '.$sessionID*/
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

		$_SESSION['cardInfo'] = array(
			'cardNo'		=> array_key_exists('prkey', $row) ? $row['prkey'] : NULL,
			'cifseqno'		=> $row['cifseqno'],
			'brseqno'		=> $row['brseqno'],
			'brname'		=> $row['brname'],
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
			'readonly'		=> FALSE,
			'module'		=> '',
			'xml1'			=> array_key_exists('xml1', $row) ? $row['xml1'] : NULL
		);
		
		//$session->unset_userdata('cardInfo');
		//$session->set_userdata($info);

		echo json_encode(array(
			'success' => TRUE, 
			'page' => $page,
			'type' => $type
		));
	}
}
/* End of file verify.php */
/* Location: ./application/controllers/card/verify.php */
