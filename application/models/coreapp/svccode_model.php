<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SvcCode_model extends CI_Model {
	//private $db, $security;
	
	function SvcCode_model()
	{
		$this->db = $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		$this->security->_initDb($this->db);
	}
	
	//charge type DFEE or SFEE
	function getServiceCodeList()
	{
		$query = "CALL sp_gettservicecodelist(?)";
		return $this->db->query($query, func_get_args());
	}
	//trxcode, desc, mnemonic, trxtype, brseqno, ipAddress, workstation, userAudit, sessionID
	function insertServiceCode()
	{
		$query = "CALL sp_insertservicecode(?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//trxcode, desc, mnemonic, trxtype, brseqno, ipAddress, workstation, userAudit, sessionID
	function updateServiceCode()
	{
		$query = "CALL sp_updateservicecode(?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//trxcode, brseqno, ipAddress, workstation, userAudit, sessionID
	function deleteServiceCode()
	{
		$query = "CALL sp_deleteservicecode(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
}