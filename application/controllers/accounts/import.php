<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends CI_Controller {	
	function index()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$card 	 = $this->card_model;
		$core 	 = $this->core;
		
		$result = $card->getAccountBIN();
		$row 	= $result->row_array();
		$num 	= $row['NUM'];
		
		$branchID = $core->getBranchID();
		
		$data['accountNo']	= $num . '00'. $branchID;
		
		$this->load->view('accounts/import', $data);
	}
}
/* End of file import.php */
/* Location: ./application/contollers/accounts/import.php */