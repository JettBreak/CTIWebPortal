<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POS_model extends CI_Model {
	private $db, $security;
	
	function POS_model()
	{
		$this->db = $this->load->database(DB2, TRUE);
		$this->security = $this->coresecurity;
		//sets the current database
		$this->security->_initDb($this->db);
	}
	//termType
	function getTerminalLocations()
	{
		$query = "SELECT a.loccode, b.location, b.brcode FROM termlist a LEFT JOIN location b ON (a.loccode = b.loccode) WHERE a.termtype = ? GROUP BY loccode ORDER BY b.brcode";
		return $this->db->query($query, func_get_args());
	}
	function getAllLocations()
	{
		$query = "CALL sp_getalllocations()";	
		return $this->db->query($query);
	}
	//$branchCode, $loccode, $status
	function getPOSList()
	{	
		$query = "CALL sp_getposlist(?,?,?)";	
		return $this->db->query($query, func_get_args());
	}
	
	function getPOSStatus()
	{				
		$query = "CALL sp_getposstatus";
		return $this->db->query($query);
	}
	//$terminalCode
	function getPOSJournal()
	{		
		$query = "CALL sp_getterminaljournal('POS',?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getPOSTransactionHistory()
	{		
		$query = "CALL sp_getpostranhistory(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getMessageTypes()
	{	
		$query = "CALL sp_getmessagetypelist";	
		return $this->db->query($query);
	}
	
	function getTransactionList()
	{	
		$query = "CALL sp_gettransactionlist";
		return $this->db->query($query);
	}
	
	function getVoidCodeList()
	{	
		$query = "CALL sp_getvoidcodelist";
		return $this->db->query($query);
	}
	
	//Maintenance Form 
	
	function getPOSLanguage()
	{
		$query = "CALL sp_getposlanguage";
		return $this->db->query($query);
	}
	//$branchCode
	function getLocations()
	{
		$query = "CALL sp_getlocations(?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode
	function getPOSInfo()
	{
		$query = "CALL sp_getposinfo(?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode, $termID, $luno, $locCode, $desc, $progCode, $xml1
	function insertPOSTerminal()
	{
		$query = "CALL sp_insertposterminal(?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode, $termID, $luno, $locCode, $desc, $progCode, $xml1
	function updatePOSTerminal()
	{
		$query = "CALL sp_updateposterminal(?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode
	function deletePOSTerminal()
	{
		$query = "CALL sp_deleteposterminal(?)";
		return $this->db->query($query, func_get_args());
	}

	//$termCode
	function getPOSDStatus()
	{
		$query = "CALL coreapp_fusion.getcodelistapp('TERMSTATX')";
		return $this->db->query($query, func_get_args());
	}

	//$termCode
	function insertPOSDTerminal()
	{
		$query = "CALL coreapp_fusion.sp_insertPOSDTerminal(?,?,?,?,?,?,?,?,?,?,@errno,@errmsg)";
		$res1 = $this->db->query($query, func_get_args());

		$res1->free_result();
		$res1->next_result();

		$query = "SELECT @errno as errno, @errmsg as errmsg";			
		$res2 = $this->db->query($query);
		$res2 = $res2->row_array();
		
		return $res2;
	}

	//$branchCode, $loccode, $status
	function getPOSDList()
	{	
		$query = "CALL coreapp_fusion.sp_getposdlist(?,?,?)";	
		return $this->db->query($query, func_get_args());
	}


	//$branchCode, $loccode, $status
	function getInstitutionList()
	{	
		$query = "SELECT instseqno, instid, instname FROM coreapp_fusion.institution;";	
		return $this->db->query($query, func_get_args());
	}

	//$termCode
	function insertPartnerInstitution()
	{
		$query = "CALL coreapp_fusion.sp_insertPartnerInstitution(?,?,?,?,?,?,@errno,@errmsg)";

		$res1 = $this->db->query($query, func_get_args());
		

		$query = "SELECT @errno as errno, @errmsg as errmsg";			
		$res2 = $this->db->query($query);
		$res2 = $res2->row_array();
		
		return $res2;
	}

	//$termCode
	function updatePartnerInstitution()
	{
		$query = "CALL coreapp_fusion.sp_updatePartnerInstitution(?,?,?,?,?,?,@errno,@errmsg)";

		$res1 = $this->db->query($query, func_get_args());
		

		$query = "SELECT @errno as errno, @errmsg as errmsg";			
		$res2 = $this->db->query($query);
		$res2 = $res2->row_array();
		
		return $res2;
	}

	//$branchCode, $loccode, $status
	function getInstitutionInfo()
	{	
		$query = "SELECT instseqno, instid, instname, insttype, contact, telno, email  FROM coreapp_fusion.institution WHERE instseqno = ?;";	
		$res1 = $this->db->query($query, func_get_args());
		$res1 = $res1->row_array();

		return $res1;
	}

	//$branchCode, $loccode, $status
	function getOutletInfo()
	{	
		$query = "SELECT outletseqno, outletid, outletname, instseqno AS instid FROM coreapp_fusion.outletxxx WHERE outletseqno = ?;";	
		$res1 = $this->db->query($query, func_get_args());
		$res1 = $res1->row_array();

		return $res1;
	}

	//$termCode
	function insertOutlet()
	{
		$query = "CALL coreapp_fusion.sp_insertOutlet(?,?,?,@errno,@errmsg)";

		$res1 = $this->db->query($query, func_get_args());
		

		$query = "SELECT @errno as errno, @errmsg as errmsg";			
		$res2 = $this->db->query($query);
		$res2 = $res2->row_array();
		
		return $res2;
	}

	//$termCode
	function updateOutlet()
	{
		$query = "CALL coreapp_fusion.sp_updateOutlet(?,?,?,@errno,@errmsg)";

		$res1 = $this->db->query($query, func_get_args());

		$query = "SELECT @errno as errno, @errmsg as errmsg";			
		$res2 = $this->db->query($query);
		$res2 = $res2->row_array();
		
		return $res2;
	}

	//$branchCode, $loccode, $status
	function getPOSDTerminalInfo()
	{	
		$query = "CALL coreapp_fusion.sp_getposdterminalinfo(?)";	
		$res1 = $this->db->query($query, func_get_args());

		return $res1;
	}

	//$termCode
	function getPOSDInfo()
	{
		$query = "CALL sp_getposdinfo(?)";
		return $this->db->query($query, func_get_args());
	}

	//$termCode
	function deleteInstitution()
	{
		$query = "CALL coreapp_fusion.sp_deleteinstitution(?)";
		return $this->db->query($query, func_get_args());
	}

	//$termCode
	function deleteOutlet()
	{
		$query = "CALL coreapp_fusion.sp_deleteoutlet(?)";
		return $this->db->query($query, func_get_args());
	}

	//$termCode
	function checkInstitution()
	{
		$query = "SELECT xml FROM coresys_fusion.termlist WHERE termtype IN('ATM', 'POS')";
		return $this->db->query($query);
	}

	//$termCode
	function checkOutlet()
	{
		$query = "SELECT COUNT(instseqno) AS count FROM coreapp_fusion.outletxxx WHERE instseqno = ?";
		return $this->db->query($query, func_get_args());
	}
}