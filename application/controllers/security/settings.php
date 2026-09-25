<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(GENERALSETTINGS_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');
		
		$this->card_model->db->trans_begin();
		
		$core = $this->core;
		$xml = $this->shortxml;
		
		$brseqno = $core->getBranchID();
		$userAudit = $core->getUserID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$override = '';
		$sessionID = $core->getSessionID();
		
		//card BIN
		if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
			$result = $this->card_model->getCardBIN();
			
			$cardBIN = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'cardBIN', $cardBIN, CACHE_TTL);
		}
		
		$first = current($cardBIN);
		
		$data['cardBIN'] = NULL;
		$data['formatValue1'] = NULL;
		$data['weights1'] = NULL;
		$data['weights1MaxLength'] = NULL;
		$data['expYears1'] = NULL;
		$data['gracePeriod1'] = NULL;
		$data['minDays1'] = NULL;
		
		foreach ($cardBIN as $row)
		{
			$codevalue = $row['codevalue'];
			
			$result = $this->card_model->getAccountFormat(
				$codevalue,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$override,
				$sessionID
			);
			
			$row = $result->row_array();
			
			$formatValue = NULL;
			$weights = $xml->getValue('WEIGHTS');
			$expYears = $xml->getValue('EXPYEARS') ? $xml->getValue('EXPYEARS') : 0;
			$gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
			$minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;
			
			if ($result->num_rows() > 0) {
			
			$xml->setXML($row['xml1']);
			$formatValue = $row['formatvalue'];
			$weights = $xml->getValue('WEIGHTS');
			$expYears = $xml->getValue('EXPYEARS') ? $xml->getValue('EXPYEARS') : 0;
			$gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
			$minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;
			
			}
			
			if ($codevalue === $first['codevalue']) {
				$data['formatValue1'] = $formatValue;
				$data['weights1'] = $weights;
				
				$charLen = strlen(str_replace(array('-', 'C'), '', $formatValue));
				
				$data['weights1MaxLength'] = $charLen;
				$data['expYears1'] = $expYears;
				$data['gracePeriod1'] = $gracePeriod;
				$data['minDays1'] = $minDays;
			}
		
			$result->free_result();
			$result->next_result();
			
			$data['cardBIN'] .= '<option formatvalue="'. $formatValue .'" weights="'. $weights .'" expyears="'. $expYears .'" graceperiod="'. $gracePeriod .'" mindays="'. $minDays .'" charlen="'. $charLen .'">'. $codevalue .'</option>';
			
		}
		//end
		
		//account types
		if (!$accountTypes = $this->cache->get($this->core->getSessionID() . 'accountTypes')) {
			$result = $this->card_model->getAccountType();
			
			$accountTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'accountTypes', $accountTypes, CACHE_TTL);
		}
		
		$first = current($accountTypes);
		
		$data['accountTypes'] = NULL;
		$data['formatValue2'] = NULL;
		$data['weights2'] = NULL;
		$data['weights2MaxLength'] = NULL;
		$data['prodCode'] = NULL;
		$data['gracePeriod2'] = NULL;
		$data['minDays2'] = NULL;
		$charFormat = NULL;
		
		foreach ($accountTypes as $row) {			
			$acctType = $row['accttype'];
			$description = $row['description'];
			
			$xml->setXML($row['xml1']);
			$formatValue = $row['formatvalue'];
			$weights = $xml->getValue('WEIGHTS');
			$prodCode = $row['prodcode'];
			$gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
			$minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;
			
			if ($acctType === $first['accttype']) {
				$data['formatValue2'] = $formatValue;
				$data['weights2'] = $weights;
				
				$charLen = strlen(str_replace(array('-', 'C'), '', $formatValue));
				
				$data['weights2MaxLength'] = $charLen;
				$data['prodCode'] = $prodCode;
				$data['gracePeriod2'] = $gracePeriod;
				$data['minDays2'] = $minDays;
				$charFormat = $row['acctchar'];
			}
			
			$data['accountTypes'] .= '<option value="'. $acctType .'" formatvalue="'. $formatValue .'" weights="'. $weights .'" prodcode="'. $prodCode .'" graceperiod="'. $gracePeriod .'" mindays="'. $minDays .'" charlen="'. $charLen .'">'. $description .'</option>';
		}
		//end
		
		//character format
		$charFormatList = array(
			'A' => 'Alpha',
			'N' => 'Numeric',
			'X' => 'Alphanumeric'
		);
		
		$data['charFormat'] = NULL;
		foreach ($charFormatList as $val => $desc) {
			$selected = $charFormat === $val ? ' selected' : NULL;
			$data['charFormat'] .= '<option value="'. $val .'"'. $selected .'>'. $desc .'</option>';
		}
		//end
		
		//$this->card_model->db->trans_commit();
		
		//if INST has card product code
		$data['p1'] = NULL;
		$data['p2'] = NULL;
		if ($core->hasProductCode()) {
			$data['p1'] = '<td><strong>P</strong> - Product Code</td>';
			$data['p2'] = 'case (n === 112):
				case (n === 80):';
		}
		
		$this->load->view('maintenance/settings', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$input = $this->input;
		$core = $this->core;
		
		$formatCode = $input->post('formatCode', TRUE);
		$formatType = $input->post('formatType', TRUE);
		$formatValue = strtoupper($input->post('formatValue', TRUE));
		$formatDesc = $formatCode . ' Format';
		$weights = $input->post('weights', TRUE);
		$prodCode = $input->post('prodCode', TRUE);
		$expYears = $input->post('expYears', TRUE);
		$gracePeriod = $input->post('gracePeriod', TRUE);
		$minDays = $input->post('minDays', TRUE);
		$charFormat = $input->post('charFormat', TRUE);
		
		$xml = '<WEIGHTS>'. $weights .'</>'.
			'<EXPYEARS>'. $expYears .'</>'.
			'<GRACEPERIOD>'. $gracePeriod .'</>'.
			'<MINDAYS>'. $minDays .'</>';
			
		$brseqno = $core->getBranchID();
		$userAudit = $core->getUserID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$override = '';
		$sessionID = $core->getSessionID();
		
		$result = $this->card_model->setAccountFormat(
			$formatCode,
			$formatType,
			$formatValue,
			$formatDesc,
			$prodCode,
			$charFormat,
			$xml,
			$brseqno,
			$ipAddress,
			$workstation,
			$userAudit,
			$override,
			$sessionID
		);
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Format successfully updated'
		));
	}
}