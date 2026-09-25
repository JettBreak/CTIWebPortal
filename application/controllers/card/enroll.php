<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Enroll extends CI_Controller {
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->library('core');
		
		$core = $this->core;
		
		$data['branch'] = $core->getBranchName();
		
		$result = NULL;
		if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
			$this->load->model('coreapp/card_model');
			
			$result = $this->card_model->getCardBIN();
			$cardBIN = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'cardBIN', $cardBIN, CACHE_TTL);
		}

		$bin = $cardBIN[0]['codevalue'];
		
		$data['cardNo'] = $bin . $core->getBranchCode();
		
		if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
			$this->load->model('coreapp/card_model');
			
			if ($result !== NULL) {
				$result->free_result();
				$result->next_result();
			}
			
			$result = $this->card_model->getCardType('N');
			$cardType = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
		}
		
		$data['cardType'] = NULL;
		foreach ($cardType as $row)
		{
			$val  = $row['accttype'];
			$desc = $row['description'];
			
			$data['cardType'] .= '<option value="'. $val .'">'. $desc .'</option>';
		}
		
		$data['dtInitIssue'] = date('F j, Y');
		$data['dtActivated'] = NULL;
		
		$data['dtExpiry'] = date('F j, Y', strtotime('+10 year'));
		$data['cardStatus'] = 'For Verification';
		
		$data['allows'] = NULL;
		for ($i = 0; $i <= 50; $i ++) {
			$data['allows'] .= '<input type="checkbox"/>AAAAAAAAAAAAAAAAAAAAAAAAAAA<br />';
		}
		$this->load->view('card/enroll', $data);
	}
	
}