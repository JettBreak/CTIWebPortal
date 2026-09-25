<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class OnlineLimits extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDINFO);
	}
	
	function index()
	{		
		$session = $_SESSION['onlineLimits'];
		
		$description = $session['description'];
		
		$data['title'] 			= $description .' Limits';
		
		//hidden fields
		$data['prseqno']		= $session['prseqno'];
		$data['limitseqno'] 	= $session['limitseqno'];
		$data['trxcode'] 		= $session['trxcode'];
		$data['cycle'] 			= $session['cycle'];
		$data['duralimit'] 		= $session['duralimit'];
		//end
		
		$data['cycleavail'] 	= $session['cycleavail'];
		$data['cyclemax'] 		= $session['cyclemax'];
		$data['cyclemaxAttr']	= $session['cyclemax'] === 'N/A' ? ' readonly' : NULL;
		$data['ctravail'] 		= $session['ctravail'];
		$data['ctrmax'] 		= $session['ctrmax'];
		$data['tranmin'] 		= $session['tranmin'];
		$data['tranminAttr']	= $session['tranmin'] === 'N/A' ? ' readonly' : NULL;
		$data['tranmax'] 		= $session['tranmax'];
		$data['tranmaxAttr']	= $session['tranmax'] === 'N/A' ? ' readonly' : NULL;
		$data['nonfeetranavail'] = $session['nonfeetranavail'];
		$data['nonfeetranmax'] 	= $session['nonfeetranmax'];
		$data['nonfeectravail'] = $session['nonfeectravail'];
		$data['nonfeectrmax'] 	= $session['nonfeectrmax'];
		$data['nonfeetranavail'] = $session['nonfeetranavail'];
		$data['nonfeetranmax'] = $session['nonfeetranmax'];
		$data['nonfeetranmaxAttr'] = $session['nonfeetranmax'] === 'N/A' ? ' readonly' : NULL;
		$data['nonfeecycle'] 	= $session['nonfeecycle'];
		
		$this->load->view('card/onlinelimits', $data);
	}
	
	function cache()
	{
		//$input = $this->input;
		
		$_SESSION['onlineLimits'] = $_POST;
		/*array(
			'description' => $input->post('description', TRUE)
		);*/
		
		echo json_encode(array(
			'success' => TRUE
		));
	}
	
	function loadDefaults()
	{
		$this->load->model('coreapp/card_model');
		
		$core = $this->core;
		
		$limitseqno = $this->input->get('limitseqno', TRUE);
		$result = $this->card_model->getCardDefaultLimitList($limitseqno);
		
		$details = array();
		
		if ($result->num_rows > 0) {
			foreach ($result->result_array() as $row) {
				if ($row['isamtlimit'] === 'N') {
					$cycleAvail = 'N/A';
					$cycleMax = 'N/A';
					$tranMin = 'N/A';
					$tranMax = 'N/A';
					$nonFeeTranAvail = 'N/A';
					$nonFeeTranMax = 'N/A';
				} else {
					$cycleAvail = $core->currency($row['cycleavail']);
					$cycleMax = $core->currency($row['cyclemax']);
					$tranMin = $core->currency($row['tranmin']);
					$tranMax = $core->currency($row['tranmax']);
					$nonFeeTranAvail = $core->currency($row['nonfeetranavail']);
					$nonFeeTranMax = $core->currency($row['nonfeetranmax']);
				}
				
				$details[] = array(
					$row['description'],
					$row['limitseqno'],
					$row['trxcode'],
					$cycleAvail,
					$cycleMax,
					$row['ctravail'] ? $row['ctravail'] : 0,
					$row['ctrmax'] ? $row['ctrmax'] : 0,
					$tranMin,
					$tranMax,
					$row['cycle'] ? $row['cycle'] : 0,
					$row['duralimit'] ? $row['duralimit'] : 0,
					$row['nonfeectravail'] ? $row['nonfeectravail'] : 0,
					$row['nonfeectrmax'] ? $row['nonfeectrmax'] : 0,
					$nonFeeTranAvail,
					$nonFeeTranMax,
					$row['nonfeecycle'] ? $row['nonfeecycle'] : 0
				);
			}
			$success = TRUE;
		} else {
			$success = FALSE;
			$details = 'An error has occured';
		}
		
		$result->free_result();
		$result->next_result();

		echo json_encode(array(
			'success' => $success,
			'details' => $details
		));
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$input = $this->input;
		
		$prseqno = $input->post('prseqno', TRUE);
		$limitseqno = $input->post('limitseqno', TRUE);
		$trxcode = $input->post('trxcode', TRUE);
		$cycleavail = str_replace(',', '', $input->post('cycleavail', TRUE));
		$cyclemax = str_replace(',', '', $input->post('cyclemax', TRUE));
		$ctravail = $input->post('ctravail', TRUE);
		$ctrmax = $input->post('ctrmax', TRUE);
		$tranmax = str_replace(',', '', $input->post('tranmax', TRUE));
		$tranmin = str_replace(',', '', $input->post('tranmin', TRUE));
		$cycle = $input->post('cycle', TRUE);
		$duralimit = $input->post('duralimit', TRUE);
		$nonfeectrmax = $input->post('nonfeectrmax', TRUE);
		$nonfeetranmax = str_replace(',', '', $input->post('nonfeetranmax', TRUE));
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$override = '';
		$sessionID = $this->core->getSessionID();
		
		$result = $this->card_model->updateOnlineLimit(
			$prseqno,
			$limitseqno,
			$trxcode,
			$cycleavail,
			$cyclemax,
			$ctravail,
			$ctrmax,
			$tranmax,
			$tranmin,
			$cycle,
			$duralimit,
			$nonfeectrmax,
			$nonfeetranmax,
			$brseqno,
			$ipAddress,
			$workstation,
			$userAudit,
			$override,
			$sessionID
		);
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Limits updated successfully'
		));
	}
	
	function setLimits()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		
		$prseqno = $this->input->post('prseqno', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$override = '';
		$sessionID = $this->core->getSessionID();
		
		$pinctr = $this->input->post('pinctr', TRUE);
		if ($pinctr) {
			$result = $this->card_model->setPINRetryMaxCount(
				$pinctr,
				$prseqno,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$override,
				$sessionID
			);
			
			$result->free_result();
			$result->next_result();
		}
		
		$limitseqno = $this->input->post('limitseqno', TRUE);
		if ($limitseqno) {
			$result = $this->card_model->setOnlineDefaultCardLimit(
				$prseqno,
				$limitseqno,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$override,
				$sessionID
			);
			
			$result->free_result();
			$result->next_result();
		}
		
		$this->session->unset_userdata('cardInfo');
		
		$success = TRUE;
		$message = 'Your changes were successfully saved';
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
	
	//get online limits
	function get()
	{
		$this->load->model('coreapp/card_model');
		
		$core = $this->core;
		$prseqno = $this->input->post('prseqno', TRUE);
		
		$result = $this->card_model->getCardLimitList($prseqno);
		
		$details = array();
		foreach ($result->result_array() as $row)
		{
			if ($row['isamtlimit'] === 'N') {
				$cycleAvail = 'N/A';
				$cycleMax = 'N/A';
				$tranMin = 'N/A';
				$tranMax = 'N/A';
				$nonFeeTranAvail = 'N/A';
				$nonFeeTranMax = 'N/A';
			} else {
				$cycleAvail = $core->currency($row['cycleavail']);
				$cycleMax = $core->currency($row['cyclemax']);
				$tranMin = $core->currency($row['tranmin']);
				$tranMax = $core->currency($row['tranmax']);
				$nonFeeTranAvail = $core->currency($row['nonfeetranavail']);
				$nonFeeTranMax = $core->currency($row['nonfeetranmax']);
			}
			
			$details[] = array(
				$row['description'],
				$row['limitseqno'],
				$row['trxcode'],
				$cycleAvail,
				$cycleMax,
				($row['ctravail'] ? $row['ctravail'] : 0),
				($row['ctrmax'] ? $row['ctrmax'] : 0),
				$tranMin,
				$tranMax,
				($row['cycle'] ? $row['cycle'] : 0),
				($row['duralimit'] ? $row['duralimit'] : 0),
				($row['nonfeectravail'] ? $row['nonfeectravail'] : 0),
				($row['nonfeectrmax'] ? $row['nonfeectrmax'] : 0),
				$nonFeeTranAvail,
				$nonFeeTranMax,
				($row['nonfeecycle'] ? $row['nonfeecycle'] : 0)
			);
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		echo json_encode(array(
			'success' => TRUE,
			'details' => $details
		));
	}
}