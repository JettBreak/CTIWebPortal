<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WithdrawalTrx extends CI_Controller {
	
    function index()
	{	
		$this->load->view('tellering/withdrawaltrx');
	}
	
}