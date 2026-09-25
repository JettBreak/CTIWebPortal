<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GenDefPIN extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(GENDEFPIN);
	}
	
	function index()
	{
		$this->load->library('shortxml');
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		$xml = $this->shortxml;
		
		if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
			$this->load->model('coreapp/card_model');

			$cardBIN = $this->card_model->getCardBIN()->result_array();
			$this->cache->save($this->core->getSessionID() .'cardBIN', $cardBIN, CACHE_TTL);
		}
		
		$data['cardBIN'] = NULL;
		foreach ($cardBIN as $row)
		{
			$xml->setXML($row['xml1']);
			$minPIN = $xml->getValue('MINPIN');
			$maxPIN = $xml->getValue('MAXPIN');
			
			$data['cardBIN'] .= '<option value="'. $row['codevalue'] .'" minpin="'. $minPIN .'" maxpin="'. $maxPIN .'">'. $row['codevalue'] .'</option>';
		}
		
		$this->load->view('card/gendefpin', $data);
	}
	
	function getData()
	{
		$this->load->model('coreapp/card_model');
		$this->load->model('coresys/misc_model');
		$this->load->library('shortxml');
		
		$core = $this->core;
		$xml = $this->shortxml;
		
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$cardBIN = $this->input->get('cardBIN', TRUE);
		
		$msgTypes = array(
			20 => 'Processing',
			21 => 'Completed',
			23 => 'Error'
		);
		
		$result = $this->misc_model->getBatchStatusList();
		
		$statusArr = $result->result_array();
		
		$result->free_result();
		$result->next_result();
		
		$result = $this->card_model->getCardListForPINGen($cardBIN);
		
		$details = array();
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$xml1 = $row['xml1'];
				$xml->setXML($xml1);
				
				$prseqno = $row['prseqno'];
				$prkey = $row['prkey'];
				$bchxno = intval($xml->getValue('BCHXNO'));
				$remarks = 'For PIN Generation';
				$disabled = '';
				$readonly = '';
				$msgtype = 0;
				
				foreach ($statusArr as $batch) {
					$batchseqno = intval($batch['batchseqno']);
					$msgtype = 0;
					
					if ($batchseqno === $bchxno) {
						$msgtype = intval($batch['msgtype']);
						$remarks = $msgTypes[$msgtype];
						
						/*if ($msgtype === 20) {
							$disabled = 'disabled';
							$readonly = 'readonly';
						}*/
						
						break;
					}
				}
				
				$details[] = array(
					'<input type="checkbox" name="cards[]" id="' . $prseqno . '" value="' . $prkey . '" xml="'. $xml1 .'" '. $disabled .' '. $readonly .'/>',
					$prkey,
					$row['acctdesc'],
					$remarks,
					$msgtype
				);
			}
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => TRUE,
			'details' => $details
		));
	}
}