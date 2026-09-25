<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDINFO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		//$this->load->library('session');
		$this->load->library('shortxml');
		$this->load->library('coreconverters');
		
		$card	 = $this->card_model;
		$cache	 = $this->cache;
		//$session = $this->session;
		$core 	 = $this->core;
		$xml 	 = $this->shortxml;
		
		//$info 	 = $session->userdata('cardInfo');
		session_start();
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		//$data['prseqno'] = $info['prseqno'];
		$data['cardNo']	= $info['cardNo'];
		//$data['cardNox'] = substr($info['cardNo'], -7);
		
		$xml->setXML($info['xml1']);
		
		//cardNo
		/*if (!$cardBIN = $cache->get('cardBIN')) {
			$result = $card->getCardBIN();
			$cardBIN = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$cache->save('cardBIN', $cardBIN, CACHE_TTL);
		}
		
		$data['cardBIN'] = NULL;
		foreach ($cardBIN as $row)
		{
			$selected = $info['cardBIN'] === $row['codevalue'] ? ' selected' : NULL;
			$data['cardBIN'] .= '<option'. $selected .'>'. $row['codevalue'] .'</option>';
		}*/
		//end
		
		//card type
		/*if (!$cardType = $cache->get('cardType')) {			
			$result = $card->getCardType('N');
			$cardType = $result->result_array();
			
			$result->free_result();
			$result->next_result();
				
			$cache->save('cardType', $cardType, CACHE_TTL);
		}*/
		
		$data['cardType'] = $info['cardType'];
		/*foreach ($cardType as $row)
		{
			$val  = $row['accttype'];
			$desc = $row['description'];
			
			$result = $card->getDefaultAllows($val);
			$row = $result->row_array();
			
			if ($result->num_rows() > 0) {
				$defAllows = rtrim($this->coreconverters->asciiHexToBin($row['allows']), 0);
			} else {
				$defAllows = 0;
			}
			
			$result->free_result();
			$result->next_result();
			
			$selected = $info['cardType'] === $desc ? ' selected' : NULL;
			
			$data['cardType'] .= '<option value="'. $val .'" defaultallows="'. $defAllows .'"'. $selected .'>'. $desc .'</option>';
		}*/
		//end
		
		$data['prseqno'] = $info['prseqno'];
		
		$data['defFastCashVal'] = $xml->getValue('FCASH') ? $core->currency($xml->getValue('FCASH')) : '0.00';
		$data['defFastCashAttr'] = NULL;
		$data['branchID']		= str_pad($core->getBranchID(), 3, '0', STR_PAD_LEFT);
		
		$data['custName'] 		= $info['custName'];
		$data['embossName']		= $xml->getValue('MBOS');
		$cardStatusDesc			= $info['cardStatDesc'];
		$data['cardStatus'] 	= $cardStatusDesc;
		$data['dtInitIssue'] 	= $core->formatDate('F j, Y', $info['dateInitIssue']);
		$data['issueCount'] 	= $info['issueCount'];
		$data['dtActivated'] 	= $info['dateActivated'] ? $core->formatDate('F j, Y', $info['dateActivated']) : 'NOT YET ACTIVATED';
		$data['dtExpiry'] 		= $info['dateExpiry'];
		$data['lastActivity'] 	= $info['lastActivity'] ? $info['lastActivity'] : 'NONE';
		$data['branch'] 		= $core->getBranchName();
		
		//tran allows
		$result = $card->getDefaultTranAllows('80');
		
		$allows = $this->coreconverters->asciiHexToBin($info['allows']);
		$data['tranAllows'] = NULL;
		foreach ($result->result_array() as $row) {
			$checked = substr($allows, $row['bitno'] - 1, 1) === '1' ? ' checked' : NULL;
			$data['tranAllows'] .= '<input type="checkbox" id="bit'. $row['bitno'] .'" name="allows[]" value="'. $row['bitno'] .'"'. $checked .'/>'. $row['description'] .'<br />';
		}

		$result->free_result();
		$result->next_result();
		//end
		
		//channel locking		
		$result = $card->getChannelLocks();
		
		$data['allows'] = NULL;
		foreach ($result->result_array() as $row) {
			$checked = $xml->getValue($row['xml1']) === 'Y' ? ' checked' : NULL;
			$data['allows'] .= '<input type="checkbox" name="ch'. $row['xml1'] .'"'. $checked .'/>'. $row['codevalue'] .'<br />';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//def fast cash
		$result = $card->getDefFastCash();
		$row = $result->row_array();
		
		$data['defFastCash'] = NULL;
		$data['defFastCashAttr'] = ' disabled';
			
		if ($row['dfc'] === 'Y') {
			$data['defFastCashAttr'] = NULL;
		}
		
		foreach ($this->_getDefFastCash() as $fType => $fDesc) {
			if ($row['dfc'] === 'Y' && $fType == 2) {
				break;
			}
			$selected = intval($xml->getValue('FTYPE')) === $fType ? ' selected' : NULL;
			$data['defFastCash'] .= '<option value="'. $fType .'"'. $selected .'>'. $fDesc .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//card status
		if (!$cardStatus = $cache->get('cardStatus')) {			
			$result = $card->getCardStatus();
			$cardStatus = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$cache->save('cardStatus', $cardStatus, CACHE_TTL);
		}
		$cardStatusx = array();
		
		foreach ($cardStatus as $key => $row) {
			$status = $row['status'];
			$desc = $row['description'];
			$acctType = $row['accttype'];
			$isEditable = $row['iseditable'];
			
			if ($info['acctType'] === $acctType && $isEditable === 'Y') {
				$cardStatusx[$status]['description'] = $desc;
				$cardStatusx[$status]['acctTypes'][$acctType] = array('isEditable' => $isEditable);
			}
		}
		
		$cardStatus = $info['cardStatus'];
		if ( in_array($cardStatus, array_keys($cardStatusx)) ) {
			$data['cardStatus'] = '<select name="cardStatus" id="cardStatus" style="width:212px">';
			foreach ($cardStatusx as $status => $row) {
				$desc = $row['description'];
				$selected = intval($cardStatus) === $status ? ' selected' : NULL;
				$data['cardStatus'] .= '<option value="'. $status .'"'. $selected .'>'. strtoupper($desc) .'</option>';
			}
			$data['cardStatus'] .= '</select>';
		} else {
			$data['cardStatus'] = '<input type="hidden" name="cardStatus" value="'. $cardStatus .'" readonly/>';
			$data['cardStatus'] .= '<input type="text" id="cardStatus" style="width: 200px" value="'. $cardStatusDesc .'" readonly/>';
		}
		//end
		
		//$data['cardStatus'] = array_key_exists('10', $cardStatusx) ? 'Y' : 'N';
		
		//accounts linked
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->getCardAccountLink($info['prseqno'], $userAudit, $sessionID);
		
		$data['accountLink'] = NULL;
		
		foreach ($result->result_array() as $row)
		{
			$xml->setXML($row['xml1']);
			
			$num 		= $row['prptr'];
			$accntType 	= $xml->getValue('ACCTTYPE');
			$accntNo 	= $xml->getValue('ACCTNO');
			$authType 	= $xml->getValue('AUTHTYPE');
			$primary 	= $xml->getValue('PRIMARY');
			
			$data['accountLink'] .= '<tr>'.
				'<td>'. $num .'</td>'.
				'<td>'. $accntType .'</td>'.
				'<td>'. $accntNo .'</td>'.
				'<td>'. $authType .'</td>'.
				'<td>'. $primary .'</td>'.
			'</tr>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//bills payment
		$result = $card->getCardBillsLink($info['prseqno'], $userAudit, $sessionID);
		
		$data['billsLink'] = NULL;		
		$parentx = NULL;	
		
		foreach ($result->result_array() as $row)
		{
			$xml->setXML($row['xml1']);
			
			$inst 		= $row['parent'];
			$ptr	 	= $row['bpayptr'];
			$subsNo	 	= $row['subscriberno'];
			$subsName 	= $row['subscribername'];
			$parent 	= $row['parentseqno'];
			
			if ($parent != $parentx) {
				$parentx 	= $parent;
				$data['billsLink'] .= '<tr id="'. $parent .'">'.
					'<td>'. $inst.'</td>'.
					'<td></td>'.
					'<td></td>'.
					'<td></td>'.
					'<td></td>'.
				'</tr>';
			}
				
			$data['billsLink'] .= '<tr idref="'. $parent .'">'.
				'<td>'. $inst.'</td>'.
				'<td>'. $ptr .'</td>'.
				'<td>'. $subsNo .'</td>'.
				'<td>'. $subsName .'</td>'.
				'<td>'. $parent .'</td>'.
			'</tr>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//online limits
		$result = $card->getCardLimitList($info['prseqno']);
		
		$data['onlineLimits'] = NULL;		
		
		$limitseqno = NULL;
		foreach ($result->result_array() as $row)
		{
			if ($row['isamtlimit'] === 'N') {
				$cycleAvail = 'N/A';
				$cycleMax = 'N/A';
				$tranMin = 'N/A';
				$tranMax = 'N/A';
				$nonFeeTranAvail = 'N/A';
				$nonFeeTranMax = 'N/A';
			} else {
				$cycleAvail = $core->currency($row['cycleavail']);
				$cycleMax = $core->currency($row['cyclemax']);
				$tranMin = $core->currency($row['tranmin']);
				$tranMax = $core->currency($row['tranmax']);
				$nonFeeTranAvail = $core->currency($row['nonfeetranavail']);
				$nonFeeTranMax = $core->currency($row['nonfeetranmax']);
			}
			
			if ($limitseqno === NULL) {
				$limitseqno = $row['limitseqno'];
			}
			
			$data['onlineLimits'] .= '<tr>'.
				'<td>'. $row['description'] .'</td>'.
				'<td>'. $row['limitseqno'] .'</td>'.
				'<td>'. $row['trxcode'] .'</td>'.
				'<td>'. $cycleAvail .'</td>'.
				'<td>'. $cycleMax .'</td>'.
				'<td>'. ($row['ctravail'] ? $row['ctravail'] : 0) .'</td>'.
				'<td>'. ($row['ctrmax'] ? $row['ctrmax'] : 0) .'</td>'.
				'<td>'. $tranMin .'</td>'.
				'<td>'. $tranMax .'</td>'.
				'<td>'. ($row['cycle'] ? $row['cycle'] : 0) .'</td>'.
				'<td>'. ($row['duralimit'] ? $row['duralimit'] : 0) .'</td>'.
				'<td>'. ($row['nonfeectravail'] ? $row['nonfeectravail'] : 0) .'</td>'.
				'<td>'. ($row['nonfeectrmax'] ? $row['nonfeectrmax'] : 0) .'</td>'.
				'<td>'. $nonFeeTranAvail .'</td>'.
				'<td>'. $nonFeeTranMax .'</td>'.
				'<td>'. ($row['nonfeecycle'] ? $row['nonfeecycle'] : 0) .'</td>'.
			'</tr>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//card limits
		$brseqno = $core->getBranchID();
		$result = $card->getCardLimits($info['acctType'], 'ONLN', $brseqno);
		
		$data['limitID'] = NULL;
		
		$data['pinRetryCnt'] = $info['pinctr'];
		$data['pinMaxRetryCnt'] = $info['pinctrmax'];
		
		$data['limitDesc'] = NULL;
		foreach ($result->result_array() as $row)
		{
			if ($row['limitseqno'] === $limitseqno) {
				$data['limitDesc'] = $row['description'];
				$selected = ' selected';
			} else {
				$selected = NULL;
			}
			
			$data['limitID'] .= '<option value="'. $row['limitseqno'] .'" pinmaxretry="'. $row['pinctrdef'] .'"'. $selected .'>'. $row['description'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//reset limits button
		$grpseqno = $core->getUserGroup();
		$hiddenBtn = '<button id="resetLimitsBtn" class="hidden">Reset Limits</button>';
		$data['resetLimitsBtn'] = in_array($grpseqno, array(1,2)) ? $hiddenBtn : NULL;
		//
		$this->load->view('card/info', $data);
	}
	
	function _getDefFastCash()
	{
		return array(
			0 => 'SAVINGS',
			1 => 'CURRENT',
			2 => 'BY ACCT PTR.'
		);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('coreconverters');
		//$this->load->library('session');
		
		//$info = $this->session->userdata('cardInfo');
		$core = $this->core;
		$input = $this->input;
		$branchID = $core->getBranchID();
		
		$prseqno = $input->post('prseqno', TRUE);
		$status = $input->post('cardStatus', TRUE);
		
		$allows = $this->input->post('allows');
		
		if ($allows) {
			$max = max($allows);
			$bin = '';
			for ($i = 1; $i <= $max; $i++) {	
				if (in_array($i, $allows)) {
					$bin .= '1';
				} else {
					$bin .= '0';
				}
			}
		} else {
			$bin = '0';
		}
		
		$hex = $this->coreconverters->asciiBinToHex($bin);
		
		//$initAllows = $hex;//'DFFFFFFBFFFF0000';//$input->post('initAllows', TRUE);
		$fCash = str_replace(',', '', $input->post('fCash', TRUE)); //format 0.00
		$fType = $input->post('fType', TRUE);
		$fPtr = 0;
		
		$atmLock = $input->post('chATMLOCK', TRUE) ? 'Y' : 'N';
		$posLock = $input->post('chPOSLOCK', TRUE) ? 'Y' : 'N';
		$webLock = $input->post('chWEBLOCK', TRUE) ? 'Y' : 'N';
		$cellLck = $input->post('chCELLLOCK', TRUE) ? 'Y' : 'N';
		
		$xml = '<ATMLOCK>'. $atmLock .'</>'.
			'<POSLOCK>'. $posLock .'</>'.
			'<WEBLOCK>'. $webLock .'</>'.
			'<CELLLOCK>'. $cellLck .'</>';
			
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$override = '';
		$sessionID = $core->getSessionID();
		
		$result = $this->card_model->updateCard(
			$prseqno,
			$status,
			$hex,
			$fCash,
			$fType,
			$fPtr,
			$xml,
			$brseqno,
			$ipAddress,
			$workstation,
			$userAudit,
			$override,
			$sessionID
		);
		
		$row = $result->row_array();
		
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Card successfully updated';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => $row['errno']/*,
			'hex' => $hex,
			'xml' => $xm
			,'prseqno' => $prseqnol*/
		));
	}
}
/* End of file info.php */
/* Location: ./application/controllers/card/info.php */