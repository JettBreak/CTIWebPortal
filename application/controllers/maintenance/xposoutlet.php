<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class XPOSOutlet extends CI_Controller {
	
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
		
		//branches

		$result = $this->branch_model->getBranchList();
		$branches = $result->result_array();
		
		$data['branches'] = NULL;
		$data['branches'] = '<option value="XXX">Select</option>';
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
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

		
			
		$data['areaName'] = NULL;
		$data['branchName'] = NULL;
		
		$data['header'] = 'New Outlet Entry';
		$data['termCodeParams'] = 'class="validate[required] numbersOnly" maxlength="8"';
		$data['termID'] = NULL;
		$data['desc'] = NULL;
		$data['mercID'] = NULL;
		$data['termIDParams'] = 'class="validate[required]"';
		$data['submitBtnVal'] = 'maintenance/xposoutlet/submit';
		$data['waitMsg'] = 'Sending new Outlet entry...';
		$data['submitBtnMsg'] = 'Submit new Outlet entry?';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/xposoutlet', $data);
	}

	function getData()
	{
		$this->load->model('coresys/pos_model');
			
		/*if ($this->core->canMon()) {
			$branchCode = $this->input->get('brcode', TRUE);
		} else {
			$branchCode = $this->core->getBranchCode();
		}*/

		if ($this->core->isHeadOffice()) {
			$brseqno = 0;
		} else {
			$brseqno = $this->core->getBranchID();
		}

		$result = $this->pos_model->getInstitutionList($brseqno);
		
		$details = array();
		if ($result->num_rows() > 0) {
			$success = TRUE;
			foreach ($result->result_array() as $row)
			{
				$details[] = array(
					$row['instid'],
					$row['instname']//,
					//isset($row['statdesc']) ? $row['statdesc'] : $row['status'] .'-Unknown' //if NULL display Unknown
				);
			}
		} else {
			$success = FALSE;
		}
		
		echo json_encode(array(
			'success' => $success,
			'details' => $details,
			'xpos' => 'xpos'
		));
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
			$outletid = $input->post('outletid', TRUE);
			$outletname = $input->post('outletname', TRUE);
			$instid = $input->post('institutions', TRUE);
			
			
			$this->load->model('coresys/pos_model');
			
			//$xml1 = '<MERCID>'. $mercID .'</>';
			
			/*$result = $this->pos_model->insertPOSTerminal(
				$termCode, $termID, $luno, $locCode, $desc, $progLang, $xml1
			);*/

			$row = $this->pos_model->insertOutlet(
				$outletid,$outletname,$instid
			);
			
			//$row = $result->row_array();
			
			if (isset($row['errno']) && (int) $row['errno'] === 0) {
				$success = TRUE;
				$message = 'New Outlet entry saved';
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
			'params' => '',
			'errorno' => $row['errno']
		));
	}
}
/* End of file posnew.php */
/* Location: ./application/contollers/maintenance/posnew.php */
