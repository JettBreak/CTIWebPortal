<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Host_model extends CI_Model {
	private $db, $security;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database(DB2, TRUE);
		$this->security = $this->coresecurity;
		//sets the current database
		$this->security->_initDb($this->db);
	}
	
	//nodeType, status
	function getNodeList()
	{
		$query = "CALL sp_getnodelist(?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getCHStats()
	{
		$query = "CALL sp_getcodelist('CHSTAT')";
		return $this->db->query($query);
	}
	//nodeName
	function getJournal()
	{
		$query = "CALL sp_getnodejournal(?)";
		return $this->db->query($query, func_get_args());
	}
	//nodeName
	function getNodeStoreAndForward()
	{
		$query = "CALL sp_getnodesafhistory(?)";
		return $this->db->query($query, func_get_args());
	}
	//nodeName
	function getNodeCmdHistory()
	{
		$query = "CALL sp_getnodecommands(?)";
		return $this->db->query($query, func_get_args());
	}
	//nodeName
	function getNodeTranHistory()
	{
		$query = "CALL sp_getnodetranhistory(?)";
		return $this->db->query($query, func_get_args());
	}
	//nodeName
	function getNodeMenu()
	{
		$query = "CALL sp_getnodemenu(?)";
		return $this->db->query($query, func_get_args());
	}
}
