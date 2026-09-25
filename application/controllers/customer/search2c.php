<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search2 extends CI_Controller {
	
	function index()
	{
		$this->load->library('core');
		$this->core->checkUserAllows(CUSTNEW_NO);
		
		$this->load->view('customer/search2');
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$success = TRUE;
		
		echo json_encode(array(
			'success' => $success
		));
	}
	
	function cache()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		$input = $this->input;
		
		$cifseqno = $input->post('cifseqno', TRUE);
		
		$custInfo = array(
			'cifseqno' => $cifseqno
		);
		
		$success = $this->cache->save($this->core->getSessionID() .'cust', $custInfo, CACHE_TTL);
		
		echo json_encode(array(
			'success' => $success
		));
	}
}