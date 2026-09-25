<?php
require_once(dirname(__FILE__).'/yiiflex.php');

class Users
{
	function __construct()
	{
		$this->user = new User;
		$this->core = new Core;
		$this->xml = new ShortXML;
		$this->model = new UsersModel;
	}
	
	function userLogIn()
	{
		$xml = $this->xml;
		
		$params = func_get_args();
		$userID = $params[0];
		$userPW = $this->core->encrypt($userID, $params[1]);
		
		$sessionID = $this->user->getSessionID();
		$workstation = $this->user->getWorkstation();
		$ipAddress = $this->user->getIPAddress();
		
		$row = $this->model->userLogIn(
			$userID,
			$userPW,
			$sessionID,
			$workstation,
			$ipAddress
		);
		
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
		$lastLogin 	= $row['lastlogin'] ? $this->core->formatDate('l, F j, Y g:i A', $row['lastlogin']) : 'Never';
		$userAllows = $row['allows'];
		$isHead		= $row['headofc'] === 'Y' ? TRUE : FALSE;
		$isMon		= $xml->getValue('ISMON') === 'Y' ? TRUE : FALSE;
		$isRep		= $xml->getValue('ISREP') === 'Y' ? TRUE : FALSE;
		$isUser		= $xml->getValue('ISUSER') === 'Y' ? TRUE : FALSE;
		$errNo		= $row['errno'];
		$errMsg		= $row['errmsg'];
		
		$secuOpt = $xml->getValue('SECUOPTION');
		$passOpt = $xml->getValue('PASSOPTION');
		$maxRetry = $xml->getValue('MAXRETRY');
		$minChar = $xml->getValue('MINCHAR');
		
		$isTeller = $xml->getValue('ISTELLER') === 'Y' ? TRUE : FALSE;
		
		$sessionExp = $xml->getValue('SESSIONEXP');
		
		if ( in_array($errNo, array('2', '1')) ) {
			$userData = array(
				'sessionExp' => $sessionExp,
				'userID'  	 => $userID,
				'userPW'	 => $userPW,
				'userName'   => $userName,
				'instName'	 => $instName,
				'address'	 => $address,
				'grpseqno'	 => $grpseqno,
				'brseqno'	 => $branchID,
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
				
				'isTeller'	 => $isTeller
			);
			
			$this->user->setUserData($userData);
			
			$response['success'] = TRUE;
		} else {
			$response['success'] = FALSE;
			$response['message'] = $errMsg;
		}
		
		return $response;
	}
	
	function userLogOut()
	{
		$userID = $this->user->getUserID();
		$grpseqno = $this->user->getUserGroup();
		
		$this->model->userLogOut($userID, $grpseqno);
		
		$response['success'] = TRUE;
		
		return $response;
	}
}
?>