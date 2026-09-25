<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CardList extends CI_Controller {
	
	function index()
	{
		$this->load->view('card/cardlist');
	}
	
}