<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
	//private $db, $security;
	
	function User_model()
	{
		$this->db = $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		$this->security->_initDb($this->db);
	}
	//xml1
	function createSuperUser()
	{
		$query = "CALL sp_createsuperuser(?)";
		return $this->db->query($query, func_get_args());		
	}
	//$userAudit, $sessionID
	function checkLogin()
	{
		$this->db->trans_begin();
		
		$query = "CALL sp_checklogin(?,".APPSEQNO.",?,@v_err,@v_msg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @v_err as errno, @v_msg as errmsg";			
		$result = $this->db->query($query);
		
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return $result;
		}
	}
	
	//$userID, $userPW, $sessionID, $workstation, $ip
	function superUserLogIn()
	{
		$query = "CALL sp_superuserlogin(?,?,?,?,?,".APPSEQNO.")";
		return $this->db->query($query, func_get_args());
	}
	//$userID, $userPW, $sessionID, $workstation, $ip
	function userLogIn()
	{
		$query = "CALL sp_userlogin(?,?,?,?,?,".APPSEQNO.")";
		return $this->db->query($query, func_get_args());
	}	
	//$userID, grpseqno
	function userLogOut()
	{
		$query = "CALL sp_userLogout(?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//$userID, $password, $brseqno
	function userOverride()
	{
		$query = "CALL sp_userOverride(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$userID, $password, $isReset, $secretQ, $secretA, $isChangeSQ, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID
	function setUserPass()
	{	
		$query = "CALL sp_setuserpass(?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?,?,?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$userID, $userAudit, $sessionID
	function resetWebUserPass()
	{	
		$query = "CALL sp_resetwebuserpassword(?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$userID, $password, $question, $answer
	function setInitUserPass()
	{
		$query = "CALL sp_setinituserpass(?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$userID
	function getUserQuestion()
	{
		$query = "CALL sp_getuserquestion(?)";
		return $this->db->query($query, func_get_args());
	}
	//$userID, $password, $question, $answer
	function setForgotPass()
	{
		$query = "CALL sp_setforgotpass(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//user templates
	//$grpseqno
	function getUserTemplates()
	{
		$query = "CALL sp_getusertemplates(".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//$tmseqno
	function getUserTempAllows()
	{
		$query = "CALL sp_getusertempallows(?,".APPSEQNO.")";
		return $this->db->query($query, func_get_args());
	}
	//$grpseqno
	function getWebUserGroup()
	{
		$query = "CALL sp_getwebusergroup(?)";
		return $this->db->query($query, func_get_args());
	}
	//$grpseqno
	function getUserGroupInfo()
	{
		$query = "CALL sp_getusergroupinfo(?)";
		return $this->db->query($query, func_get_args());
	}
	//$grpseqno, $desc, $allows, $userAudit, $override, $workstation, $sessionID
	function insertUserTemplate()
	{
		$query = "CALL sp_insertusertemplate(?,".APPSEQNO.",?,?,?,?,?,?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$tmseqno, $grpseqno, $desc, $allows, $userAudit, $override, $workstation, $change, $sessionID
	function updateUserTemplate()
	{
		$query = "CALL sp_updateusertemplate(?,?,".APPSEQNO.",?,?,?,?,?,?,?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//tmseqno, brseqno, ipAddress, workstation, userID, override, sessionID
	function deleteUserTemplate()
	{
		$query = "CALL sp_deleteusertemplate(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$tmseqno, $userAudit, $sessionID
	function updateAllUserTemplates()
	{
		$query = "CALL sp_updateallusertemplate(".APPSEQNO.",?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	function getLastInsertID()
	{
		return $this->db->insert_id();
	}
	//end
	
	//user list
	//$grpseqno, $brseqno
	function getWebUserList()
	{
		//$query = "SELECT * FROM userlist WHERE brseqno = 1 AND grpseqno = 2";
		$query = "CALL sp_getwebuserlist(?,?,".APPSEQNO.")";
		return $this->db->query($query, func_get_args());
	}
	
	function getDepartments()
	{
		$query = "CALL sp_getcodelist('DEPARTMENT')";
		return $this->db->query($query);
	}
	//$tmseqno, $grpseqno, $userID, $brseqno, $userName, $allows, $xml1, $xml2, $workstation, $userAudit, $sessionID
	function insertWebUser()
	{
		$query = "CALL sp_insertwebuser(?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$tmseqno, $userID, $brseqno, $userName, $allows, $xml1, $workstation, $userAudit, $sessionID
	function updateWebUser()
	{
		$query = "CALL sp_updatewebuser(?,?,?,?,?,?,?,?,".APPSEQNO.",?,0)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$userID, $userAudit, $sessionID
	function deleteWebUser()
	{
		$query = "CALL sp_deletewebuser (?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$grpseqno, $userID, $userAudit, $sessionID
	function resetWebUserSettings()
	{
		$query = "CALL sp_resetwebusersettings(?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$userID, $userAudit, $sessionID
	function enableWebUser()
	{
		$query = "CALL sp_enablewebuser(?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$userID, $userAudit, $sessionID
	function disableWebUser()
	{
		$query = "CALL sp_disablewebuser(?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	/*function getAccountTypes()
	{
		$query = "CALL sp_getaccounttype()";
		return $this->db->query($query);
	}*/
	
	function getWebUserGroupList()
	{
		$query = "CALL sp_getwebusergrouplist()";
		return $this->db->query($query);
	}
	
	//user group
	//grpseqno, desc, userAudit, override, workstation, xml1
	function insertWebUserGroup()
	{
		//$this->db->trans_begin();
		
		$query = "CALL sp_insertusergroup(?,?,?,?,?,?, @errno)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @errno as errno";			
		return $result = $this->db->query($query);
		
		/*if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return $result;
		}*/
	}
	
	function updateWebUserGroup()
	{
		//$this->db->trans_begin();
		
		$query = "CALL sp_updateusergroup(?,?,?,?,?,?, @errno)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @errno as errno";			
		return $result = $this->db->query($query);
		
		/*if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return $result;
		}*/
	}
	
	function deleteWebUserGroup()
	{
		//$this->db->trans_begin();
		
		$query = "CALL sp_deleteusergroup(?, @errno, @errmsg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @errno as errno, @errmsg as errmsg";			
		return $result = $this->db->query($query);
		
		/*if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return $result;
		}*/
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * get ONUSCODE
	 *
	 * @access	public
	 * @return	onuscode
	 */
	function getONUSCODE()
	{
		
		$query = "SELECT varname, vardata AS 'onusCode' 
			FROM configxx 
			WHERE varname = 'ONUSCODE'";
		return $this->db->query($query, func_get_args());
	}
	
	// --------------------------------------------------------------------

	/**
	 * get FINSWITCH
	 *
	 * @access	public
	 * @return	finswitch
	 */
	function getFINSWITCH()
	{
		
		$query = "SELECT varname, vardata AS 'finswitch' 
			FROM configxx 
			WHERE varname = 'FINSWITCH'";
		return $this->db->query($query, func_get_args());
	}
	
	// --------------------------------------------------------------------

	/**
	 * get BANKCODE
	 *
	 * @access	public
	 * @return	bankcode
	 */
	function getBANKCODE()
	{
		
		$query = "SELECT clientcode 
			FROM clientxx LIMIT 1";
		return $this->db->query($query, func_get_args());
	}
	
	//end
	
	// --------------------------------------------------------------------

	/**
	 * get BANK MNEMONIC
	 *
	 * @access	public
	 * @return	bankmnem
	 */
	function getBANKMNEM()
	{
		
		$query = "SELECT varname, vardata AS 'bankmnem'
			FROM configxx 
			WHERE varname='BANKCODE' LIMIT 1";
		return $this->db->query($query, func_get_args());
	}
	
	//end 
	
	// --------------------------------------------------------------------

	/**
	 * get BANK MNEMONIC
	 *
	 * @access	public
	 * @return	bankmnem
	 */
	function getUserPassCycleCount()
	{
		
		$query = "SELECT a.xml2 AS userXML, b.xml1 AS grpXML FROM userlist a, usergrpx b WHERE a.grpseqno = b.grpseqno AND a.userid = ?;";
		return $this->db->query($query, func_get_args());
	}
	
	//end

	//$msgType, $brseqno, $sysVCode, $prKey, $prKey, $userID, $workstation, $xml
	function insertAuditLogclixx()
	{
		$query = "CALL sp_insertlogclixx(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}

	//status,prseqno,workstation,useraudit,sessionid
	function getCurrentUserInfo()
	{
		$query = "CALL sp_getcurrentuserinfo(?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//user templates
	//$grpseqno
	function getUserTemplateInfo()
	{
		$query = "SELECT description FROM coreapp_fusion.tmmaster WHERE tmseqno = ?";
		return $this->db->query($query, func_get_args());
	}
}
/* End of file user_model.php */
/* Location: ./application/models/coreapp/user_model.php */
