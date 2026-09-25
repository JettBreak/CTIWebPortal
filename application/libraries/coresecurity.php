<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CoreSecurity extends CI_Controller {
	private $session, $core, $db;
	
	function __construct()
	{
		$CI =& get_instance();
		$CI->load->library('session');
		$CI->load->library('core');
		
		$this->session = $CI->session;
		$this->core = $CI->core;
		
		$this->_confirmLoggedIn();
		//$this->_validateReferrer();
	}
	
	function _confirmLoggedIn() {
		$loggedIn  = $this->core->isLoggedIn();
		
		$basedir = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		
		/*$public = array(
			$basedir,
			$basedir .'login',
			$basedir .'login/submit',
			$basedir .'logout',
			$basedir .'card/upload/submit',
			$basedir .'setpw',
			$basedir .'setpw/submit',
			$basedir .'setpw2',
			$basedir .'setpw2/submit',
			$basedir .'forgotpw',
			$basedir .'forgotpw/verify',
			$basedir .'forgotpw/submit',
			$basedir .'superuser/checklogin',
			$basedir .'superuser/getsession',
			$basedir .'superuser/create',
			$basedir .'superuser/getemployees'
		);*/
		if (isset($_SESSION['loggedIn']) && $loggedIn === TRUE) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Your session has expired.<br>click <a href="http://'. $_SERVER['HTTP_HOST'] . $basedir .'">here</a> to login'
			));
			exit();
		}
		/*if(!in_array($_SERVER['REQUEST_URI'], $public)) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Your session has expired.<br>click <a href="http://'. $_SERVER['HTTP_HOST'] . $basedir .'">here</a> to login'
			));
			exit();
			//die('Denied');
			//header('Location: http://corewaretech.com/beta/portal');
		}*/
	}
	
	function _validateReferrer() {		
		//SITEURL
		/*$coreware = array(
			'http://corewaretech.com/beta/portal/',
			'http://www.corewaretech.com/beta/portal/'
		);
		if ((isset($_SERVER['HTTP_REFERER']) === TRUE)) {
			if (in_array($_SERVER['HTTP_REFERER'], $coreware) === FALSE) {
				
			}
		}*/
	}
	
	function _initDb($db) {
		$this->db = $db;
	}
	
	/**
	 * Query Validator
	 *
	 * @param	string	query string
	 * @param	string	query parameters
	 * @return	query result
	 */
	function validateQuery($query, $params = NULL)
	{
		$result = $this->db->query($query, $params);
		
		$row 	= $result->row_array();

		if ((array_key_exists('errno', $row) && (array_key_exists('errmsg', $row))) === TRUE) {
			$errNo 	= $row['errno'];
			$errMsg	= $row['errmsg'];

			if (($errNo === '8') && ($errNo !== NULL)) {
				echo json_encode(array(
					'success' => FALSE,
					'errorno' => $errNo,
					'message' => $errMsg
				));
				exit();
			}
		}
		return $result;	
	}
	
	// --------------------------------------------------------------------
}
/* End of file CoreSecurity.php */
/* Location: ./application/libraries/CoreSecurity.php */
