<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DepositTrx extends CI_Controller {
	
    function index()
	{	
		$this->load->view('tellering/deposittrx');
	}
	
}