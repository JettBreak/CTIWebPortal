<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Misc_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database(DB2, TRUE);
		$this->security = $this->coresecurity;
		//sets the current database
		$this->security->_initDb($this->db);
	}
	
	function getLocationTypes()
	{	
		$query = "CALL sp_getcodelist('INSTTYPE')";	
		return $this->db->query($query);
	}
	
	//location char(50) ,brcode CHAR(16), locsite INT
	function insertLocation()
	{
		$query = "CALL sp_insertlocation(?,?,?)";	
		return $this->db->query($query, func_get_args());
	}
	
	//locCode, location, branchCode, locSite, isEdited
	function updateLocation()
	{
		$query = "CALL sp_updatelocation(?,?,?,?,?)";	
		return $this->db->query($query, func_get_args());
	}
	
	//locCode
	function deleteLocation()
	{
		$query = "CALL sp_deletelocation(?)";	
		return $this->db->query($query, func_get_args());
	}
	
	//brCode
	function getLocations()
	{
		$query = "CALL sp_getlocations(?)";	
		return $this->db->query($query, func_get_args());
	}
	
	function getAllLocations()
	{
		$query = "CALL sp_getalllocations()";	
		return $this->db->query($query);
	}
	
	//service charges
	function getFeeList()
	{
		$query = "CALL sp_getfeelist(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//brcode
	function getTermListForFeeList()
	{
		$query = "SELECT a.termcode, a.description FROM termlist a ".
		"LEFT JOIN location b ON ( a.loccode = b.loccode ) WHERE a.termtype IN ('ATM','POS') ".
		"ORDER BY a.termtype, a.termcode";
		return $this->db->query($query, func_get_args());
	}
	
	function getServiceTypes()
	{
		$query = "CALL sp_getcodelist('SERVTYPE')";
		return $this->db->query($query);
	}
	
	function getFeeTypes()
	{
		$query = "CALL sp_getcodelist('FEETYPE')";
		return $this->db->query($query);
	}
	
	function getCardholders()
	{
		$query = "CALL sp_getcodelist('AUTHNAME')";
		return $this->db->query($query);
	}
	
	function getNetworkTypes()
	{
		$query = "CALL sp_getcodelist('NETWRKTYPE')";
		return $this->db->query($query);
	}
	
	function getTerminalTypeList()
	{
		$query = "CALL sp_getterminaltypelist()";
		return $this->db->query($query);
	}
	
	function getTransactionList()
	{
		$query = "CALL sp_gettransactionlistservicefee()";
		return $this->db->query($query);
	}
	//termcode, brseqnox, servtype, checkib, authname, trxcodex, trxcode2, feetype, feeval, minrange, maxrange, chargetype, description, isdisabled, nettype, brseqno, ipaddress, workstation, userID, override
	function insertFee()
	{
		$query = "CALL sp_insertFee(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	//feeseqno, termcode, brseqnox, servtype, checkib, authname, feetype, feeval, minrange, maxrange, description, isdisabled, nettype, brseqno, ipaddress, workstation, userID, override
	function updateFee()
	{
		$query = "CALL sp_updateFee(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	//feeseqno, brseqno, ipAddress, workstation, userID, override
	function deleteFee()
	{
		$query = "CALL sp_deleteFee(?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//end
	
	function smsNotification()
	{
		$query = "CALL sp_smsnotification(?,?,?,@v_errno)";
		$this->db->query($query, func_get_args());
		$query = "SELECT @v_errno AS errno";
		return $this->db->query($query);
	}
	
	//status, msgtype, trxcode, prtype, prkey, xml1, useraudit, override, wkstn
	function insertDefaultPINGenBatch()
	{
		$query = "CALL sp_insertDefaultPINGenBatch(?,?,?,?,?,?,?,?,?,@batchseqno)";
		
		$this->db->query($query, func_get_args());
		$query = "SELECT @batchseqno AS batchseqno";
		return $this->db->query($query);
	}
	
	function getBatchStatusList()
	{
		$query = "SELECT batchseqno, status, msgtype FROM batchxxx WHERE trxcode = 938888";
		
		return $this->db->query($query);
	}
	
	function getBatchStatus()
	{
		$query = "CALL sp_getBatchStatus(?)";
		
		return $this->db->query($query, func_get_args());
	}	

	//
	function updateEventStatus()
	{
		$query = "CALL sp_updatewebeventstat(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
}
