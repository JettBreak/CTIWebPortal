<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {
	
	function index()
	{
		$this->load->library('core');
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
			$this->load->model('coreapp/card_model');

			$cardBIN = $this->card_model->getCardBIN()->result_array();
			$this->cache->save($this->core->getSessionID() .'cardBIN', $cardBIN, CACHE_TTL);
		}
		
		$data['cardBIN'] = NULL;
		foreach ($cardBIN as $row)
		{
			$data['cardBIN'] .= '<option value="'. $row['codevalue'] .'">'. $row['codevalue'] .'</option>';
		}
		
		//$this->output->cache(CACHE_TTL);
		$this->load->view('card/search', $data);
	}
	
}
/* End of file search.php */
/* Location: ./application/controllers/card/search.php */