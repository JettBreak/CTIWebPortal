<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Longpoll extends CI_Controller {
	
    function index()
	{
		$this->load->view('longpoll');
	}
	
	function getData()
	{
		$filename = realpath(APPPATH .'../emboss/data.txt');
		
		$lastmodif = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
		$currentmodif = filemtime($filename);
		
		while ($currentmodif <= $lastmodif) {
			usleep(10000);
			clearstatcache();
			$currentmodif = filemtime($filename);
		}
		
		echo json_encode(array(
			'msg' => file_get_contents($filename),
			'timestamp' => $currentmodif
		));
	}
}