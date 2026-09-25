<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SuperUser extends CI_Controller {

	function checkLogIn()
	{
		$userID = $this->input->post('userID');
		$password = $this->input->post('password');
		
		$success = FALSE;
		$token = NULL;
		
		if ($userID === 'core' && $password === date('mdY')) {
			$success = TRUE;
			
			$token = $_SESSION['token'] = sha1(date('mdyhis'));
			$_SESSION['inst'] = array(
				'host' => '192.168.168.50',
				'db1' => 'coreapp_demo',
				'db2' => 'coresys_demo'
			);
		}
		
		echo json_encode(array(
			'success' => $success,
			'token' => $token
		));
	}
	
	function create()
	{
		$this->load->model('coreapp/user_model');
		$this->load->library('core');
			
		$token = $this->input->post('token', TRUE);
		
		$success = FALSE;
		$message = 'An error has occured';
		
		if ($token === $_SESSION['token']) {
			$superUserID = $this->input->post('superUserID', TRUE);
			$password = $this->input->post('password', TRUE);
			$dateExp = $this->input->post('dateExp', TRUE);
		
			$xml1 = '<USER>'. $superUserID .'</>'.
				'<PASS>'. $this->core->encrypt($superUserID, $password) .'</>'.
				'<STAT>1</>'.
				'<DTEXP>'. $dateExp .'</>';
			
			$result = $this->user_model->createSuperUser($xml1);
			
			$row = $result->row_array();
			
			if (intval($row['errno']) === 0) {
				$success = TRUE;
				$message = 'Super User created successfully';
				session_destroy();
			}
		}

		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
	
	function getSession()
	{
		if (isset($_SESSION['token'])) {
			$success = $_SESSION['token'];
		} else {
			$success = FALSE;
		}
		//session_destroy();
		echo json_encode(array(
			'success' => $success
		));
	}
}