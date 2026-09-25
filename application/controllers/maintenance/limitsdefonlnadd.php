<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LimitsDefOnlnAdd extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		
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
		$this->load->model('coreapp/card_model');
		
		$accttype = $this->input->get('type', TRUE);
		
		//get branches
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$data['branches'] = '<option value="9999">(Global)</option>';
		
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				$matched = $row['brseqno'] === $this->core->getBranchID() ? TRUE : FALSE;
				
				if (!$this->core->isHeadOffice() && $matched) {
					$data['branches'] = '<option value="9999">(Global)</option>'.
						'<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				
				if ($matched) {
					$selected = ' selected';
				} else {
					$selected = NULL;
				}
				
				$data['branches'] .= '<option value="'. $row['brseqno'] .'"'. $selected .'>'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		//end
		
		$data['accttype'] = $accttype;

		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/limitsdefonlnxx', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$input = $this->input;
		$core = $this->core;
		
		$limittype = 'ONLN';
		$accttype = $input->post('accttypex', TRUE);
		$grouptype = 'CARD';
		$description = $input->post('limitname', TRUE);
		$maxval = 0;
		$minval = 0;
		$pinctrdef = $input->post('pinctrdef', TRUE);
		$pinctrmax = $input->post('pinctrmax', TRUE);
		$brseqno = $input->post('branchx', TRUE);
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$override = '';
		$sessionID = $core->getSessionID();
		
		$result = $this->card_model->insertDefaultOnlineLimit(
			$limittype,
			$accttype,
			$grouptype,
			$description,
			$maxval,
			$minval,
			$pinctrdef,
			$pinctrmax,
			$brseqno,
			$ipAddress,
			$workstation,
			$userAudit,
			$override,
			$sessionID
		);
		
		$row = $result->row_array();
		$errno = intval($row['errno']);
		
		if ($errno > 0 || $errno == -1) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Created successfully';
		}
		//$message = $row['errmsg'];
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'limitseqno' => $row['limitseqno'],
			'desc' => $description,
			'pinctrdef' => $pinctrdef,
			'pinctrmax' => $pinctrmax
		));
	}
}