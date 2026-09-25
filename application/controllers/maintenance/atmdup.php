<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATMDup extends CI_Controller {	

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MAINTENANCEATM_NO);
		
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
	
	function index($termCode)
	{
		$this->load->model('coresys/atm_model');
		$this->load->model('coreapp/area_model');
		$this->load->library('shortxml');
		
		$atm = $this->atm_model;
		$xml = $this->shortxml;
		$core = $this->core;
		
		$result = $atm->getTerminalInfo($termCode);
		
		if (!$termCode || ($result->num_rows() === 0)) {
			$this->load->helper('url');
			redirect('maintenance/atm');
			exit();
		}
		
		$row = $result->row_array();
	
		$progCode = $row['progcode'];
		$prodCode = $row['productcode'];
		$termLang = $row['termlang'];
		$location = $row['loccode'];
		$emulation = $row['emulation'];
		$dispLogic = $row['dispenselogic'];
		$instType = $row['installationtype'];
		$data['dtProd'] = $row['dtprod'] ? $core->formatDate('m/d/Y', $row['dtprod']) : 'Undefined';
		
		//denomination
		$progCodeDeno = $row['progcode_denomination'];
		$progLangDeno = $row['proglang_denomination'];
		
		$xml->setXML($row['xml']);

		//increment LUNO
		$len = strlen($row['luno']);
		$row['luno']++;
		
		$data['luno'] = str_pad($row['luno'], $len, '0', STR_PAD_LEFT);
		//end
		
		$data['termID']   = $row['termid'];
		$data['desc'] 	  = $row['description'];
		$currency = $xml->getValue('CURRENCY');
		$data['contactNo'] = $xml->getValue('CONTACT');
		
		$xml->setXML($row['xml2']);
		$data['screenLoadSize'] = $row['screenloadsize'];
		$data['otherLoadSize'] 	= $row['otherloadsize'];
		$data['stateLoadSize'] 	= $row['stateloadsize'];
		$data['maxNotes'] 		= $xml->getValue('MAXNOTES');
		$data['fitLoadSize'] 	= $row['fitloadsize'];
		$data['optLoadSize'] 	= $row['optionloadsize'];
		
		$data['loadNewKey'] = ($row['isloadkeynew'] === '1' ? 'checked' : NULL);
		$data['loadPower']  = ($row['isloadpower'] === '1' ? 'checked' : NULL);
		
		$data['threshold'] = $xml->getValue('THRES') ? $core->currency($xml->getValue('THRES')) : '0.00';
		$data['decimal'] = $row['amtdec'];
		$data['status'] = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';
		
		//product codes
		$result->free_result();
		$result->next_result();
		
		$result = $atm->getProductCodes();
		
		$data['prodName'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['productcode'] === $prodCode ? ' selected' : NULL);
			$data['prodName'] .= '<option value="'. $row['productcode'] .'"'. $selected .'>'. $row['productname'] .'</option>';
		}
		
		//terminal language
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getTerminalLanguage();
		
		$data['termLang'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['termlang'] === $termLang ? ' selected' : NULL);
			$data['termLang'] .= '<option value="'. $row['termlang'] .'"'. $selected .'>'. $row['description'] .'</option>';
		}
		
		//location
		$result->free_result();
		$result->next_result();
			
		if ($core->isHeadOffice()) {
			$result = $atm->getAllLocations();
		} else {
			$result = $atm->getLocations($core->getBranchCode());
		}
		
		$data['location'] = NULL;
		$areaName = NULL;
		$brchName = NULL;
		
		foreach ($result->result_array() as $row) {
			$result->free_result();
			$result->next_result();
		
			$result = $this->area_model->getAreaByBranch($row['brcode']);
			$r = $result->row_array();
			
			if ($result->num_rows() === 0) {
				$area = 'undefined';
				$branch = 'undefined';
			} else {
				$area = $r['areaname'] ? $r['areaname'] : 'Undefined';
				$branch = $r['brname'] ? $r['brname'] : 'Undefined';
			}
			
			if ($areaName === NULL) {
				$areaName = $area;
			}
		
			if ($brchName === NULL) {
				$brchName = $branch;
			}

			$data['location'] .= '<option value="'. $row['loccode'] .'" areaname="'. $area .'" brname="'. $branch .'">'. $row['location'] .'</option>';
		}
		
		//Area and Branch
		$data['areaName'] = $areaName;
		$data['branchName'] = $brchName;
		//end
		
		//emulation
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getCodeList('TERMEMUL');
		
		$data['emulation'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['codevalue'] === $emulation ? ' selected' : NULL);
			$data['emulation'] .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
			
		//program codes
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getProgramCodes();
		
		$row = $result->row_array();
		$data['progCodex'] = $row['progcode'];
		$data['progLangx'] = $row['proglang'];
		
		$data['progName'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['progcode'] === $progCode ? ' selected' : NULL);
			$data['progName'] .= '<option proglang="'. $row['proglang'] .'" value="'. $row['progcode'] .'"'. $selected .'>'. $row['tpfile'] .'</option>';
		}
		
		//installation type
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getCodeList('INSTTYPE');
		
		$data['instType'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['codeseqno'] === $instType ? ' selected' : NULL);
			$data['instType'] .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
		}
		
		//dispense logic
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getCodeList('DISPLOGIC');
		
		$data['dispLogic'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['codeseqno'] === $dispLogic ? ' selected' : NULL);
			$data['dispLogic'] .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
		}
		
		//denominations
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getDenomination();
		
		$data['progCodeDeno'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['progcode'] === $progCodeDeno ? ' selected' : NULL);
			$data['progCodeDeno'] .= '<option value="'. $row['progcode'] .'"'. $selected .'>'. $row['progcode'] .'</option>';
		}
		$data['progLangDeno'] = $progLangDeno;
		
		$result->free_result();
		$result->next_result();
		
		if ($progCodeDeno === '0') {
			$progCodeDeno = $data['progCodex'];
			$progLangDeno = $data['progLangx'];
		}
		
		$result = $atm->getTerminalDenomination($progCodeDeno, $progLangDeno);
		
		$data['denominations'] = NULL;
		foreach ($result->result_array() as $row) {
			$parmData = $row['parmdata'];
			
			$curr = substr($parmData, 0, 3);
			$deno = $core->currency(substr($parmData, 6, 12) / 100);
			$cass = substr($parmData, 4, 1);
			$data['denominations'] .= '<tr>
				<td>'. $curr .'</td>
				<td>'. $deno .'</td>
				<td>'. $cass .'</td>
			</tr>';
		}
				
		//default currency
		$result->free_result();
		$result->next_result();
			
		$result = $atm->getCurrency();
		
		$data['currency'] = NULL;
		foreach ($result->result_array() as $row) {
			$selected = ($row['currency'] === $currency ? ' selected' : NULL);
			$data['currency'] .= '<option value="'. $row['currency'] .'"'. $selected .'>'. $row['currency'] .'</option>';
		}
		
		//increment termCode
		$len = strlen($termCode);
		$termCode++;
		
		$termCode = str_pad($termCode, $len, '0', STR_PAD_LEFT);
		//end
		
		//contact persons
		$data['hName'] = $xml->getValue('HNAME');
		$data['hNameAttr'] = $xml->getValue('HTEL') !== '' ? 'validate[required] ' : NULL;
		$data['hTel'] = $xml->getValue('HTEL');
		$data['hTelAttr'] = $xml->getValue('HNAME') !== '' ? 'validate[required] ' : NULL;
		$data['nName'] = $xml->getValue('NNAME');
		$data['nNameAttr'] = $xml->getValue('NTEL') !== '' ? 'validate[required] ' : NULL;
		$data['nTel'] = $xml->getValue('NTEL');
		$data['nTelAttr'] = $xml->getValue('NNAME') !== '' ? 'validate[required] ' : NULL;
		$data['tName'] = $xml->getValue('TNAME');
		$data['tNameAttr'] = $xml->getValue('TTEL') !== '' ? 'validate[required] ' : NULL;
		$data['tTel'] = $xml->getValue('TTEL');
		$data['tTelAttr'] = $xml->getValue('TNAME') !== '' ? 'validate[required] ' : NULL;
		$data['oName'] = $xml->getValue('ONAME');
		$data['oNameAttr'] = $xml->getValue('OTEL') !== '' ? 'validate[required] ' : NULL;
		$data['oTel'] = $xml->getValue('OTEL');
		$data['oTelAttr'] = $xml->getValue('ONAME') !== '' ? 'validate[required] ' : NULL;
		
		$data['header'] = 'New ATM Entry';
		$data['termCodeParams'] = 'class="validate[required] numbersOnly" maxlength="8" value="'. $termCode .'"';
		$data['termIDParams'] = 'class="validate[required]"';
		$data['submitBtnVal'] = 'maintenance/atmnew/submit';
		$data['waitMsg'] = 'Sending new ATM entry...';
		$data['submitBtnMsg'] = 'Submit new ATM entry?';
		
		$this->load->view('maintenance/atmx', $data);
	}
}
/* End of file atmdup.php */
/* Location: ./application/contollers/maintenance/atmdup.php */