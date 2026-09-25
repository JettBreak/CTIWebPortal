<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_model extends CI_Model {
	private $db, $security;
	
	function Customer_model()
	{
		$this->db = $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		
		//sets the current database
		$this->security->_initDb($this->db);
	}
	
	function getCellBIN()
	{		
		$query = "CALL sp_getCodeList('CELLBIN')";
		return $this->db->query($query);
	}
	//$id, $blobPic, $userAudit, $sessionID
	function getCustomerPicture()
	{		
		$query = "CALL sp_getcustomerpicture(?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifseqno, $userAudit, $sessionID
	function getCustomerCardLink()
	{
		$query = "CALL sp_getcustomercardlink(?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifseqno
	function getCustomerById()
	{		
		$query = "CALL sp_getcifnamebycifseqno(?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifseqno, $fName, $lName, $mName, $userID, $sessionID
	function searchCustomer()
	{
		$query = "CALL sp_getcustomerlist(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function insertCustomer()
	{
		$query = "CALL sp_insertcustomer(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override,
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $cifseqno, $sessionID
	function updateCustomer()
	{
		$query = "CALL sp_updatecustomer(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";	
		return $this->security->validateQuery($query, func_get_args());
	}
	//$blobkey, $desc, $blobData, $userID, $sessionID
	function insertCustPic()
	{	
		$query = "CALL sp_insertcustomerpicture(?,2,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$blobPic, $cifseqno, $blobData, $userAudit, $sessionID
	function updateCustPic()
	{
		$query = "CALL sp_updatecustomerpicture(?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	function getNextBlobPic()
	{
		$query = "SHOW TABLE STATUS LIKE 'blobdata'";
		
		$result = $this->db->query($query);
		$row 	= $result->row_array();
		
		return $row['Auto_increment'];
	}
	
	function getNextCustomerID()
	{
		$query = "SHOW TABLE STATUS LIKE 'customer'";
		
		$result = $this->db->query($query);
		$row 	= $result->row_array();
		
		return $row['Auto_increment'];
	}
	//$city, $province, $zipcode
	function getZones()
	{
		$query = "CALL sp_getzone(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	//cifseqno, useraudit, override, ipAddress, workstation, brseqno, sessionID
	function deleteCustomer()
	{
		$query = "CALL sp_deletecustomer(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//$msgType, $trxCode, $brseqno, $sysVCode, $userID, $userID, $workstation, $xml
	function insertLogclixx()
	{
		$query = "CALL sp_insertlogclixx(?,?,?,?,0,'CUST','',?,'','','','','','','',?,'','WEB','WEB',?,?)";
		return $this->db->query($query, func_get_args());
	}
}
/* End of file customer_model.php */
/* Location: ./application/models/coreapp/customer_model.php */