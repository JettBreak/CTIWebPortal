<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BillsPay_model extends CI_Model {
	private $db, $security;
	
	function __construct()
	{
		$this->db = $this->load->database(DB2, TRUE);
	}
	
	//billid, subno
	function validateSubscriber()
	{
		$this->db->trans_begin();
		
		$query = "CALL sp_validatesubscriber(?,?,@seqno,@errno)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @seqno as seqno, @errno as errno";			
		$result = $this->db->query($query);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return $result;
		}
	}
	
	//seqno
	function getSubNoValidation()
	{
		$query = "CALL sp_getsubnovalidation(?)";
		return $this->db->query($query, func_get_args());
	}
}