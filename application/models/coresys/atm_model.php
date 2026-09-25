<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATM_model extends CI_Model {

	private $fileVersion = '1.10.00';
	private $db, $security;
	
	function __construct()
	{
		$this->db = $this->load->database(DB2, TRUE);
		$this->security = $this->coresecurity;
		//sets the current database
		$this->security->_initDb($this->db);

		$this->load->library('version');

		$file = basename(__DIR__) . '/' . basename(__FILE__);

		$verified = $this->version->validate($file, $this->fileVersion);

		if (!$verified) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Module is out of date. Please contact software administrator.'
			));
			exit();
		}
	}

	//termType
	function getTerminalLocations()
	{
		$query = "CALL sp_getUsedTerminalLocations(?)";
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
	
	function getVoidCodes()
	{
		$query = "CALL sp_getcodelist('SPVOIDCD')";
		return $this->db->query($query);
	}
	
	function getVoidCodeList()
	{	
		$query = "CALL sp_getvoidcodelist";
		return $this->db->query($query);
	}
	
	function getATMList($brcode, $loccode, $status)
	{
		//$query = "CALL sp_getatmList(?,?,?)";
		$filter = '';
		$params = array();

		if ($brcode != 0) {
			$filter .= ' AND d.brcode = ?';
			array_push($params, $brcode);
		}

		if ($loccode != 0) {
			$filter .= ' AND a.loccode = ?';
			array_push($params, $loccode);
		}

		if (intval($status) >= 0) {
			$filter .= ' AND b.status = ?';
			array_push($params, $status);
		}

		$query = 'SELECT a.termcode,a.description,a.termid,a.loccode,d.brcode,b.state, b.status,a.luno,a.installationtype,a.progcode,a.proglang,a.xml,
			IF(a.progcode_denomination=0,a.progcode,a.progcode_denomination) AS progcode_denomination,
			IF(a.proglang_denomination=0,a.proglang,a.proglang_denomination) AS proglang_denomination,
			c.codevalue AS statdesc,d.location,e.codevalue AS insttype,

			i.cardcapturebin, i.cashhandler, i.depositbin, i.receiptprinter, i.journalprinter, i.nightsafedepository,
			i.cassette1, i.cassette2, i.cassette3, i.cassette4, i.statementribbon, i.statementprinter, i.envelopedispenser,

			SUM(
			IF((SUBSTRING(k.parmdata,5,1))="1",  j.notesincas1 * (SUBSTRING(k.parmdata,7,12))/100,
			IF((SUBSTRING(k.parmdata,5,1))="2",  j.notesincas2 * (SUBSTRING(k.parmdata,7,12))/100,
			IF((SUBSTRING(k.parmdata,5,1))="3",  j.notesincas3 * (SUBSTRING(k.parmdata,7,12))/100,
			IF((SUBSTRING(k.parmdata,5,1))="4",  j.notesincas4 * (SUBSTRING(k.parmdata,7,12))/100, 0.0))))) as remainingcash
			FROM termlist a
			LEFT JOIN termonln b on (b.termcode=a.termcode)
			LEFT JOIN codelist c ON (b.status = c.codeseqno AND c.codetype="TERMSTAT")
			LEFT JOIN location d ON (d.loccode = a.loccode)
			LEFT JOIN codelist e ON (a.installationtype = e.codeseqno AND e.codetype="INSTTYPE")
			LEFT JOIN termsupx i ON (i.termcode=a.termcode)
			LEFT JOIN termctrs j ON (j.termcode=a.termcode)
			LEFT JOIN termparm k ON (k.tpcode=11 AND ((k.progcode=a.progcode AND a.progcode_denomination = 0) OR (k.progcode=a.progcode_denomination AND a.progcode_denomination > 0) )
			AND ((k.proglang=a.proglang AND a.proglang_denomination = 0) OR (k.proglang=a.proglang_denomination AND a.proglang_denomination > 0) ))
			WHERE a.termtype = "ATM" ' . $filter . ' GROUP BY 1';

		return $this->db->query($query, $params);
	}
	
	//termCode
	function getATMInfoByCode()
	{
		$query = 'SELECT a.termcode, a.description, a.termid, a.loccode, d.brcode, b.state, b.status, a.luno, a.installationtype, a.progcode, a.proglang, a.xml,
			IF (a.progcode_denomination = 0, a.progcode,a.progcode_denomination) AS progcode_denomination,
			IF (a.proglang_denomination = 0, a.proglang,a.proglang_denomination) AS proglang_denomination,
			c.codevalue AS statdesc, d.location, e.codevalue AS insttype,
			i.*,
			"Unknown" AS cmdstat,
			"Unknown" AS cmddesc,
			SUM(
			IF ((SUBSTRING(k.parmdata,5,1))="1", j.notesincas1 * (SUBSTRING(k.parmdata,7,12))/100,
			IF ((SUBSTRING(k.parmdata,5,1))="2", j.notesincas2 * (SUBSTRING(k.parmdata,7,12))/100,
			IF ((SUBSTRING(k.parmdata,5,1))="3", j.notesincas3 * (SUBSTRING(k.parmdata,7,12))/100,
			IF ((SUBSTRING(k.parmdata,5,1))="4", j.notesincas4 * (SUBSTRING(k.parmdata,7,12))/100, 0.0))))) AS remainingcash
			FROM termlist a
			LEFT JOIN termonln b on (b.termcode = a.termcode)
			LEFT JOIN codelist c ON (b.status = c.codeseqno AND c.codetype = "TERMSTAT")
			LEFT JOIN location d ON (d.loccode = a.loccode)
			LEFT JOIN codelist e ON (a.installationtype = e.codeseqno AND e.codetype = "INSTTYPE")
			LEFT JOIN termsupx i ON (i.termcode = a.termcode)
			LEFT JOIN termctrs j ON (j.termcode = a.termcode)
			LEFT JOIN termparm k ON (k.tpcode = 11 AND ((k.progcode = a.progcode AND a.progcode_denomination = 0) OR (k.progcode = a.progcode_denomination AND a.progcode_denomination > 0) )
			AND ((k.proglang=a.proglang AND a.proglang_denomination = 0) OR (k.proglang=a.proglang_denomination AND a.proglang_denomination > 0) ))
			WHERE a.termtype = "ATM" AND
				a.termcode = ?';

		return $this->db->query($query, func_get_args());
	}

	//termCode
	function getATMContacts()
	{
		$query = "SELECT xml FROM termlist WHERE termcode = ?";
		return $this->db->query($query, func_get_args());
	}

	//$luno
	function getATMLastCommandStatus()
	{	
		$query = "CALL sp_getatmlastcmdstatus(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getATMStatus()
	{				
		$query = "CALL sp_getatmstatus";
		return $this->db->query($query);
	}
	//$terminalCode
	function getATMCounters()
	{
		$query = "CALL sp_getatmcounters(?)";
		return $this->db->query($query, func_get_args());
	}
	//$progcode, $proglang
	function getTerminalPrograms()
	{	
		$query = "CALL sp_getterminalprograms(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getTerminalConfig()
	{		
		$query = "CALL sp_getterminalconfig(?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getTerminalFitness()
	{		
		$query = "CALL sp_getterminalfitness(?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getTerminalSupplies()
	{		
		$query = "CALL sp_getterminalsupplies(?)";
		return $this->db->query($query, func_get_args());
	}
	//$luno
	function getTerminalCommands()
	{	
		$query = "CALL sp_getterminalcommands(?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getTerminalStatusHistory()
	{	
		$query = "CALL sp_getterminalstatushistory(?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getATMJournal()
	{		
		$query = "CALL sp_getterminaljournal('ATM',?)";
		return $this->db->query($query, func_get_args());
	}
	//$terminalCode
	function getATMTransactionHistory()
	{		
		$query = "CALL sp_getatmtranhistory(?)";
		return $this->db->query($query, func_get_args());
	}
	//$cmd, dtlog, $limit
	function getTransactionLog()
	{
		$query = "CALL sp_gettranhistory(?,?,?)";
		$res1 = $this->db->query($query, func_get_args());
		
		$result = $res1->result_array();
		
		$res1->free_result();
		$res1->next_result();
					
		$query = "SELECT FOUND_ROWS() as rows";
		$res2 = $this->db->query($query);
		$res2 = $res2->row_array();
		$res2 = $res2['rows'];
		
		return array('result' => $result, 'numrows' => $res2);
	}
	//Maintenance Form 
	//$termCode
	function getTerminalInfo()
	{
		$query = "CALL sp_getterminalinfo('ATM',?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getTerminalLanguage()
	{
		$query = "CALL sp_getterminallanguage";
		return $this->db->query($query);
	}
	
	function getProductCodes()
	{
		$query = "CALL sp_getproductcodes";
		return $this->db->query($query);
	}
	//$branchCode
	function getLocations()
	{
		$query = "CALL sp_getlocations(?)";
		return $this->db->query($query, func_get_args());
	}
	function getAllLocations()
	{
		$query = "CALL sp_getalllocations()";	
		return $this->db->query($query);
	}
	function getProgramCodes()
	{
		$query = "CALL sp_getprogramcodes";
		return $this->db->query($query);
	}
	//$codeValue
	function getCodelist()
	{
		$query = "CALL sp_getcodelist(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getDenomination()
	{
		$query = "CALL sp_getdenomination";
		return $this->db->query($query);
	}
	//$progCode, $progLang
	function getTerminalDenomination()
	{
		$query = "CALL sp_getterminaldenomination(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode, $termID, $luno, $installType, $codeDeno, $langDeno, $emulation, $prodCode,
	//$locCode, $description, $amtdec, $progCode, $progLang, $termLang, $screenLoadSize, $stateLoadSize,
	//$fitLoadSize, $optLoadSize, $otherLoadSize, $isLoadKeyNew, $dispLogic, $isLoadPower, $dtProd, $xml1, $xml2
	function insertATMTerminal()
	{
		$query = "CALL sp_insertatmterminal(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode, $termID, $luno, $installType, $codeDeno, $langDeno, $emulation, $prodCode,
	//$locCode, $description, $amtdec, $progCode, $progLang, $termLang, $screenLoadSize, $stateLoadSize,
	//$fitLoadSize, $optLoadSize, $otherLoadSize, $isLoadKeyNew, $dispLogic, $isLoadPower, $dtProd, $xml1, $xml2
	function updateATMTerminal()
	{
		$query = "CALL sp_updateatmterminal(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$termCode
	function deleteATMTerminal()
	{
		$query = "CALL sp_deleteatmterminal(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getCurrency()
	{
		$this->db = $this->load->database(DB1, TRUE);
		$query = "CALL sp_getcurrency";
		return $this->db->query($query);
	}
	
	//ATM Key Management
	function getNodeList()
	{
		$query = "SELECT nodename, description FROM nodelist WHERE nodename IN ('ATM', 'POS')";
		return $this->db->query($query);
	}
	
	function getEncryptionMode()
	{
		$query = "CALL sp_getcodelist('ENCMODE')";
		return $this->db->query($query);
	}
	
	function getEncryptionType()
	{
		$query = "CALL sp_getcodelist('CRYPTYPE')";
		return $this->db->query($query);
	}
	//nodename, $secCode, $encMode, $encType1, $encType2, $pek1, $pek2, $variant1, $variant2, $kek1, $kek2, $userAudit, $workstation, $xml
	function insertSecurityKey()
	{
		$query = "CALL sp_insertsecuritykey(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//nodename, $oldCode, $secCode, $encMode, $encType1, $encType2, $pek1, $pek2, $variant1, $variant2, $kek1, $kek2, $userAudit, $workstation, $xml
	function updateSecurityKey()
	{
		$query = "CALL sp_updatesecuritykey(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getSecurityKeyList()
	{
		$query = "CALL sp_getsecuritykeylist()";
		return $this->db->query($query);
	}
	//$secCode
	function deleteSecurityKey()
	{
		$query = "CALL sp_deletesecuritykey(?)";
		return $this->db->query($query, func_get_args());
	}
	//end
	
	//Issue Log
	
	//termCode, brseqno, userID, ipAddress, workstation
	function getIssueList()
	{
		$query = "CALL sp_getIssueList(?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getIssueTypes()
	{
		$query = "CALL sp_getcodelist('TRMPRBTYPE')";
		return $this->db->query($query);
	}
	
	function getHardwareTypes()
	{
		$query = "CALL sp_getcodelist('TERMPROB')";
		return $this->db->query($query);
	}
	
	function insertIssueLog()
	{
		$query = "CALL sp_insertIssueLog(?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function updateIssueLog()
	{
		$query = "CALL sp_updateIssueLog(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getIssueInfo()
	{
		$query = "CALL sp_getIssueInfo(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function deleteIssueLog()
	{
		$query = "CALL sp_deleteIssueLog(?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	 function insertAuditLogclixx()
        {
                $query = "CALL sp_insertlogclixx(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                return $this->db->query($query, func_get_args());
        }	
}
/* End of file atm_model.php */
/* Location: ./application/models/coreapp/atm_model.php */
