<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class VerifyMobile extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(MOBILESEARCH_NO);
		$this->core->checkUserAllows(MOBILEENROLL_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		//save cellBIN to cache
		if (!$cellBIN = $this->cache->get($this->core->getSessionID() . 'cellBIN')) {
			$this->load->model('coreapp/card_model');
			$cellBIN = $this->card_model->getCellBIN()->result_array();
			$this->cache->save($this->core->getSessionID() .'cellBIN', $cellBIN, CACHE_TTL);
		}
					
		$data['cellBIN'] = NULL;
		foreach ($cellBIN as $row)
		{
			$data['cellBIN'] .= '<option value="'. $row['codevalue'] .'">'. $row['codevalue'] .'</option>';
		}
		
		//$this->output->cache(CACHE_TTL);
		$this->load->view('card/verifymobile', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$input = $this->input;
		
		$cellBIN = $input->post('cellBIN1', TRUE) . $input->post('cellBIN2', TRUE);
		
		$result = $this->card_model->getmobileinfo($cellBIN);
		
		if ($result->num_rows() > 0) {
			$success = TRUE;
		} else {
			$success = FALSE;
		}
		
		echo json_encode(array(
			'success' => $success
		));
	}
}