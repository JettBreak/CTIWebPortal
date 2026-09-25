<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class KeyNew extends CI_Controller {
	
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
		$this->load->model('coresys/atm_model');
		
		//nodelist
		$result = $this->atm_model->getNodeList();
		
		$nodeList = '<option value="">No Nodes defined</option>';
		if ($result->num_rows() > 0) {
			$nodes = $result->result_array();
			
			$nodeList = '';
			foreach ($nodes as $row) {
				$nodeList .= '<option value="'. $row['nodename'] .'">'. $row['description'] .'</option>';
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
			$encModeList .= '<option value="'. $row['codevalue'] .'">'. $row['xml1'] .'</option>';
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
		
		$encType = NULL;
		foreach ($encTypes as $row) {
			$selected = $row['codevalue'] === 'DP' ? ' selected' : NULL; //set Diebold PIN Pad as default
			$encType .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['xml1'] .'</option>';
		}
		//end
		
		$data['inMasterKeyMaxLength'] = 16;
		$data['inWorkingKeyMaxLength'] = 16;
		$data['outMasterKeyMaxLength'] = 16;
		$data['outWorkingKeyMaxLength'] = 16;
		
		$data['inMasterKeyAttr'] = 'validate[required,minSize[16]] hexOnly upperCase';
		$data['inWorkingKeyAttr'] = 'validate[required,minSize[16]] hexOnly upperCase';
		$data['outMasterKeyAttr'] = 'validate[required,minSize[16]] hexOnly upperCase';
		$data['outWorkingKeyAttr'] = 'validate[required,minSize[16]] hexOnly upperCase';
		
		$data['inMasterKeyIndex'] = NULL;
		$data['inWorkingKeyIndex'] = NULL;
		$data['outMasterKeyIndex'] = NULL;
		$data['outWorkingKeyIndex'] = NULL;
		
		$data['oldSecCode'] = NULL;
		$data['secCode'] = NULL;
		$data['zoneAttr'] = NULL;
		$data['inVariant'] = 0;
		$data['inMKey'] = NULL;
		$data['inMKeyAttr'] = NULL;
		$data['inWKey'] = NULL;
		$data['inWKeyAttr'] = NULL;
		$data['outMKey'] = NULL;
		$data['outMKeyAttr'] = NULL;
		$data['outWKey'] = NULL;
		$data['outWKeyAttr'] = NULL;
		
		$data['title'] = 'New Security Key Entry';
		$data['encModeList'] = $encModeList;
		$data['encType1'] = $encType;
		$data['encType2'] = $encType;
		$data['submitBtnMsg'] = 'Submit new Security Key entry?';
		$data['waitMsg'] = 'Sending new Security Key entry...';
		$data['submitBtnVal'] = 'maintenance/keynew/submit';
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
		
		if ($row['errno'] !== '8') { //if session valid
			$this->load->model('coresys/atm_model');
			
			$nodeName = $input->post('nodeNamex', TRUE);
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
			
			$result = $this->atm_model->insertSecurityKey(
				$nodeName, 
				$secCode, 
				$encMode, 
				$encType1, 
				$encType2, 
				$pek1, 
				$pek2, 
				$variant1, 
				$variant2, 
				$kek1, 
				$kek2, 
				$userAudit, 
				$workstation, 
				$xml);

			
			
			$row = $result->row_array();
			
			if ($row['errno'] !== '-1') {
				$success = TRUE;
				$message = 'New Security Key saved';
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
			'errorno' => $row['errno']
		));
	}
}