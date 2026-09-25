<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {	

	function index()
	{
		$inst = $_SESSION['inst'];

		$data['folder'] = $inst['css'];
		$data['bankName'] = $inst['bankName'];
		$data['appName'] = APPNAME;
		$data['siteURL'] = $inst['siteURL'];
		
		$this->load->view('welcome', $data);
	}
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */