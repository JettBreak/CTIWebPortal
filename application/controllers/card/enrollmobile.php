<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EnrollMobile extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MOBILEENROLL_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		//$this->load->library('session');
		
		$cache	 = $this->cache;
		//$session = $this->session;
		$core 	 = $this->core;
		
		//$info = $session->userdata('cardInfo');
		$info = $_SESSION['cardInfo'];
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardBIN']	= $info['cardBIN'];
		$data['custName'] 	= $info['custName'];
		$data['cardStatus'] = $info['cardStatDesc'];
		$data['cardType'] 	= $info['cardType'];
		
		if (!$cellBIN = $cache->get($this->core->getSessionID() . 'cellBIN')) {
			$result = $this->card_model->getCellBIN();
			$cellBIN = $result->result_array();
			
			$result->free_result();
			$result->next_result();
		
			$cache->save($this->core->getSessionID() . 'cellBIN', $cellBIN, CACHE_TTL);
		}
					
		$data['cellBIN'] = NULL;
		foreach ($cellBIN as $row)
		{
			$data['cellBIN'] .= '<option value="'. $row['codevalue'] .'">'. $row['codevalue'] .'</option>';
		}
		
		//mobile allows / notification
		
		$data['defaultMsg'] = 'To activate mobile phone send ACT [MPIN] to 09229990213';
		
		$result = $this->card_model->getDefaultTranAllows('CELL');
		
		$data['tranAllows'] = NULL;
		
		$allows = '1'; //set MOBILE BALANCE INQUIRY default checked
		
		foreach ($result->result_array() as $row) {
			$bitNo = $row['bitno'];
			
			$checked = substr($allows, $bitNo - 1, 1) === '1' ? ' checked' : NULL;
			$data['tranAllows'] .= '<input type="checkbox" id="bit'. $bitNo .'" name="allows[]" value="'. $bitNo .'"'. $checked .'/>'. $row['description'] .'<br />';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		             
		$this->load->view('card/enrollmobile', $data);
	}
	
	function link()
	{
		$this->load->model('coresys/misc_model');
		$this->load->model('coreapp/card_model');
		
		$this->load->library('session');
		$this->load->library('coreconverters');
		
		$card 	 = $this->card_model;
		$session = $this->session;
		$core 	 = $this->core;
		$input 	 = $this->input;
		
		//$info 	 = $session->userdata('cardInfo');
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$prseqno 	 = $info['prseqno'];
		$cifseqno 	 = $info['cifseqno'];
		$brseqno 	 = $info['brseqno'];
		
		$areaCode 	 = $input->post('custAreaCodeMobile', TRUE);
		$num		 = $input->post('custMobile', TRUE);
		
		$prKey 	 	 = $areaCode . $num;
		$cardBIN	 = $input->post('cardBIN', TRUE);
		
		$allows = $this->input->post('allows');
		
		if ($allows) {
			$max = max($allows);
			$bin = '';
			for ($i = 1; $i <= $max; $i++) {	
				if (in_array($i, $allows)) {
					$bin .= '1';
				} else {
					$bin .= '0';
				}
			}
		} else {
			$bin = '0';
		}
		
		$hex = $this->coreconverters->asciiBinToHex($bin);
		
		$ipAddress	 = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit	 = $core->getUserID();
		$userOverride = $session->userdata('userOverride');
		$sessionID   = $core->getSessionID();
		
		$result = $card->insertCardMobileLink(
			$prseqno,
			$cifseqno,
			$brseqno,
			$prKey,
			$cardBIN,
			$hex,
			$ipAddress,
			$workstation,
			$userAudit,
			$userOverride,
			$sessionID
		);
		$session->unset_userdata('userOverride');
		$row = $result->row_array();
		
		$errNo = intval($row['errno']);
		
		if ($errNo > 0) {
			$success = FALSE;
			
			$message = $row['errmsg'];
			if (!$message) {
				$message = 'Cannot link card to mobile';
			}
			
		} else {
			$success = TRUE;
			$message = 'Account successfully linked to mobile';
			
			$result->free_result();
			$result->next_result();
			
			//push message
			if ($this->input->post('notifyUser')) {
				$notifyMsg = $this->input->post('notifyMsg', TRUE);
				
				$result = $this->misc_model->smsNotification($prKey, $notifyMsg, 9090);
			
				$result->free_result();
				$result->next_result();
			}
		}
		
		echo json_encode(array(
			'success' => $success,
			'errno'	  => $errNo,
			'message' => $message,
			'bin' => $bin,
			'hex' => $hex
		));
	}
}
/* End of file enrollmobile.php */
/* Location: ./application/controllers/card/enrollmobile.php */