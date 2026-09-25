<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AccountEnrollment extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ACCNTENROLL_NO);
	}
	
	function index()
	{		
		$this->load->library('session');
		
		$session = $this->session;
		
		if (!$info = $session->userdata('cardInfo')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		};
		
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardBIN']	= $info['cardBIN'];
		$data['custName'] 	= $info['custName'];
		$data['cardStatus'] = $info['cardStatus'];
		$data['cardType'] 	= $info['cardType'];
		
		$session->set_flashdata('prseqno', $data['prseqno']);
		
		$this->load->view('card/accountenrollment', $data);
	}
	
	function getData()
	{
		session_start();
		
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('core');
		$this->load->library('shortxml');
		
		$card	   = $this->card_model;
		$session   = $this->session;
		$core      = $this->core;
		$xml 	   = $this->shortxml;
		
		$session->keep_flashdata('prseqno');
		$prseqno   = $session->flashdata('prseqno');
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result    = $card->getCardAccountLink($prseqno, $userAudit, $sessionID);
		
		unset($_SESSION['accounts']);
		
		$aaData = array();
		$prptr = NULL;
		
		$_SESSION['accounts'] = array();
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row)
			{
				$xml->setXML($row['xml1']);
				
				$prptr 		= $row['prptr'];
				$accntType 	= $xml->getValue('ACCTTYPE');
				$accntNo 	= $xml->getValue('ACCTNO');
				$authType 	= $xml->getValue('AUTHTYPE');
				$primary 	= $xml->getValue('PRIMARY');
				$pseqnoLink	= $row['pseqnolink'];
				
				$_SESSION['accounts'][] = $accntNo;
				
				$aaData[] = array(
					$prptr,
					$accntType,
					$accntNo,
					$authType,
					$primary,
					$pseqnoLink
				);
				
				$prptr++; 
			}
		}
		
		if ($result->num_rows === 0) {
			$isPrimary = 'Y';
		} else {
			$isPrimary = 'N';
		}
		
		$data = array(
			'prptr'   => $prptr,
			'primary' => $isPrimary
		);
		
		$session->set_userdata($data);
		
		echo json_encode(array(
			'aaData' => $aaData
		));
	}
	
	function remove()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('core');
		
		$card		= $this->card_model;
		$session	= $this->session;
		$core		= $this->core;
		$input 		= $this->input;
		
		$prseqno 	= $input->post('prseqno', TRUE);
		$pseqnoLink = $input->post('pseqnoLink', TRUE);
		$prptr 		= $input->post('prptr', TRUE);
		$userAudit  = $core->getUserID();
		$sessionID  = $core->getSessionID();
		
		$result 	= $card->deleteCardAccountLink($prseqno, $pseqnoLink, $prptr, $userAudit, $sessionID);
		
		$session->set_flashdata('prseqno', $prseqno);
		
		echo json_encode(array(
			'removeaccount' => $result
		));
	}
}
/* End of file accountenrollment.php */
/* Location: ./application/controllers/card/accountenrollment.php */