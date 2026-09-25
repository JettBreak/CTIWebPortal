<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POSEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MAINTENANCEPOS_NO);
		
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
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/pos_model');
		$this->load->model('coreapp/area_model');
		$this->load->model('coreapp/branch_model');
		$this->load->library('shortxml');
		
		$cache = $this->cache;
		$pos = $this->pos_model;
		$core = $this->core;
		$xml = $this->shortxml;
		
		$result = $pos->getPOSInfo($termCode);
		
		if (!$termCode || ($result->num_rows() === 0)) {
			$this->load->helper('url');
			redirect('maintenance/pos');
			exit();
		}
		
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		$xml->setXML($row['xml']);
		
		$MerchID = $row['xml'] ? $xml->getValue('MERCID') : '';
		$InstID = $row['xml'] ? $xml->getValue('INSTID') : '';
		$Outlet = $row['xml'] ? $xml->getValue('OUTLET') : '';
		
		$data['termID'] = $row['termid'];
		$data['luno'] = $row['luno'];
		$data['mercID'] = $MerchID;
		$data['desc'] = $row['description'];
		$data['status'] = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';
		$progLang = $row['proglang'];
		$location = $row['loccode'];
		
		//get POS language from cache
		if (!$posLang = $cache->get($this->core->getSessionID() . 'posLang')) {			
			$result = $pos->getPOSLanguage();
			$posLang = $result->result_array();
			
			$result->free_result();
			$result->next_result();
		
			$cache->save($this->core->getSessionID() . 'posLang', $posLang, CACHE_TTL);
		}
		
		$data['posLang'] = NULL;
		foreach ($posLang as $row) {
			$selected = ($row['termlang'] === $progLang ? ' selected' : NULL);
			$data['posLang'] .= '<option value="'. $row['termlang'] .'"'. $selected .'>'. $row['description'] .'</option>';
		}
		//end
		
		//location
		if ($core->isHeadOffice()) {
			$result = $pos->getAllLocations();
		} else {
			$result = $pos->getLocations($core->getBranchCode());
		}
		$resultArr = $result->result_array();
		
		$result->free_result();
		$result->next_result();
		
		$data['location'] = NULL;
		
		$areaName = NULL;
		$brchName = NULL;

		foreach ($resultArr as $row) {
			$result = $this->area_model->getAreaByBranch($row['brcode']);
			$r = $result->row_array();
			
			if ($location === $row['loccode']) {
				$areaName = $r['areaname'];
				$brchName = $r['brname'];
				$selected = ' selected';
			} else {
				$selected = NULL;
			}
			
			if ($result->num_rows() === 0) {
				$area = 'undefined';
				$branch = 'undefined';	
			} else {
				$area = $r['areaname'] ? $r['areaname'] : 'Undefined';
				$branch = $r['brname'] ? $r['brname'] : 'Undefined';
			}
			
			$data['location'] .= '<option value="'. $row['loccode'] .'" areaname="'. $area .'" brname="'. $branch .'"'. $selected .'>'. $row['location'] .'</option>';
			
			$result->free_result();
			$result->next_result();
		}
		//end

		$result->free_result();
		$result->next_result();

		//institutions
		
		$data['institutions'] = NULL;
		$data['institutions'] = '<option value="XXX">Select</option>';
		$data['outlets'] = NULL;
		$data['outlets'] = '<option value="XXX">Select</option>';
		
		if ($this->core->hasPOSCashOut()) {

			$result = $this->branch_model->getInstitutions();
			$institutions = $result->result_array();

			if (count($institutions) > 0) {
				foreach ($institutions as $row) {

					if ($row['instseqno'] == $InstID) {
						$selected = ' selected';
						$data['instID'] = $row['instid'];
					} else {
						$selected = NULL;
					}

					$data['institutions'] .= '<option instid="'.$row['instid'].'" value="'. $row['instseqno'] .'"'.$selected.'>'. $row['instname'] .'</option>';
				}
			} else {
				$data['institutions'] = '<option value="">No Institution Defined</option>';
			}
			//end

			$result->free_result();
			$result->next_result();

			//institutions

			$result = $this->branch_model->getOutlets();
			$outlets = $result->result_array();
			
			if (count($outlets) > 0) {
				foreach ($outlets as $row) {

					if ($row['outletseqno'] == $Outlet) {
						$selected = ' selected';
						$data['outletID'] = $row['outletid'];
					} else {
						$selected = NULL;
					}

					$data['outlets'] .= '<option inst="'.$row['instseqno'].'" outletid="'.$row['outletid'].'" value="'. $row['outletseqno'] .'"'.$selected.'>'. $row['outletname'] .'</option>';
				}
			} else {
				$data['outlets'] = '<option value="">No Outlet Defined</option>';
			}
		}
		//end 
		
		$data['areaName'] = $areaName;
		$data['branchName'] = $brchName;
	
		$data['header'] = 'Update POS Information';
		$data['termCodeParams'] = 'value="'. $termCode .'" class="validate[required] numbersOnly" maxlength="8" readonly';
		$data['termIDParams'] = 'readonly';
		$data['submitBtnVal'] = 'maintenance/posedit/submit';
		$data['waitMsg'] = 'Updating POS entry...';
		$data['submitBtnMsg'] = 'The modification will alter the terminal behavior. Do you want to continue?';
		$data['POSCashOut'] = $this->core->hasPOSCashOut();

		$data['POSnbsp'] = $data['POSCashOut'] ? '<tr><td>&nbsp;</td></tr>' : NULL;

		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/posx', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/user_model');
		$this->load->library('shortxml');
		
		$core  = $this->core;
		$input = $this->input;
		$xml = $this->shortxml;
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$row = $this
			->user_model
			->checkLogin($userAudit, $sessionID)
			->row_array();
			
		if ($row['errno'] !== '8') { //if session valid
			$termCode = $input->post('posCode', TRUE);
			$termID   = $input->post('posID', TRUE);
			$luno	  = $input->post('luno', TRUE);
			$locCode  = $input->post('location', TRUE);
			$mercID   = $input->post('mercID', TRUE);
			$desc	  = $input->post('description', TRUE);
			$progLang = $input->post('progLang', TRUE);
			$instID	  = $input->post('institutions', TRUE);
			$outlet   = $input->post('outlet', TRUE);
			
			$this->load->model('coresys/pos_model');
			
			$result = $this->pos_model->getPOSInfo($termCode);
			
			if (!$termCode || ($result->num_rows() === 0)) {
				$this->load->helper('url');
				redirect('maintenance/pos');
				exit();
			}
			
			$row = $result->row_array();
			
			$result->free_result();
			$result->next_result();
			
			$xml->setXML($row['xml']);

			$xml->isTagExist('MERCID') ? $xml->editTag('MERCID', $mercID) : $xml->addTag('MERCID', $mercID);
			$xml->isTagExist('INSTID') ? $xml->editTag('INSTID', $instID) : $xml->addTag('INSTID', $instID);
			$xml->isTagExist('OUTLET') ? $xml->editTag('OUTLET', $outlet) : $xml->addTag('OUTLET', $outlet);
			
			$xml1 = $xml->getXML();
			
			$result = $this->pos_model->updatePOSTerminal(
				$termCode, $termID, $luno, $locCode, $desc, $progLang, $xml1
			);
			
			$row = $result->row_array();
			
			if ($row['errno'] === '0') {
				$success = TRUE;
				$message = 'POS entry updated successfully';
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
/* End of file posedit.php */
/* Location: ./application/contollers/maintenance/posedit.php */