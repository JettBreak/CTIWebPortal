<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {
	
	private $inst;
	//function __construct()
	//{
		//parent::__construct();
		
		//session_destroy();
		//$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		//$this->cache->clean();
	//}
	
	function __construct() {
		parent::__construct();
		
		$this->inst = $_SESSION['inst'];
		
		//echo print_r($_SESSION);
	}
	
	function index()
	{
		//echo $_SESSION['inst'];
		
		$data['folder']	= $this->inst['css'];
		$data['bankName'] = $this->inst['bankName'];
		$data['siteURL'] = $this->inst['siteURL'];
		
		//session_destroy();
		
		$this->load->view('login', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/user_model');
		$this->load->model('coreapp/card_model');
		//$this->load->library('session');
		$this->load->library('core');
		$this->load->library('shortxml');
		
		$user	  = $this->user_model;
		$card 	  = $this->card_model;
		//$session  = $this->session;
		$core	  = $this->core;
		$input	  = $this->input;
		$xml	  = $this->shortxml;

		//$userlevel = $usererror;
		
		$userID 	 = $input->post('userID', TRUE);
		$pw 		 = $input->post('userPW', TRUE);
		$userPW		 = $core->encrypt($userID, $pw);
		$ipAddress 	 = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$ipAddress	 = $core->getIPAddress();
		//$sessionID	 = $core->encrypt($userID, $session->userdata('session_id'));
		$sessionID = session_id();
			
		$result = $user->superUserLogIn(
			$userID,
			$userPW,
			$sessionID,
			$workstation,
			$ipAddress
		);	
		
		$row = $result->row_array();

		//print_r($row);
		if ($row['errno'] === '0') {
			
			$result->free_result();
			$result->next_result();
			
			$result = $user->userLogIn(
				$userID,
				$userPW,
				$sessionID,
				$workstation,
				$ipAddress
			);
			
			$row = $result->row_array();
			
		}
		
		$result->free_result();
		$result->next_result();
		
		//get ONUS Code
		$result = $user->getONUSCODE();

		if ($result->num_rows() === 0) {
		echo json_encode(array(
		'success' => FALSE,
		'message' => 'No ONUS Code Defined'
		));
		return;
		}

		$ocr = $result->row_array(); // onuscode result
		$onusCode = $ocr['onusCode'];

		$result->free_result();
		$result->next_result();
		//end

		//get FINSWITCH
		$result = $user->getFINSWITCH();

		if ($result->num_rows() === 0) {
		echo json_encode(array(
		'success' => FALSE,
		'message' => 'No FINSWITCH Defined'
		));
		return;
		}
		
		$fsr = $result->row_array(); // finswitch result
		$finswitch = $fsr['finswitch'];
		
		$result->free_result();
		$result->next_result();
		
		//get BANK_CODE
		$result = $user->getBANKCODE();

		if ($result->num_rows() === 0) {
		echo json_encode(array(
		'success' => FALSE,
		'message' => 'No INSTCODE Defined'
		));
		return;
		}

		$bcr = $result->row_array(); // bankcode result
		$bnkCode = str_pad($bcr['clientcode'], 3, '0', STR_PAD_LEFT);

		$result->free_result();
		$result->next_result();
		
		//get BANK_MNEM
		$result = $user->getBANKMNEM();

		if ($result->num_rows() === 0) {
		echo json_encode(array(
		'success' => FALSE,
		'message' => 'No BANKMNEM Defined'
		));
		return;
		}

		$bmr = $result->row_array(); // bankcode result
		$bnkMnem = $bmr['bankmnem'];

		$result->free_result();
		$result->next_result();
		//end

		$errn = 0;
		/*$result = $card->checkiftokencolexists($_SESSION['db1']);
		$token = $result->row_array();

		$errn = $token['errno'];
		$breakmsg = $token['errmsg'];*/

		$coreencrypt = $errn < 1;

		/*$result->free_result();
		$result->next_result();*/
		

		//get CORE_ENCRYPT
		$coreencrypt = array('coreencrypt'=>$coreencrypt);//$user->isCOREENCRYPT();

		if (count($coreencrypt) === 0) {
			$coreencrypt = FALSE;
		} else {
			$coreencrypt = TRUE;//$coreencrypt['coreencrypt'];
		}

		//$result->free_result();
		//$result->next_result();
		//end
		
		//print_r($row);
		$xml->setXML($row['brxml'] . $row['grpxml']);
		$userName 	= $row['username'];
		$instName 	= $row['instname'];
		$address 	= $row['addr'];
		$grpseqno 	= $row['grpseqno'];
		$branchID 	= $row['brseqno'];
		$branchName = $row['brname'];
		$branchCode = $row['brcode'];
		$regionCode = $row['regioncode'];
		$areaName	= $row['areaname'];
		$lastLogin 	= $row['lastlogin'] ? $core->formatDate('l, F j, Y g:i A', $row['lastlogin']) : 'Never';
		$userAllows = $row['allows'];
		$isHead		= $row['headofc'] === 'Y';
		$isMon		= $xml->getValue('ISMON') === 'Y';
		$isRep		= $xml->getValue('ISREP') === 'Y';
		$isUser		= $xml->getValue('ISUSER') === 'Y';
		$errNo		= $row['errno'];
		$errMsg		= $row['errmsg'];
		
		$secuOpt = $xml->getValue('SECUOPTION');
		$passOpt = $xml->getValue('PASSOPTION');
		$maxRetry = $xml->getValue('MAXRETRY');
		$minChar = $xml->getValue('MINCHAR');
		$passRecycle = $xml->getValue('PWCYCLE');
		
		$isTeller = $userID === 'coreware';
		
		$sessionExp = $xml->getValue('SESSIONEXP');
		
		$isSuper = FALSE;
		
		//user group options
		
		//check security option index 1 if on
		if (substr($secuOpt, 0, 1) !== '1') {
			$lastLogin = '';
		} else {
			$lastLogin = '<strong>Last Login: </strong>'. $lastLogin;
		}
		
		//check passoption index 3 if on
		if (substr($passOpt, 2, 1) !== '1' && $errNo == 90) {
			$errNo = '1';
		}
		//end
			
		if ( in_array($errNo, array('2', '1')) ) {				
			$sessionData = array(
				'sessionID'	 => $sessionID,
				'sessionExp' => $sessionExp,
				'userID'  	 => $userID,
				'userPW'	 => $userPW,
				'userName'   => $userName,
				'instName'	 => $instName,
				'address'	 => $address,
				'userGroup'	 => $grpseqno,
				'branchID'	 => $branchID,
				'branchName' => $branchName,
				'branchCode' => $branchCode,
				'regionCode' => $regionCode,
				'areaName' 	 => $areaName,
				'lastLogin'  => $lastLogin,
				'loggedIn' 	 => TRUE,
				'userAllows' => $userAllows,
				'secretQ'	 => $row['secretq'], 
				'isHead'	 => $isHead,
				'isMon'		 => $isMon,
				'isRep'		 => $isRep,
				'isUser'	 => $isUser,
				
				'secuOpts'	 => $secuOpt,
				'passOpts'	 => $passOpt,
				'maxRetry'	 => $maxRetry,
				'minChar'	 => $minChar,
				'passCycle'  => $passRecycle,
				
				'isTeller'	 => $isTeller,
				
				'finswitch'  => $finswitch,
				'onusCode'   => $onusCode,
				'bnkCode'	 => $bnkCode,
				'bnkMnem'	 => $bnkMnem,
				'coreencrypt'=> $coreencrypt,
				'superUser'	 => $isSuper
			);
			
			//check if super user
			if ($grpseqno === '0') {
				$isSuper = TRUE;
			}
			
			$_SESSION['userData' . $sessionID] = $sessionData;
			//print_r($sessionData);
			//$session->set_userdata($sessionData);
			$topRight 	= '<span><a href="#" id="logout">Logout</a></span>';
			
			switch ($this->inst['theme']) {
				case 1:
					$currentDT = '<strong>Current Date: </strong>'.date('l, F j, Y');
					
					$header = '<tr>
							<td id="instName" rowspan="2" style="vertical-align:middle !important">'. $this->inst['bankName'] .'</td>
							<td id="branch"><strong>Branch: </strong>'. $branchName .'</td>
							<td id="userName"><strong>Username: </strong>'. $userName .'</td>
							<td id="topRight">'. $topRight .'</td>
						</tr>
						<tr>
							<td id="currentDT">'. $currentDT .'</td>
							<td id="lastLogin">'. $lastLogin .'</td>
							<td></td>
							<td></td>
						</tr>';
					
					$floatMenu = $core->getFloatingMenu($userAllows, $grpseqno, $userName, $userID);
			
					break;
				case 2:
					$header = '<tr>
							<td id="instName" rowspan="2" style="vertical-align:middle !important">'. $this->inst['bankName'] .'</td>
							<td colspan="2">
								'. $core->getFloatingMenux($userAllows, $grpseqno, $userName, $userID) .'     
							</td>
							<td id="topRight">'. $topRight .'</td>
						</tr>';
						
					$floatMenu = '';
					break;
			}
			
			echo json_encode(array(
				'success' 	=> TRUE,
				//'userID' 	=> $userID,
				'isSuper'	=> $isSuper,
				'header'	=> $core->compressOutput($header),
				//'userName' 	=> $userName,
				//'instName' 	=> $instName,
				//'address' 	=> $address,
				//'branch' 	=> $branchName,
				//'currentDT'	=> date('l, F j, Y'),
				//'lastLogin' => $lastLogin,
				'div' => $floatMenu,
				'encryption' => $coreencrypt,
				'sessionExp'=> $sessionExp,
				'isTeller' => $isTeller//,
				//'hash' => $row['allows']
			));
			
		} else {
			
			$isReset = in_array($errNo, array(
				'90', //User password was reset
				'5' //user password expired
			));
			
			$isNeedDefine = in_array($errNo, array(
				'88', //Password not yet defined!
				'75' //User is new.
			));
			
			//save user ID to cache
			if ($isReset === TRUE) { //User password was reset
				$_SESSION['userIDx'] = $userID;
				$_SESSION['minChar'] = $minChar;
				$_SESSION['sysPwd'] = $pw;
				//$this->cache->save($this->core->getSessionID() .'userIDx', $userID, CACHE_TTL);
			} 
			
			if ($isNeedDefine === TRUE) {
				$_SESSION['userID'] = $userID;
				$_SESSION['minChar'] = $minChar;
				$_SESSION['sysPwd'] = $pw;
				//$this->cache->save($this->core->getSessionID() .'userID', $userID, CACHE_TTL);
			}
			//end
			
			echo json_encode(array(
				'success' => FALSE,
				'message' => $errMsg,
				'isDefine' => $isNeedDefine,
				'isReset' => $isReset,
				'errNo' => $errNo/*,
				'allows' => $row['allows']*/
			));
		}
	}
}
/* End of file login.php */
/* Location: ./application/controllers/login.php */