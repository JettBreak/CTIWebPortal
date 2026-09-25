<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class New_ATM_model extends CI_Model {
	private $db, $security;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database(DB2, TRUE);
		$this->security = $this->coresecurity;
		//sets the current database
		$this->security->_initDb($this->db);
	}
	
	function getATMList($brcode)
	{
		$filter = '';
		$params = array();

		if ($brcode != 0) {
			$filter .= ' AND d.brcode = ?';
			array_push($params, $brcode);
		}

		$query = 'SELECT a.termcode, a.description, b.status,
			c.codevalue AS statdesc
			FROM termlist a
			LEFT JOIN termonln b ON (b.termcode = a.termcode)
			LEFT JOIN codelist c ON (b.status = c.codeseqno AND c.codetype = "TERMSTAT")
			LEFT JOIN location d ON (a.loccode = d.loccode)
			WHERE a.termtype = "ATM" ' . $filter . ' GROUP BY 1';

		return $this->db->query($query, $params);
	}
}
