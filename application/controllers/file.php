<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class File extends CI_Controller
{		
	function index()
	{
		echo '';
	}
	
	function js()
	{
		$segs = $this->uri->segment_array();
		
		foreach ($segs as $segment)
		{
			$filepath = $segment.'.js';
			
			if(file_exists($filepath))
			{
				readfile($filepath);
			}
		}
	}
	
	function css()
	{
		$segs = $this->uri->segment_array();
		
		foreach ($segs as $segment)
		{
			$filepath = 'css/'. $segment .'.css';
			if(file_exists($filepath))
			{
				readfile($filepath);
			}
		}
	}
}