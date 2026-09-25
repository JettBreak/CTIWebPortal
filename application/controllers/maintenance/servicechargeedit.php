<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ServiceChargeEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(SVCCHARGES_NO);
		
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
	
	function index()
	{
		$this->load->model('coreapp/branch_model');
		$this->load->model('coresys/misc_model');
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');
		
		$xml = $this->shortxml;
		
		$session = $_SESSION['serviceCharge'];
		
		$data['termCode'] = $session['termCode'];
		
		//branches
		$result = $this->branch_model->getBranchList();
		
		if ($session['brseqno'] === '9999') {
			$selected = ' selected';
		} else {
			$selected = NULL;
		}
		
		$data['branchList'] = '<option value="9999"'. $selected .'>GLOBAL</option>';
		foreach ($result->result_array() as $row) {
			$selected = $session['brseqno'] == $row['brseqno'] ? ' selected' : NULL;
			$data['branchList'] .= '<option value="'. $row['brseqno'] .'"'. $selected .'>'. $row['brname'] .'</option>';
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
		$data['checkIB'] = NULL;
		//end
		
		$data['serviceTypes'] = NULL;//'<option term="ATM, BNET" auth="SAVE">ATM / BNET</option>';
		foreach ($resultArr as $row) {
			$xml->setXML($row['xml1']);
			
			$term = str_replace('"', '', $xml->getValue('TERM'));
			$auth = str_replace('"', '', $xml->getValue('AUTH'));
			$ib = $xml->getValue('IB') !== '' ? $xml->getValue('IB') : 'X';
			$val = $row['codevalue'];
			$desc = $xml->getValue('DESC');
			
			if ($session['servType'] === $val) {
				$selected = ' selected';
				$data['checkIB'] = $ib;
			} else {
				$selected = NULL;
			}
			$data['serviceTypes'] .= '<option term="'. $term .'" auth="'. $auth .'" ib="'. $ib .'" value="'. $val .'"'. $selected .'>'. $desc .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//terminal types
		$result = $this->misc_model->getTerminalTypeList();
		
		$data['terminalTypes'] = '<option value="zzzz">zzzz - GLOBAL</option>';
		foreach ($result->result_array() as $row) {
			$termType = $row['termtype'];
			
			$selected = $session['termType'] === $termType ? ' selected' : NULL;
			$data['terminalTypes'] .= '<option value="'. $termType .'"'. $selected .'>'. $termType .' - '. $row['description'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//fee types
		$result = $this->misc_model->getFeeTypes();
		
		$data['feeTypes'] = NULL;
		foreach ($result->result_array() as $row) {
			$val = $row['codevalue'];
			
			$selected = $session['feeType'] === $val ? ' selected' : NULL;
			$data['feeTypes'] .= '<option value="'. $val .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
		
		switch ($session['feeType']) {
			case 'F':
				$label = 'Charge Amount:';
				$attribute = ' disabled';
				$feeAttr = 'class="currencyOnly" maxlength="12"';
				break;
			case 'R':
				$label = 'Charge Amount:';
				$attribute = NULL;
				$feeAttr = 'class="currencyOnly" maxlength="12"';
				break;
			case 'P':
				$label = 'Rate (%):';
				$attribute = ' disabled';
				$feeAttr = 'class="numbersOnly" maxlength="3"';
				break;
		}
		
		$data['rangeAttr'] = $attribute;
		$data['feeValueLabel'] = $label;
		$data['feeAttr'] = $feeAttr;
		
		$result->free_result();
		$result->next_result();
		//end
		
		//cardholders
		$result = $this->misc_model->getCardholders();
		
		$data['cardholders'] = NULL;
		foreach ($result->result_array() as $row) {
			$val = $row['codevalue'];
			
			$selected = $session['authName'] === $val ? ' selected' : NULL;
			$data['cardholders'] .= '<option value="'. $val .'"'. $selected .'>'. $row['xml1']  .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//network types
		$result = $this->misc_model->getNetworkTypes();
		
		$data['networkTypes'] = NULL;
		foreach ($result->result_array() as $row) {
			$val = $row['codevalue'];
			
			$selected = $session['netType'] === $val ? ' selected' : NULL;
			$data['networkTypes'] .= '<option value="'. $val .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//transaction list
		$data['transactionList'] = '<input type="text" style="width:328px" value="'. $session['tranDesc'] .'" readonly/>';
		//end
		
		//charge types		
		$data['chargeTypes'] = '<input type="text" style="width:208px" value="'. $session['chargeDesc'] .'" readonly/>';
		
		$param = $session['chargeType'] .'FEE';
		$result = $this->branch_model->getServiceCodeList($param);
			
		foreach ($result->result_array() as $row) {
			if ($session['trxcode2'] === $row['trxcode']) {
				$data['serviceCodeList'] = '<input type="text" style="width:300px" value="'. $row['description'] .'" readonly/>';
				//break;
			}
		}
		
		$result->free_result();
		$result->next_result();
		//end

		//card types

		
		$data['cardtype'] = '';//<option value="0">ALL</option>';

		//if ($this->core->isISOCustomer()) {

			$result = $this->card_model->getCardTypeFees();
			foreach ($result->result_array() as $row) {
				$selected = $session['accttype'] === $row['accttype'] ? ' selected' : NULL;
				$data['cardtype'] .= '<option value="'. $row['accttype'] .'"'.$selected.'>'. $row['description'] .'</option>';
			}

			$result->free_result();
			$result->next_result();
		//}
		
		//end
		
		$data['title'] = 'Edit Service Charge No. '. $session['feeseqno'];
		$data['formAction'] = 'maintenance/servicechargeedit/submit';
		$data['submitLabel'] = 'Update';
		
		$data['minRange'] = $this->core->currency(str_replace(',', '', $session['minRange']));
		$data['maxRange'] = $this->core->currency(str_replace(',', '', $session['maxRange']));
		$data['feeValue'] = $this->core->currency(str_replace(',', '', $session['serviceFee']));
		$data['remarks'] = $session['remarks'];
		$data['hiddenInput'] = '<input type="hidden" name="feeseqno" value="'. $session['feeseqno'] .'"/>';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/servicechargex', $data);
	}
	
	function submit()
	{
		$this->load->model('coresys/misc_model');
		
		$input = $this->input;
		$core = $this->core;
		
		$feeseqno = $input->post('feeseqno', TRUE);
		$termType = $input->post('terminalType', TRUE);
		$termCode = $input->post('termCode', TRUE);
		if ($termCode === 'GLOBAL') {
			$termCode = 'zzzz';
		}
		$branchx = $input->post('branchx', TRUE);
		$serviceType = $input->post('serviceType', TRUE);
		$checkIB = $input->post('checkIB', TRUE);
		$authName = $input->post('cardholder', TRUE);
		//$trxcodex = $input->post('transaction', TRUE);
		//$trxcode2 = $input->post('serviceCode', TRUE);
		$accttype = $input->post('cardtype', TRUE);
		$feeType = $input->post('feeType', TRUE);
		$feeValue = str_replace(',', '', $input->post('feeValue', TRUE));
		$minRange = str_replace(',', '', $input->post('minRange', TRUE));
		$minRange = $minRange == '' ? '0.00' : $minRange;
		$maxRange = str_replace(',', '', $input->post('maxRange', TRUE));
		$maxRange = $maxRange == '' ? '0.00' : $maxRange;
		//$chargeType = $input->post('chargeType', TRUE);
		$remarks = $input->post('remarks', TRUE);
		$isDisabled = '';
		$networkType = $input->post('networkType', TRUE);
		
		$brseqno = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userID = $core->getUserID();
		$override = '';
		
		$result = $this->misc_model->updateFee(
			$accttype,
			$feeseqno,
			$termType,
			$termCode,
			$branchx,
			$serviceType,
			$checkIB,
			$authName,
			//$trxcodex,
			//$trxcode2,
			$feeType,
			$feeValue,
			$minRange,
			$maxRange,
			//$chargeType,
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
		$message = 'Service Charge updated successfully';
		
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