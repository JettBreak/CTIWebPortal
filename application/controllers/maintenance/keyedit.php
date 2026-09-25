<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class KeyEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ATMKEYMGMT_NO);
		
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
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->library('shortxml');
		
		//$this->cache->clean();
		if (!$atmKey = $this->cache->get($this->core->getSessionID() . 'atmKey')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$xml = $this->shortxml;
		$xml->setXML($atmKey['xml']);
		
		$this->load->model('coresys/atm_model');
		
		//nodelist
		$result = $this->atm_model->getNodeList();
		
		$nodeList = '<option value="">No Nodes defined</option>';
		if ($result->num_rows() > 0) {
			$nodes = $result->result_array();
			
			$nodeList = '';
			foreach ($nodes as $row) {
				$selected = $row['nodename'] === $atmKey['nodeName'] ? ' selected' : NULL;
				$nodeList .= '<option value="'. $row['nodename'] .'"'. $selected .'>'. $row['description'] .'</option>';
			}
		}
		
		$data['nodeList'] = $nodeList;
		
		$result->free_result();
		$result->next_result();
		//end
		
		//Encryption Mode
		if (!$encModes = $this->cache->get($this->core->getSessionID() . 'encModes')) {
			//$result->free_result();
			//$result->next_result();
			
			$result = $this->atm_model->getEncryptionMode();
			$encModes = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'encModes', $encModes, CACHE_TTL);
		}
		
		$encModeList = NULL;
		foreach ($encModes as $row) {
			$selected = $row['codevalue'] === $atmKey['encMode'] ? ' selected' : NULL;
			$encModeList .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
		//end
		
		//Encryption Type
		if (!$encTypes = $this->cache->get($this->core->getSessionID() . 'encTypes')) {
			if ($result !== NULL) {
				$result->free_result();
				$result->next_result();
			}
			
			$result = $this->atm_model->getEncryptionType();
			$encTypes = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'encTypes', $encTypes, CACHE_TTL);
		}
		
		$encType1 = NULL;
		foreach ($encTypes as $row) {
			$selected = $row['codevalue'] === $atmKey['inEncType'] ? ' selected' : NULL;
			$encType1 .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
		
		$encType2 = NULL;
		foreach ($encTypes as $row) {
			$selected = $row['codevalue'] === $atmKey['outEncType'] ? ' selected' : NULL;
			$encType2 .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
		//end
		
		$data['oldSecCode'] = $atmKey['secCode'];
		$data['secCode'] = $atmKey['secCode'];
		$data['zoneAttr'] = $xml->getValue('ZONE') === 'Y' ? ' checked' : NULL;
		$data['inVariant'] = $atmKey['inVariant'];
		$data['inMKey'] = $atmKey['inMKey'];
		$data['inMKeyAttr'] = $xml->getValue('INMKEY') === 'Y' ? ' checked' : NULL;
		$data['inWKey'] = $atmKey['inWKey'];
		$data['inWKeyAttr'] = $xml->getValue('INWKEY') === 'Y' ? ' checked' : NULL;
		$data['outMKey'] = $atmKey['outMKey'];
		$data['outMKeyAttr'] = $xml->getValue('OUTMKEY') === 'Y' ? ' checked' : NULL;;
		$data['outWKey'] = $atmKey['outWKey'];
		$data['outWKeyAttr'] =  $xml->getValue('OUTWKEY') === 'Y' ? ' checked' : NULL;
		
		//set input box attrs
		
		if ($atmKey['encMode'] === 'DES') {
			$maxLength = 16;
		} else { //TDES
			$maxLength = 32;
		}
		
		$data['inMasterKeyMaxLength'] = $maxLength;
		$data['inWorkingKeyMaxLength'] = $maxLength;
		$data['outMasterKeyMaxLength'] = $maxLength;
		$data['outWorkingKeyMaxLength'] = $maxLength;
		
		$data['inMasterKeyAttr'] = 'validate[required,minSize['. $maxLength .']] hexOnly upperCase';
		$data['inWorkingKeyAttr'] = 'validate[required,minSize['. $maxLength .']] hexOnly upperCase';
		$data['outMasterKeyAttr'] = 'validate[required,minSize['. $maxLength .']] hexOnly upperCase';
		$data['outWorkingKeyAttr'] = 'validate[required,minSize['. $maxLength .']] hexOnly upperCase';
		
		$data['inMasterKeyIndex'] = NULL;
		$data['inWorkingKeyIndex'] = NULL;
		$data['outMasterKeyIndex'] = NULL;
		$data['outWorkingKeyIndex'] = NULL;
		
		//if HSM Stored Keys
		if ($xml->getValue('INMKEY') === 'Y') {
			$data['inMasterKeyAttr'] = 'validate[required,custom[onlyNumberSp]] numbersOnly';
			$data['inMasterKeyMaxLength'] = 5;
			$data['inMasterKeyIndex'] = '(index) ';
		}
		
		if ($xml->getValue('INWKEY') === 'Y') {
			$data['inWorkingKeyAttr'] = 'validate[required,custom[onlyNumberSp]] numbersOnly';
			$data['inWorkingKeyMaxLength'] = 5;
			$data['inWorkingKeyIndex'] = '(index) ';
		}
		
		if ($xml->getValue('OUTMKEY') === 'Y') {
			$data['outMasterKeyAttr'] = 'validate[required,custom[onlyNumberSp]] numbersOnly';
			$data['outMasterKeyMaxLength'] = 5;
			$data['outMasterKeyIndex'] = '(index) ';
		}
		
		if ($xml->getValue('OUTWKEY') === 'Y') {
			$data['outWorkingKeyAttr'] = 'validate[required,custom[onlyNumberSp]] numbersOnly';
			$data['outWorkingKeyMaxLength'] = 5;
			$data['outWorkingKeyIndex'] = '(index) ';
		}
		//end
		
		//end
		
		$data['title'] = 'Update Security Key Entry';
		$data['encModeList'] = $encModeList;
		$data['encType1'] = $encType1;
		$data['encType2'] = $encType2;
		$data['submitBtnMsg'] = 'Update Security Key entry?';
		$data['waitMsg'] = 'Updating Security Key entry...';
		$data['submitBtnVal'] = 'maintenance/keyedit/submit';
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/keyx', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/user_model');
		
		$input = $this->input;
		$core = $this->core;
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$row = $this
			->user_model
			->checkLogin($userAudit, $sessionID)
			->row_array();
		
		$errNo = $row['errno'];
		if ($errNo !== '8') { //if session valid
			$this->load->model('coresys/atm_model');
			
			$nodeName = $input->post('nodeNamex', TRUE);
			$oldSecCode = $input->post('oldSecCode', TRUE);
			$secCode = $input->post('secCode', TRUE);
			$encMode = $input->post('encMode', TRUE);
			$encType1 = $input->post('inEncType', TRUE);
			$encType2 = $input->post('outEncType', TRUE);
			$pek1 = strtoupper($input->post('inMasterKey', TRUE));
			$pek2 = strtoupper($input->post('inWorkingKey', TRUE));
			$variant1 = $input->post('inVariant', TRUE);
			$variant2 = $input->post('outVariant', TRUE);
			$kek1 = strtoupper($input->post('outMasterKey', TRUE));
			$kek2 = strtoupper($input->post('outWorkingKey', TRUE));
			$workstation = $core->getWorkstation();
			
			$inMKey = $input->post('inMKey', TRUE) ? 'Y' : 'N';
			$inWKey = $input->post('inWKey', TRUE) ? 'Y' : 'N';
			$outMKey = $input->post('outMKey', TRUE) ? 'Y' : 'N';
			$outWKey = $input->post('outWKey', TRUE) ? 'Y' : 'N';
			$zone = $input->post('zone', TRUE) ? 'Y' : 'N';
			
			$xml = '<INMKEY>'. $inMKey .'</>'.
				'<INWKEY>'. $inWKey .'</>'.
				'<OUTMKEY>'. $outMKey .'</>'.
				'<OUTWKEY>'. $outWKey .'</>'.
				'<ZONE>'. $zone .'</>';
			
			$result = $this->atm_model->updateSecurityKey($nodeName, $oldSecCode, $secCode, $encMode, $encType1, $encType2, $pek1, $pek2, $variant1, $variant2, $kek1, $kek2, $userAudit, $workstation, $xml);

			
			
			$row = $result->row_array();
			
			if ($row['errno'] !== '-1') {
				$success = TRUE;
				$message = 'Security Key updated';
			} else {
				$success = FALSE;
				$message = $row['errmsg'];
			}
		} else { //if invalid userID / session then logout
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => $errNo
		));
	}
}