<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Branch_model extends CI_Model {
	function Branch_model()
	{
		$this->db = $this->load->database(DB1, TRUE);
		 
	}
	
	function getArea()
	{	
		$query = "CALL sp_getarealist()";	
		return $this->db->query($query);
	}		
	//areacode int ,areaname CHAR(50) 
	function insertBranch()
	{
		$query = "CALL sp_insertbranch(?,?,?,?,?,?,?,?,?,?,?,?,". APPSEQNO .",?)";	
		return $this->db->query($query, func_get_args());
	}
	
	// v_oldcode , v_areacode , v_areaname , v_brseqno , v_ipaddress , v_wkstn , v_useraudit , v_appseqno , v_sessionid 
	function updateBranch()
	{
		$query = "CALL sp_updatebranch(?,?,?,?,?,?,?,?,?,?,?,?,?,". APPSEQNO .",?)";	
		return $this->db->query($query, func_get_args());
	}
	
	//  brseqno , user_brseqno , ipaddress ,wkstn , useraudit , appseqno , sessionid 
	function deleteBranch()
	{
		$query = "CALL sp_deletebranch(?,?,?,?,?,?,". APPSEQNO .",?)";	
		return $this->db->query($query, func_get_args());
	}
	
	//brCode
	function getBranchList()
	{
		$query = "CALL sp_getbranchlist()";	
		return $this->db->query($query, func_get_args());
	}
	
	//brCode
	function getBranchListRep()
	{
		$query = "CALL sp_getbranchlistRep(?)";	
		return $this->db->query($query, func_get_args());
	}
	
	//for service charge
	function getServiceCodeList()
	{
		$query = "CALL sp_getservicecodelist(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getBranchNameValue()
	{
		$query = "SELECT brname FROM branches WHERE brcode = ? LIMIT 1";
		return $this->db->query($query, func_get_args());
	}

	function getBranchCodeValue()
	{
		$query = "SELECT brcode FROM branches WHERE brseqno = ? LIMIT 1";
		return $this->db->query($query, func_get_args());
	}

	//brCode
	function getInstitutions()
	{
		$query = "CALL coreapp_fusion.getInstitutions()";	
		return $this->db->query($query, func_get_args());
	}
	//brCode
	function getOutlets()
	{
		$query = "CALL coreapp_fusion.getOutlets()";	
		return $this->db->query($query, func_get_args());
	}
	
	function getBranchDetails()
	{
		$query = "SELECT brname FROM coreapp_fusion.branches WHERE brseqno = ?";
		
		return $this->db->query($query, func_get_args());
	}
	
}