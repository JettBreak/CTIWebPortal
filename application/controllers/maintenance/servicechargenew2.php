<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ServiceChargeNew extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(SVCCHARGES_NO);
	}
	
	function index($termCode)
	{
		$this->load->model('coreapp/branch_model');
		$this->load->model('coresys/misc_model');
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');
		
		$xml  = $this->shortxml;
		$core = $this->core;
		
		if ($termCode === 'zzzz') {
			$data['termCode'] = 'GLOBAL';
		} else {
			$data['termCode'] = $termCode;
		}
		
		//branches
		$result = $this->branch_model->getBranchList();
		
		$data['branchList'] = '<option value="9999">GLOBAL</option>';
		foreach ($result->result_array() as $row) {
			$data['branchList'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//service types
		$result = $this->misc_model->getServiceTypes();
		
		$resultArr = $result->result_array();
		
		//check IB
		$row = current($resultArr);
		$xml->setXML($row['xml1']);
		$data['checkIB'] = $xml->getValue('IB') !== '' ? $xml->getValue('IB') : 'X';
		//end
		
		$data['serviceTypes'] = NULL;//'<option term="ATM, BNET" auth="SAVE">ATM / BNET</option>';
		foreach ($resultArr as $row) {
			$xml->setXML($row['xml1']);
			
			$term = str_replace('"', '', $xml->getValue('TERM'));
			$auth = str_replace('"', '', $xml->getValue('AUTH'));
			$ib = $xml->getValue('IB') !== '' ? $xml->getValue('IB') : 'X';
			$val = $row['codevalue'];
			$desc = $xml->getValue('DESC');
			
			$data['serviceTypes'] .= '<option term="'. $term .'" auth="'. $auth .'" ib="'. $ib .'" value="'. $val .'">'. $desc .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//terminal types
		$result = $this->misc_model->getTerminalTypeList();
		
		$data['terminalTypes'] = '<option value="zzzz">zzzz - GLOBAL</option>';
		foreach ($result->result_array() as $row) {
			$data['terminalTypes'] .= '<option value="'. $row['termtype'] .'">'. $row['termtype'] .' - '. $row['description'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//fee types
		$result = $this->misc_model->getFeeTypes();
		
		$data['feeTypes'] = NULL;
		foreach ($result->result_array() as $row) {
			$data['feeTypes'] .= '<option value="'. $row['codevalue'] .'">'. $row['xml1'] .'</option>';
		}
		$data['rangeAttr'] = ' disabled';
		$data['feeValueLabel'] = 'Charge Amount:';
		
		$result->free_result();
		$result->next_result();
		//end
		
		//cardholders
		$result = $this->misc_model->getCardholders();
		
		$data['cardholders'] = NULL;
		foreach ($result->result_array() as $row) {
			$data['cardholders'] .= '<option value="'. $row['codevalue'] .'">'. $row['xml1'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//network types
		$result = $this->misc_model->getNetworkTypes();
		
		$data['networkTypes'] = NULL;
		foreach ($result->result_array() as $row) {
			$data['networkTypes'] .= '<option value="'. $row['codevalue'] .'">'. $row['xml1'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//transaction list
		$result = $this->misc_model->getTransactionList();
		
		$data['transactionList'] = '<select name="transaction" id="transaction" style="width:340px">';
		foreach ($result->result_array() as $row) {
			$data['transactionList'] .= '<option value="'. $row['trxcode'] .'">'. $row['description'] .'</option>';
		}
		$data['transactionList'] .= '</select>';
		
		$result->free_result();
		$result->next_result();
		//end
		
		//card types

		
		$data['cardtype'] = '<option value="0">ALL</option>';

		if ($core->isISOCustomer()) {

			$result = $this->card_model->getCardTypeFees();
			foreach ($result->result_array() as $row) {
				$data['cardtype'] .= '<option value="'. $row['accttype'] .'">'. $row['description'] .'</option>';
			}

			$result->free_result();
			$result->next_result();
		}
		
		//end
		
		//charge types
		$chargeTypes = array(
			'S' => 'Debit to Source Account',
			'D' => 'Debit to Destination Account'
		);
		
		$data['chargeTypes'] = '<select name="chargeType" id="chargeType" style="width:220px">';
		$data['serviceCodeList'] = '<select name="serviceCode" id="serviceCode" style="width:350px">';
		
		foreach ($chargeTypes as $val => $desc) {
			$data['chargeTypes'] .= '<option value="'. $val .'">'. $desc .'</option>';
			
			$param = $val .'FEE';
			//service code
			$result = $this->branch_model->getServiceCodeList($param);
			
			foreach ($result->result_array() as $row) {
				if ($val === 'D') {
					$attr = ' class="hidden"';
				} else {
					$attr = '';
				}
				$data['serviceCodeList'] .= '<option chargetype="'. $val .'" value="'. $row['trxcode'] .'"'. $attr .'>' . $row['mnemonic'] . ' - ' . $row['description'] .'</option>';
			}
			
			$result->free_result();
			$result->next_result();
			//end
		}
		
		$data['chargeTypes'] .= '</select>';
		$data['serviceCodeList'] .= '</select>';
		//end
		
		$data['title'] = 'New Service Charge';
		$data['formAction'] = 'maintenance/servicechargenew/submit';
		$data['submitLabel'] = 'Save';
		
		$data['minRange'] = '0.00';
		$data['maxRange'] = '0.00';
		$data['feeValue'] = '0.00';
		$data['feeAttr'] = 'class="currencyOnly" maxlength="12"';
		$data['remarks'] = NULL;
		$data['hiddenInput'] = NULL;
		
		$this->load->view('maintenance/servicechargex', $data);
	}
	
	function submit()
	{
		$this->load->model('coresys/misc_model');
		
		$input = $this->input;
		$core = $this->core;
		
		$termType = $input->post('terminalType', TRUE);
		$termCode = $input->post('termCode', TRUE);
		if ($termCode === 'GLOBAL') {
			$termCode = 'zzzz';
		}
		$accttype = $input->post('cardtype', TRUE);
		$branchx = $input->post('branchx', TRUE);
		$serviceType = $input->post('serviceType', TRUE);
		$checkIB = $input->post('checkIB', TRUE);
		$authName = $input->post('cardholder', TRUE);
		$trxcodex = $input->post('transaction', TRUE);
		$trxcode2 = $input->post('serviceCode', TRUE);
		$feeType = $input->post('feeType', TRUE);
		$feeValue = str_replace(',', '', $input->post('feeValue', TRUE));
		$minRange = str_replace(',', '', $input->post('minRange', TRUE));
		$maxRange = str_replace(',', '', $input->post('maxRange', TRUE));
		$chargeType = $input->post('chargeType', TRUE);
		$remarks = $input->post('remarks', TRUE);
		$isDisabled = '';
		$networkType = $input->post('networkType', TRUE);
		
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userID = $core->getUserID();
		$override = '';
		
		$result = $this->misc_model->insertFee(
			$accttype,
			$termType,
			$termCode,
			$branchx,
			$serviceType,
			$checkIB,
			$authName,
			$trxcodex,
			$trxcode2,
			$feeType,
			$feeValue,
			$minRange,
			$maxRange,
			$chargeType,
			$remarks,
			$isDisabled,
			$networkType,
			$brseqno,
			$ipAddress,
			$workstation,
			$userID,
			$override
		);
		
		$row = $result->row_array();

		$success = TRUE;
		$message = 'New Service Charge added successfully';
		
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		$result->free_result();
		$result->next_result();
			
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}