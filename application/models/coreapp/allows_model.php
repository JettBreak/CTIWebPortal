<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Allows_model extends CI_Model {
	//private $db, $security;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		$this->security->_initDb($this->db);
	}
	
	function getProductTypes()
	{
		$query = "CALL sp_getproductlist()";
		return $this->db->query($query);
	}
	//prtype
	function getAllowsSetup()
	{
		$query = "CALL sp_getallowssetup(?)";
		return $this->db->query($query, func_get_args());
	}
	//prtype, trxcode
	function getTransactionAllows()
	{
		$query = "CALL sp_gettransactionallows(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//prType, bitNo, trxcodex, brseqno, ipAddress, workstation, userID, sessionID
	function insertAllowsSetup()
	{
		$query = "CALL sp_insertallowssetup(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prType, bitNo, trxcodex, oldtrxcodex, brseqno, ipAddress, workstation, userID, sessionID
	function updateAllowsSetup()
	{
		$query = "CALL sp_updateallowssetup(?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prType, trxcode, brseqno, ipAddress, workstation, userID, sessionID
	function deleteAllowsSetup()
	{
		$query = "CALL sp_deleteallowssetup(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//grouptype
	function getAcctTypes()
	{
		$query = "SELECT accttype, description, grouptype FROM accttype WHERE grouptype IN ('ACCT', 'CARD', 'CELL')";
		return $this->db->query($query, func_get_args());
	}
	//prtype
	function getDefaultTranAllows()
	{
		$query = "CALL sp_getdefaulttranallows(?)";
		return $this->db->query($query, func_get_args());
	}
	//prtype, accttype, prtype
	function getDefaultAllows()
	{
		$query = "CALL sp_getdefaultallows(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//prtype, prtype, hex, grayed(hex), brseqno, ipAddress, workstation, userAudit, sessionID
	function setAllowsDefault()
	{
		$query = "CALL sp_setallowsdefault(?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prtype, accttype, hex, brseqno, ipAddress, workstation, userAudit, sessionID
	function updateAllToAllowsDefault()
	{
		$query = "CALL sp_updatealltoallowsdefault(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
}
