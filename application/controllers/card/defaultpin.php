<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DefaultPIN extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(GENDEFPIN);
	}
	
	function index()
	{
		$this->load->model('coreapp/card_model');
		
		$info = $_SESSION['pinLen'];	
		$data['minPIN'] = $info['minPIN'];
		$data['maxPIN'] = $info['maxPIN'];
		
		$result = $this->card_model->getPINGenBatch();
		
		$row = $result->row_array();
		
		$result = $this->card_model->getPINGenBatch();
		
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		$data['batchNo'] = intval($row['batchno']) + intval($row['stepcount']);
		
		$this->load->view('card/defaultpin', $data);
	}
	
	function cache()
	{		
		$_SESSION['pinLen'] = $_POST;

		echo json_encode(array(
			'success' => TRUE
		));
	}
	
	function generate()
	{
		$this->load->model('coreapp/card_model');
		$this->load->model('coresys/misc_model');
		$this->load->library('shortxml');
		$this->load->library('session');
		
		$card = $this->card_model;
		$misc = $this->misc_model;
		$xml = $this->shortxml;
		
		$status = 1;
		$msgtype = 20; //processing
		$trxcode = 938888; //generate default PIN
		$prtype = 'CARD';
		$userAudit = $this->core->getUserID();
		$override = $this->session->userdata('userOverride');
		$workstation = $this->core->getWorkstation();
		
		$batchNo = $this->input->post('batchNo', TRUE);
		$defaultPIN = $this->input->post('defaultPIN', TRUE);
		$cards = $this->input->post('cards');
		
		foreach ($cards as $param) {
			$card->db->trans_begin();
			$misc->db->trans_begin();
			
			$prseqno = $param[0];
			$prkey = $param[1];
			
			$xml1 = '<DPIN>'. $defaultPIN .'</>';
			
			$result = $misc->insertDefaultPINGenBatch($status, $msgtype, $trxcode, $prtype, $prkey, $xml1, $userAudit, $override, $workstation);
			
			$row = $result->row_array();
			
			$bchxno = $row['batchseqno'];
			
			$result->free_result();
			$result->next_result();
			
			$xml->setXML($param[2]);
			$xml->editTag('PGENO', $batchNo);
			$xml->editTag('DPIN', $defaultPIN);
			$xml->editTag('BCHXNO', $bchxno);
			$xml1 = $xml->getXML();
			
			$result = $card->updateCardDefPIN($prseqno, $xml1);
			
			if ($card->db->trans_status() === FALSE) {
				$success = FALSE;
				$card->db->trans_rollback();
			} else {
				$success = TRUE;
				//$card->db->trans_rollback();
				$card->db->trans_commit();
			}
			
			if ($misc->db->trans_status() === FALSE) {
				$success = FALSE;
				$misc->db->trans_rollback();
			} else {
				$success = TRUE;
				//$misc->db->trans_rollback();
				$misc->db->trans_commit();
			}
		}
		
		//update sequencx
		if ($success) {
			$card->updatePINGenSequence($batchNo);
		}
		
		echo json_encode(array(
			'success' => $success
		));
	}
}