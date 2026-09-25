<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ipay_model extends CI_Model {
	public $db, $security;

	function __construct()
	{
		parent::__construct();
		$this->db 		= $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		
		//sets the current database
		$this->security->_initDb($this->db);
	}

	function getFitListXX()
	{
		$query = "CALL coresys_fusion.getfitlistx()";
		return $this->db->query($query);
	}

	function getIPayCardType()
	{
		$query = "CALL sp_getipaycardtype(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getIPAYHost()
	{
		$query = "CALL coreapp_fusion.sp_getconfigvalue('COREASHOST', @host)";
		$this->db->query($query, func_get_args());

		$query = "SELECT @host as host";			
		return $this->db->query($query);
	}

	function getIPAYPort()
	{
		$query = "CALL coreapp_fusion.sp_getconfigvalue('IPAYPORT', @port)";
		$this->db->query($query, func_get_args());

		$query = "SELECT @port as port";			
		return $this->db->query($query);
	}

	function getIPayCardInfo()
	{
		$query = "CALL coreapp_fusion.sp_getipaycardinfo()";
		return $this->db->query($query);
	}

	function getIPayCardParameters()
	{
		$query = "CALL coreapp_fusion.sp_getinstapayparams(
      @prseqno, @prkey, @prtype, @cardtype, @brseqno,@brcode, @merchantid, @mallid, @userid, @userpass, @errorno
    )";

    $this->db->query($query, func_get_args());

    $query = "SELECT  @prseqno AS prseqno, @prkey AS prkey, @prtype AS prtype, 
			                @cardtype AS cardtype,
			                @brseqno AS brseqno, @brcode AS brcode,
			                @merchantid AS merchantid,
			                @mallid AS mallid,
			                @userid AS userid, @userpass AS userpass,
			                @errorno AS errorno";			

		return $this->db->query($query);
	}

	function insertIPayCard()
	{
		$query = "CALL coreapp_fusion.sp_insertipaycard(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}

	function insertConfigxx()
	{
		$query = "CALL coreapp_fusion.sp_setconfigvalue(?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}

	function getConfigxx()
	{
		$query = "CALL coreapp_fusion.sp_getconfigvalue(?,@description)";
		$this->db->query($query, func_get_args());

		$query = "SELECT @description as description";			
		return $this->db->query($query);
	}

	function insertAuditLogclixx()
	{
		$query = "CALL sp_insertlogclixx(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}

}
