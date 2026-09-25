<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Useroverride extends CI_Controller {
	
    function index()
	{
		$this->output->cache(CACHE_TTL);
		$this->load->view('override');
	}
	
	function submit()
	{
		$this->load->model('coreapp/user_model');
		$this->load->library('core');
		
		$user  = $this->user_model;
		$core  = $this->core;
		$input = $this->input;
		
		$userID		 = $core->getUserID();
		$overrideUID = $input->post('overrideUID', TRUE);
		$overridePW  = $this->core->encrypt($overrideUID, $input->post('overridePW', TRUE));
		$branchID 	 = $core->getBranchID();
		
		if ($overrideUID === $userID) {
			$success = FALSE;
			$message = 'Process cannot be overriden by the same user';
			$errNo = 1;
		} else {
			$result = $user->userOverride($overrideUID, $overridePW, $branchID);
			$row 	= $result->row_array();
			
			$errNo 	= isset($row['v_errno']) ? trim((string) $row['v_errno']) : '';
			$message = isset($row['v_errmsg']) ? $row['v_errmsg'] : 'Override validation failed';
			
			if ($errNo !== '0') {
				$success = FALSE;
			} else {
				$success = TRUE;
				$this->load->library('session');
				$this->session->set_userdata(array('userOverride' => $overrideUID));
			}
		}

		echo json_encode(array(
			'success' => $success, 
			'message' => $message,
			'errNo' => $errNo
		));
	}
}
/* End of file useroverride.php */
/* Location: ./application/controllers/useroverride.php */
