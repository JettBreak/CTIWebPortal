<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POSNew extends CI_Controller {
	
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

	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/pos_model');
		$this->load->model('coreapp/area_model');
		$this->load->model('coreapp/branch_model');
		
		$cache = $this->cache;
		$pos = $this->pos_model;
		$core = $this->core;
		
		$data['luno'] = NULL;
		$data['status'] = 'Out of Service';
				
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
			$data['posLang'] .= '<option value="'. $row['termlang'] .'">'. $row['description'] .'</option>';
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
		
		if (count($resultArr)) {
			foreach ($resultArr as $row) {
				$result = $this->area_model->getAreaByBranch($row['brcode']);
				
				if ($result->num_rows() > 0) {
					
					$r = $result->row_array();
					
					if ($areaName === NULL) {
						$areaName = $r['areaname'];
					}
					
					if ($brchName === NULL) {
						$brchName = $r['brname'];
					}
					
					$data['location'] .= '<option value="'. $row['loccode'] .'" areaname="'. $r['areaname'] .'" brname="'. $r['brname'] .'">'. $row['location'] .'</option>';
				}
				$result->free_result();
				$result->next_result();
			}
		} else {
			$data['location'] = '<option value="">No Location Defined</option>';
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
					//if user branch is not allowed to monitor users from other branches
	/*				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
						$data['institutions'] = '<option instid="'.$row['instid'].'" value="'. $row['instseqno'] .'">'. $row['instname'] .'</option>';
						break;
					}*/
					$data['institutions'] .= '<option instid="'.$row['instid'].'" value="'. $row['instseqno'] .'">'. $row['instname'] .'</option>';
				}
			} else {
				$data['institutions'] = '<option value="">No Institution Defined</option>';
			}
			//end

			$result->free_result();
			$result->next_result();

			//outlets

			$result = $this->branch_model->getOutlets();
			$outlets = $result->result_array();

			if (count($outlets) > 0) {
				foreach ($outlets as $row) {
					//if user branch is not allowed to monitor users from other branches
	/*				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
						$data['institutions'] = '<option instid="'.$row['instid'].'" value="'. $row['instseqno'] .'">'. $row['instname'] .'</option>';
						break;
					}*/
					$data['outlets'] .= '<option inst="'.$row['instseqno'].'" outletid="'.$row['outletid'].'" value="'. $row['outletseqno'] .'">'. $row['outletname'] .'</option>';
				}
			} else {
				$data['outlets'] = '<option value="">No Outlet Defined</option>';
			}
			//end 	
		}
			
		$data['areaName'] = $areaName;
		$data['branchName'] = $brchName;
		
		$data['header'] = 'New POS Entry';
		$data['termCodeParams'] = 'class="validate[required] numbersOnly" maxlength="8"';
		$data['termID'] = NULL;
		$data['desc'] = NULL;
		$data['mercID'] = NULL;
		$data['termIDParams'] = 'class="validate[required]"';
		$data['submitBtnVal'] = 'maintenance/posnew/submit';
		$data['waitMsg'] = 'Sending new POS entry...';
		$data['submitBtnMsg'] = 'Submit new POS entry?';
		$data['POSCashOut'] = $this->core->hasPOSCashOut();

		$data['POSnbsp'] = $data['POSCashOut'] ? '<tr><td>&nbsp;</td></tr>' : NULL;
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/posx', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/user_model');
		
		$core  = $this->core;
		$input = $this->input;
		
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
			
			$xml1 = $mercID != '' ? '<MERCID>'. $mercID .'</>' : '' ;
			$xml1 .= $instID != 'xxx' ? '<INSTID>'. $instID .'</>' : '' ;
			$xml1 .= $outlet != 'xxx' ? '<OUTLET>'. $outlet .'</>' : '' ;
			
			$result = $this->pos_model->insertPOSTerminal(
				$termCode, $termID, $luno, $locCode, $desc, $progLang, $xml1
			);
			
			$row = $result->row_array();
			
			if (isset($row['errno']) && (int) $row['errno'] === 0) {
				$success = TRUE;
				$message = 'New POS entry saved';
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
/* End of file posnew.php */
/* Location: ./application/contollers/maintenance/posnew.php */
