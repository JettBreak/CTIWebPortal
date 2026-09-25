<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATMCmd_model extends CI_Model {
	private $db, $security;
	
	function ATMCmd_model()
	{
		$this->db 		= $this->load->database(DB2, TRUE);
		$this->security = $this->coresecurity;
		
		//sets the current database
		$this->security->_initDb($this->db);
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function terminalUp()
	{
		$query = "CALL sp_terminalup(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function terminalDown()
	{
		$query = "CALL sp_terminaldown(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function terminalLoad()
	{
		$query = "CALL sp_terminalload(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $terminalCode, $brseqno, $userAudit, $ipAddress, $workstation
	function terminalReset()
	{
		$query = "CALL sp_terminalreset(?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function getTerminalInformation()
	{
		$query = "CALL sp_getterminformation(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function getSupplyCounters()
	{
		$query = "CALL sp_getsupplycounters(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function syncDateTime()
	{
		$query = "CALL sp_syncdatetime(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function generateNewKey()
	{
		$query = "CALL sp_generatekey(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function tagTerminalForDeployment()
	{
		$query = "CALL sp_tagtermfordeployment(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno, $brseqno, $userAudit, $ipAddress, $workstation
	function tagTerminalUnderMaintenance()
	{
		$query = "CALL sp_tagtermundermaintenance(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
}
/* End of file atmcmd_model.php */
/* Location: ./application/models/coreapp/atmcmd_model.php */