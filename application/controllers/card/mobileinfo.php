<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MobileInfo extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(MOBILEINFO_NO);
		$this->core->checkUserAllows(MOBILEENROLL_NO);
	}
	
	function index($cellNo = NULL)
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
		
		$data['cellNo'] = '0';
		$this->load->view('card/mobileinfo', $data);
	}
}