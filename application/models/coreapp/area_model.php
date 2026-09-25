<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Area_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		//sets the current database
		$this->security->_initDb($this->db);
	}
	
	function getTerminalAreas($brcodes)
	{
		if ($brcodes) {
			$query = "SELECT a.regioncode, a.areaname, b.brcode FROM areacode a LEFT JOIN branches b ON (a.regioncode = b.regioncode) WHERE b.brcode IN (".$brcodes.") ORDER BY b.brcode";
			return $this->db->query($query);
		} else {
			return false;
		}
	}
	
	//areacode int ,areaname CHAR(50) 
	function insertArea()
	{
		$query = "CALL sp_insertarea(?,?,?,?,?,?,". APPSEQNO .",?)";	
		return $this->security->validateQuery($query, func_get_args());
	}
	
	// v_oldcode , v_areacode , v_areaname , v_brseqno , v_ipaddress , v_wkstn , v_useraudit , v_appseqno , v_sessionid
	function updateArea()
	{
		$query = "CALL sp_updatearea(?,?,?,?,?,?,?,". APPSEQNO .",?)";	
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//areacode
	function deleteArea()
	{
		$query = "CALL sp_deletearea(?,?,?,?,?,". APPSEQNO .",?)";	
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//brseqno
	function getAreaByBranch()
	{
		$query = "CALL sp_getareabybranch(?)";	
		return $this->db->query($query, func_get_args());
	}
	
	function getAreaList()
	{
		$query = "CALL sp_getarealist()";	
		return $this->db->query($query, func_get_args());
	}
	
	//regionCode
	function getBranchListByArea()
	{
		$query = "CALL sp_getbranchlistbyarea(?)";
		return $this->db->query($query, func_get_args());
	}
}
