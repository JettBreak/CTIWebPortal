<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PDFGenerator extends CI_Controller {	
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(BARTSFILE_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		$pdfGen = '';
		
		//branches combobox
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$branchList = NULL;
		foreach ($branches as $row) {
			$branchList .= '<option value="'. $row['brcode'] .'" brname="'. $row['brname'] .'">'. $row['brname'] .'</option>';
		}
		
		$data['branches'] = NULL;
		if ($this->core->canRep()) {
			$data['branches'] = '<tr>
				<td><label for="branchList">Branch:</label></td>
				<td><select name="branch" id="branchList" style="width:162px">
						<option value="0" brname="ALL BRANCHES">ALL</option>
						'. $branchList .'
					</select></td>
			</tr>';
		}
		//end
		
		//report types
		$r = $this->core->getNavMenu();
		$reportTypes = $r[REPORTS_NO]['subMenu'];
		
		$options = NULL;
		$formAction = NULL;
		$pdfGen = NULL;
		foreach ($reportTypes as $key => $report) {
			if (in_array($key, array(PDFGEN_NO, REPBRANCHLOG_NO, BARTSFILE_NO, BILLSPAYMENTREP_NO, PINMAILERBATCHREP_NO, REPORTPROCLIST_NO))) {
				continue;
			}
			if ($formAction === NULL) {
				$formAction = $report['link'];
			}
			if (substr($this->core->getUserAllows(), $key - 1, 1) === '1') {
				$options .= '<option value="'. $report['link'] .'" rtype="'. $report['type'] .'" isChannel="'. $report['isChannel'] .'">'. $report['name'] .'</option>';
				$pdfGen[] = $key;
			}
		}
		
		if (count($pdfGen) < 1) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'You are not allowed to access this module'
			));
			exit();
		}
		$data['reportTypes'] = $options;
		
		//channel type
		if (!$termTypes = $this->cache->get($this->core->getSessionID() . 'termTypes')) {
			$this->load->model('coreapp/reports_model');
			$result = $this->reports_model->getTermTypes();
			
			$termTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'termTypes', $termTypes, CACHE_TTL);
		}
		
		$options = '<option value="0">All</option>';
		
		if (count($termTypes)) {
			foreach ($termTypes as $row) {
				$options .= '<option>'. $row['codevalue'] .'</option>';
			}
		} else {
			$options .= '<option value="">No Channel Types Defined</option>';
		}
		
		$data['termTypes']  = $options;
		
		//$data['formAction'] = 'reports/atmavailability/preview';
		$data['formAction'] = $formAction;
		//end
		
		$data['title'] = 'PDF Generator';
		$data['date'] = date('m/d/Y');
		$this->load->view('reports/pdfgenerator', $data);
	}
	
}