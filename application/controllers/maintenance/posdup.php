<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POSDup extends CI_Controller {
	
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
		
		$cache = $this->cache;
		$pos = $this->pos_model;
		$core = $this->core;
		
		$result = $pos->getPOSInfo($termCode);
		
		if (!$termCode || ($result->num_rows() === 0)) {
			$this->load->helper('url');
			redirect('maintenance/pos');
			exit();
		}
		
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		$data['termID'] = $row['termid'];
		
		//increment LUNO
		$len = strlen($row['luno']);
		$row['luno']++;
		
		$data['luno'] = str_pad($row['luno'], $len, '0', STR_PAD_LEFT);
		//end
		
		$data['desc'] = $row['description'];
		$data['status'] = strtoupper($row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown');
		$progLang = $row['proglang'];
		$location = $row['loccode'];
		
		//get POS language from cache
		if (!$posLang = $cache->get($this->core->getSessionID() . 'posLang')) {
			$result->free_result();
			$result->next_result();
			
			$result = $pos->getPOSLanguage();
			$posLang = $result->result_array();
			
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
			
			$data['location'] .= '<option value="'. $row['loccode'] .'" areaname="'. $r['areaname'] .'" brname="'. $r['brname'] .'"'. $selected .'>'. $row['location'] .'</option>';
			
			$result->free_result();
			$result->next_result();
		}
		//end
		
		//increment termCode
		$len = strlen($termCode);
		$termCode++;
		
		$termCode = str_pad($termCode, $len, '0', STR_PAD_LEFT);
		//end
		
		$data['areaName'] = $areaName;
		$data['branchName'] = $brchName;
		
		$data['header'] = 'New POS Entry';
		$data['termCodeParams'] = 'class="validate[required] numbersOnly" maxlength="8" value="'. $termCode .'"';
		$data['termIDParams'] = 'class="validate[required]"';
		$data['submitBtnVal'] = 'maintenance/posnew/submit';
		$data['waitMsg'] = 'Sending new POS entry...';
		$data['submitBtnMsg'] = 'Submit new POS entry?';
		$this->load->view('maintenance/posx', $data);
	}
}
/* End of file posdup.php */
/* Location: ./application/contollers/maintenance/posdup.php */