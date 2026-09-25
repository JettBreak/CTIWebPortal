<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CardInquiry extends CI_Controller {
	
    function index()
	{	
		$this->load->view('tellering/cardinquiry');
	}
	
}