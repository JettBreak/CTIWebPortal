<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class StatusChange extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CHANGECARDSTAT_NO);
	}
	
	function index()
	{
		//$this->load->library('session');
		
		//$session = $this->session;
		//$core    = $this->core;
		
		//$info = $session->userdata('cardInfo');
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		$data['prseqno'] = $info['prseqno'];
		$data['cardBIN'] = $info['cardBIN'];
		$data['custName'] = $info['custName'];
		$data['cardStatus'] = $info['cardStatus'];
		$data['cardStatDesc'] = $info['cardStatDesc'];
		$data['cardType'] = $info['cardType'];
		
		$statusList = $this->core->getCardStatusList();
		
		//remove current card status in combobox
		$data['newCardStatus'] = NULL;
		foreach ($statusList as $value => $desc) {
			if ($data['cardStatus'] != $value) {
				$data['newCardStatus'] .= '<option value="'. $value .'">'. strtoupper($desc) .'</option>';
			}
		}
		
		$this->load->view('card/statuschange', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');	
		
		$card  = $this->card_model;
		$core  = $this->core;
		$input = $this->input;
		
		$prseqno   = $input->post('prseqno', TRUE);
		$status    = $input->post('newCardStatus', TRUE);
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();	
		$userAudit = $core->getUserID();
		$userOverride = $this->session->userdata('userOverride');
		$sessionID = $core->getSessionID();
		
		$statDesc = NULL;
		foreach ($core->getCardStatusList() as $val => $desc) {
			if ($status == $val) {
				$statDesc = $desc;
			}
		}
		
		$result = $card->updateCardStatus($prseqno, $status, $statDesc, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID);
		
		$this->session->unset_userdata('userOverride');
		$message = 'Card status successfully updated';
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => $message
		));
	}
}
/* End of file statuschange.php */
/* Location: ./application/controllers/card/statuschange.php */