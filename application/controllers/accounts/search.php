<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ACCNTMGMT_NO);

		$this->load->model('coreapp/user_model');

		$result = $this->user_model->checkLogin($this->core->getUserID(), $this->core->getSessionID());

		$row = $result->row_array();

		if (intval($row['errno']) > 0) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Invalid Login Session. Please relogin.'
			));
			exit();
		}
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		
		$branchCode = $this->core->getBranchCode();
		
		if (!$accountTypes = $this->cache->get($this->core->getSessionID() . 'accountTypes')) {
			$result = $this->card_model->getAccountType();
			
			$accountTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'accountTypes', $accountTypes, CACHE_TTL);
		}
		
		$first = current($accountTypes);
		
		$formatValue = $first['formatvalue'];
		$brChar = 'B';
		$newFormat = $this->getFormat($formatValue, $brChar);
		
		$data['mask'] = $newFormat;
		$placeholder = str_replace(array('P', 'S', 'C', 'X'), '_', $newFormat);
		$data['acctNoPlaceholder'] = $placeholder;
		
		$acctChar = $first['acctchar'];
		
		switch ($acctChar) {
			case 'A':
				$XFormat = '[A-Za-z]';
				break;
			case 'N':
				$XFormat = '[0-9]';
				break;
			case 'X':
				$XFormat = '[A-Za-z0-9]';
				break;
			default:
				$XFormat = '[A-Za-z0-9]';
				break;
		}
		
		$data['XFormat'] = $XFormat;
		
		$data['accountTypes'] = NULL;//'<option mask="SSSSBBBBBB" xchar="[A-Za-z0-9]">TEST</option>';
		foreach ($accountTypes as $row) {
			switch ($row['acctchar']) {
				case 'A':
					$xChar = '[A-Za-z]';
					break;
				case 'N':
					$xChar = '[0-9]';
					break;
				case 'X':
					$xChar = '[A-Za-z0-9]';
					break;
				default:
					$xChar = '[A-Za-z0-9]';
					break;
			}

			$brChar = 'B';
			$newFormat = $this->getFormat($row['formatvalue'], $brChar);
			$placeholder = str_replace(array('P', 'S', 'C', 'X'), '_', $newFormat);
		
			$data['accountTypes'] .= '<option inputph="'. $placeholder .'" mask="'. $row['formatvalue'] .'" xchar="'. $xChar .'" value="'. $row['accttype'] .'">'. $row['description'] .'</option>';
		}
		
		$data['sessionExp'] = $this->core->getSessionExp();
		//$this->output->cache(CACHE_TTL);
		$this->load->view('accounts/search', $data);
	}

	function getFormat($formatValue, $brChar)
	{
		$branchCode = $this->core->getBranchCode();
		
		$padCnt = substr_count($formatValue, $brChar);
		$get = str_repeat($brChar, $padCnt); //BBBBBB
		$brVal = str_pad($branchCode, $padCnt, '0', STR_PAD_LEFT);
		$newFormat = str_replace($get, $brVal, $formatValue);
		
		return $newFormat;
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$accntNo = $this->input->post('accntNo', TRUE);
		$accntType = $this->input->post('accntType', TRUE);
		
		$result = $this->card_model->getAccountInfoByKey($accntNo, $accntType);
		
		//log
		$userID = $this->core->getUserID();
		$workstation = $this->core->getWorkstation();
		$ipAddress = $this->core->getIPAddress();
		$brseqno = $this->core->getBranchID();
		
		$logXML = '<AC>'. $accntNo .'</>'.
			'<IP>'. $workstation .'</>'.
			'<IPADDR>'. $ipAddress .'</>'.
			'<BRSEQN0>'. $brseqno .'</>';
		
		if ($result->num_rows() > 0) {
			$_SESSION['accountInfo'] = $result->row_array();
			
			$msgType = 41;
			$sysVCode = 0;
			
			$success = TRUE;
			$message = NULL;
		} else {
			//voided
			$msgType = 43;
			$sysVCode = 7101;
			
			$logXML .= '<SYSVMINI>ACCOUNT INVALID</>'.
				'<SYSVDESC>Account Not Found</>';
				
			$success = FALSE;
			$message = 'Account Not Found';
		}
		
		$result->free_result();
		$result->next_result();
		
		$this->card_model->insertLogclixx(
			$msgType, $brseqno, $sysVCode, $accntNo,
			$accntNo, $userID, $workstation, $logXML
		);
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}