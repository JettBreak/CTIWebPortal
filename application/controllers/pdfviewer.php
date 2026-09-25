<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PDFViewer extends CI_Controller {
	
    function index()
	{	
		$this->load->view('loading');
	}
	
	function preview()
	{
		$data['src'] = 'pdfviewer';
		$this->load->view('pdfviewer', $data);
	}
	
	function norecord()
	{
		$this->load->view('norecord');
	}
}