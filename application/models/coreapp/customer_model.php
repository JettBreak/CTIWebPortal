<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_model extends CI_Model {
	public $db, $security;
	
	function __construct()
	{
		parent::__construct();
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
		$query = "CALL sp_getcustomercardlink(?,?,?,".APPSEQNO.",?)";
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
	function searchCustomerApproval()
	{
		$query = "CALL sp_editCustomerApproval(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifseqno, $fName, $lName, $mName, $userID, $sessionID
	function validateCustomer()
	{
		$query = "SELECT cifseqno, firstname, lastname, middlename FROM customer WHERE custkey = ?;";
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
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function insertCustomerApproval()
	{
		$query = "CALL sp_insertcustomerApproval(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function customerEditApproval()
	{
		$query = "CALL sp_customereditApproval(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function getCustomerForApproval()
	{
		$query = "SELECT cifseqno, custkey, firstname, lastname, middlename, suffix, prefix FROM custappr a
		LEFT JOIN userlist b ON (a.useraudit = b.userid) WHERE b.brseqno = ?";
		return $this->db->query($query, func_get_args());
	}
	
	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function getCustomerForApprovalHO()
	{
		$query = "SELECT cifseqno, custkey, firstname, lastname, middlename, suffix, prefix FROM custappr a
		LEFT JOIN userlist b ON (a.useraudit = b.userid)";
		return $this->db->query($query, func_get_args());
	}

	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function getCustomerEditForApprovalHO()
	{
		$query = "SELECT cifseqno, custkey, firstname, lastname, middlename, suffix, prefix FROM custeditappr a
		LEFT JOIN userlist b ON (a.useraudit = b.userid)";
		return $this->db->query($query);
	}

	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function getCustomerEditForApproval()
	{
		$query = "SELECT cifseqno, custkey, firstname, lastname, middlename, suffix, prefix FROM custeditappr  a
		LEFT JOIN userlist b ON (a.useraudit = b.userid) WHERE brseqno = ?";
		return $this->db->query($query, func_get_args());
	}

	//$custkey, $useraudit, $override, $ipAddress, $wkstn, $sessionID
	function insertApprovedCustomer()
	{
		$query = "CALL sp_insertapprovedcustomer(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}

	//$custkey, $useraudit, $override, $ipAddress, $wkstn, $sessionID
	function approvedCustomerUpdate()
	{
		$query = "CALL sp_approvedcustomeredit(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$custkey, $useraudit, $override, $ipAddress, $wkstn, $sessionID
	function removeRejectedCustomer()
	{
		$query = "CALL sp_rejectforapprovalcustomer(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$custkey, $useraudit, $override, $ipAddress, $wkstn, $sessionID
	function removeRejectedCustomerEdit()
	{
		$query = "CALL sp_rejectforapprovalcustomeredit(?,?,?,?,?,".APPSEQNO.",?)";
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
	//$cifgrpseqno, $custkey, $ciftype, $prefix, $fname, $mname, $lname, $suffix, $uniqtype, $uniqval, $useraudit, $override, 
	//$ipAddress, $wkstn, $xml1, $xml2, $xml3, $xml4, $xml5, $xml6, $xml7, $xml8, $blobpic, $blobsgn, $brseqno, $sessionID
	function insertbatchcustomer()
	{
		$query = "CALL sp_insertbatchcustomer(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
}
/* End of file customer_model.php */
/* Location: ./application/models/coreapp/customer_model.php */
