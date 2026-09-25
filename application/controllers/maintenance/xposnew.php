<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class xPOSNew extends CI_Controller {
	
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
		$this->load->model('coreapp/branch_model');
		$this->load->model('coreapp/area_model');
		
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
		
		//branches

		$result = $this->branch_model->getBranchList();
		$branches = $result->result_array();
		
		$data['branches'] = NULL;
		$data['branches'] = '<option value="XXX">Select</option>';
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option code="'.$row['brcode'].'" value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				$data['branches'] .= '<option code="'.$row['brcode'].'" value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		//end

		$result->free_result();
		$result->next_result();

		//institutions

		$result = $this->branch_model->getInstitutions();
		$institutions = $result->result_array();
		
		$data['institutions'] = NULL;
		$data['institutions'] = '<option value="XXX">Select</option>';
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
		
		$data['outlets'] = NULL;
		$data['outlets'] = '<option value="XXX">Select</option>';
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

		$result->free_result();
		$result->next_result();

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
		//termstatus

		$result = $this->pos_model->getPOSDStatus();
		$termstat = $result->result_array();
		
		$data['status'] = NULL;
		$data['status'] = '<option value="XXX">Select</option>';
		if (count($termstat) > 0) {
			foreach ($termstat as $row) {
				//if user branch is not allowed to monitor users from other branches
/*				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['institutions'] = '<option instid="'.$row['instid'].'" value="'. $row['instseqno'] .'">'. $row['instname'] .'</option>';
					break;
				}*/
				$data['status'] .= '<option value="'. $row['codeseqno'] .'">'. strtoupper($row['codevalue']) .'</option>';
			}
		} else {
			$data['status'] = '<option value="">No Status Defined</option>';
		}
		//end 
			
		$data['areaName'] = NULL;
		$data['branchName'] = NULL;
		
		$data['header'] = 'New POS Entry';
		$data['termCodeParams'] = 'class="validate[required] numbersOnly" maxlength="8"';
		$data['termID'] = NULL;
		$data['desc'] = NULL;
		$data['mercID'] = NULL;
		$data['termIDParams'] = 'class="validate[required]"';
		$data['submitBtnVal'] = 'maintenance/xposnew/submit';
		$data['waitMsg'] = 'Sending new POS entry...';
		$data['submitBtnMsg'] = 'Submit new POS entry?';
		
		$this->load->view('maintenance/xposx', $data);
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
			$termCode = $input->post('poscode', TRUE);
			$termID   = $input->post('posID', TRUE);
			$luno	  = $input->post('luno', TRUE);
			$locCode  = $input->post('location', TRUE);
			$mercID   = $input->post('mercID', TRUE);
			$status   = $input->post('statusx', TRUE);
			//$desc	  = $input->post('description', TRUE);
			$progLang = $input->post('posLang', TRUE);
			$termName = $input->post('termName', TRUE);
			$isBranch = $input->post('isBranch', TRUE);//) ? $input->post('isBranch', TRUE) : 0;
			$brseqno  = $isBranch == NULL ? 0 : $input->post('branches', TRUE) ;
			$brcode   = $isBranch == NULL ? 0 : $input->post('brcode', TRUE) ;

			$isOthers = $input->post('isOthers', TRUE);//) ? $input->post('isOthers', TRUE) : 0;
			$instseqno  = $isOthers == NULL ? 0 : $input->post('instid', TRUE) ;
			$outlet   = $isOthers == NULL ? 0 : $input->post('outlet', TRUE) ;
			
			$this->load->model('coresys/pos_model');
			
			//$xml1 = '<MERCID>'. $mercID .'</>';
			
			/*$result = $this->pos_model->insertPOSTerminal(
				$termCode, $termID, $luno, $locCode, $desc, $progLang, $xml1
			);*/

			$row = $this->pos_model->insertPOSDTerminal(
					$termID,$termCode,$status,$termName,$brseqno,$instseqno,$outlet,$progLang,$locCode,$luno
			);
			
			//$row = $result->row_array();
			
			if ($row['errno'] === '0') {
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
			'params' => $termID.','.$termCode.','.$status.','.$termName.','.$brseqno.','.$instseqno.','.$outlet.','.$progLang.','.$locCode.','.$luno,
			'errorno' => $row['errno']
		));
	}
}
/* End of file posnew.php */
/* Location: ./application/contollers/maintenance/posnew.php */