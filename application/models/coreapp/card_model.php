<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Card_model extends CI_Model {
	public $db, $security;
	
	function __construct()
	{
		$this->db 		= $this->load->database(DB1, TRUE);
		$this->security = $this->coresecurity;
		
		//sets the current database
		$this->security->_initDb($this->db);
	}
	//$userAudit, $sessionID
	function checkLogin()
	{	
		$query = "CALL sp_checklogin(?,".APPSEQNO.",?,@v_err,@v_msg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @v_err as errno, @v_msg as errmsg";			
		return $this->db->query($query);
	}
	
	function getLastInsertID()
	{
		return $this->db->insert_id();
	}
	
	function getCardBINWithFormat()
	{
		$query = "CALL sp_getCardBINWithFormat()";
		return $this->db->query($query);
	}
	
	function getCardBIN()
	{	
		$query = "CALL sp_getcardbin";
		return $this->db->query($query);
	}
	
	function getCellBIN()
	{		
		$query = "CALL sp_getCodeList('CELLBIN')";
		return $this->db->query($query);
	}
	
	//for QCRB product codes
	function getProductCodes()
	{		
		$query = "CALL sp_getCodeList('PRCDCODE')";
		return $this->db->query($query);
	}
	
	function getBPayStat()
	{
		$query = "CALL sp_getcodelist('BPAYSTAT')";
		return $this->db->query($query);
	}
	
	//verify form
	//$cardNo, $branchID, $ipAddress, $workstation, $userAudit
	function getCardInfo()
	{	
		$query = "CALL sp_getcardinfo(?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$cardNo, $branchID, $userAudit, $sessionid
	function getCardToken()
	{	
		$query = "CALL sp_getcardtoken(?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$cardNo, $branchID, $userAudit, $sessionid
	function getCardInfoByToken()
	{	
		$query = "CALL sp_getcardinfobytoken(?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$cardNo
	function getValidCardForReplacement()
	{	
		$query = "CALL sp_getvalidcardforreplace(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$cardNo
	function getValidCardForChangeStat()
	{	
		$query = "CALL sp_getvalidcardforchgstat(?)";
		return $this->db->query($query, func_get_args());
	}
	//$prseqno, $status, $statDesc, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function updateCardStatus()
	{		
		$query = "CALL sp_updatecardstatus(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	//$prseqno, $userAudit, $sessionID
	function getCardAccountLink()
	{		
		$query = "CALL sp_getcardaccountlink(?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prseqno, prseqnolink, brseqno, ipaddress, workstation, userID, override, sessionID
	function setCardPrimaryAccountLink()
	{
		$query = "CALL sp_setcardprimaryaccountlink(?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$prseqno, $userAudit, $sessionID
	function getCardBillsLink()
	{		
		$query = "CALL sp_getcardbillslink(?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$param
	function getCardType()
	{	
		//Y = Personalized or N = else
		$query = "CALL sp_getcardtype(?)";
		return $this->db->query($query, func_get_args());
	}
	//grouptype
	function getCardTypeFees()
	{
		$query = "SELECT accttype, description, grouptype FROM accttype WHERE isfee = 'Y'";
		return $this->db->query($query, func_get_args());
	}
	//
	function getCardTypeGeneralSettings()
	{
		$query = "SELECT accttype, description, grouptype FROM accttype WHERE grouptype = 'CARD' ORDER BY accttype";
		return $this->db->query($query, func_get_args());
	}
	//$branchID, $ipAddress, $workstation, $userAudit, $sessionID
	function getCardOrderList()
	{		
		$query = "CALL sp_getcardorderlist(?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$branchID, $ipAddress, $workstation, $userAudit, $sessionID
	function getCardOrderHistory()
	{
		$query = "CALL sp_getcardorderhistory(?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$branchID, $ipAddress, $workstation, $userAudit, $sessionID
	function getCardForGenList()
	{		
		$query = "CALL sp_getcardforgenlist(?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$orderNo, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID
	function deleteCardOrder()
	{
		$query = "CALL sp_deletecardorder(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$cardProductCode, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID
	function deleteCardProduct()
	{
		$query = "CALL sp_deletecardproduct(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$orderNo, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID
	function cancelCardOrder()
	{
		$query = "CALL sp_cancelcardorder(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$orderNo, $acctype, $brseqno, $outlet, $count, $bin, $personalized,
	//$xml, $ipAddress, $workstation, $userAudit, $sessionID
	function updateCardOrder()
	{
		$query = "CALL sp_updatecardorder(?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$acctType, $acctDesc, $brseqno, $outlet, $count, $bin, $personalized, $custName,
	//$xml, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function insertCardOrder()
	{
		$query = "CALL sp_insertcardorder(?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//account enrollment form
	//$prseqno, $pseqnoLink, $prptr, $acctNo, $acctDesc, $prKey, $branchID,
	//$ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function deleteCardAccountLink()
	{
		$query = "CALL sp_deletecardaccountlink(?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$acctno
	function getAccountInfoByKey()
	{
		$query = "CALL sp_getaccountinfobykey(?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getAccountBIN()
	{	
		$query = "CALL sp_getaccountbin()";
		return $this->db->query($query);
	}
	//$prseqno, $pseqnoLink, $prptr, $xml, $acctNo, $acctDesc,
	//$prKey, $branchID, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function insertCardAccountLink()
	{	
		$query = "CALL sp_insertcardaccountlink(?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end

	//$prseqno, $pseqnoLink, $prptr, $xml, $acctNo, $acctDesc,
	//$prKey, $branchID, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function updateCardAccountLink()
	{	
		$query = "CALL sp_updatecardaccountlink(?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//bills enrollment form
	function getBillsInstList()
	{
		$query = "CALL sp_getinstitutionlist('1,2,5','1,3')";
		return $this->db->query($query);
	}
	
	//prseqno, bpayseqno, payptr, subsno, subsname, status, brseqno, ipaddress, workstation, userAudit, sessionID
	function insertBillsPayment()
	{
		$query = "CALL sp_insertbillspay(?,?,?,?,?,?,?,?,?,?,". APPSEQNO .",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prseqno, blistseq, payptr, oldsubsno, subsno, subsname, status, brseqno, ipaddress, workstation, userAudit, sessionID
	function updateBillsPayment()
	{
		$query = "CALL sp_updatebillspay(?,?,?,?,?,?,?,?,?,?,?,". APPSEQNO .",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prseqno, blistseq, brseqno, ipaddress, workstation, userAudit, sessionID
	function deleteBillsPayment()
	{
		$query = "CALL sp_deletebillspay(?,?,?,?,?,?,". APPSEQNO .",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//mobile enrollment
	//$cardno, $userAudit, $sessionID
	function getCardMobileLink()
	{
		$query = "CALL sp_getCardMobileLink(?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$prseqno, $cifseqno, $brseqno, $prkey, $cardBIN, $allows
	//$ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function insertCardMobileLink()
	{
		$query = "CALL sp_insertCardMobileLink(?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$pseqnoLink, $prseqno, $prKey, $cardBIN, $branchID,
	//$ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function deleteCardMobileLink()
	{
		$query = "CALL sp_deleteCardMobileLink(?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//replacement form
	//$prkey
	function getCardForReplace()
	{
		$query = "CALL sp_getcardforreplace(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//$prseqno, $pseqnoLink, $cifseqno, $status, $xml, $prKey2, 
	//$brseqno, $ipAddress, $workstation, $userAudit, $userOverride, $sessionID
	function cardReplace()
	{
		$query = "CALL sp_cardreplace(?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//issuance form
	//$prKey
	function getValidCardForActivation()
	{
		$query = "CALL sp_getvalidcardforactivation(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//issuance form
	//$prKey
	function getValidCardForActivationPCIDSS()
	{
		$query = "CALL sp_getvalidcardforactivationpcidss(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	/*function getactivationnextstatus($acctType) {
		$query = "CALL sp_getactivationnextstatus(?)";
		return $this->db->query($query, func_get_args());
	}*/
	//$prseqno, $prkey, $cifseqno, $custName, $acctType, 
	//$branchID, $ipAddress, $workstation, $userAudit, $sessionID
	function activateCard()
	{
		$query = "CALL sp_activatecard(?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//account info form
	//$prseqno
	function getAcctCardLink()
	{
		$query = "CALL sp_getacctcardlink(?)";
		return $this->db->query($query, func_get_args());
	}
	//end
	
	//account info form
	function getAccountType()
	{
		$query = "CALL sp_getaccounttype()";
		return $this->db->query($query, func_get_args());
	}
	//$cifseqno, $brseqno, $prKey, $acctType, $status, $allows, $ipAddress,
	//$workstation, $userAudit, $userOverride, $sessionID
	//1 - Online
	function insertAccount()
	{
		$query = "CALL sp_insertaccount(?,?,?,?,1,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prseqno, cifseqno, brseqno, prkey, accttype, authmode, status, allows, 
	//ipAddress, workstation, userAudit, override, sessionID
	function updateAccount()
	{
		$query = "CALL sp_updateaccount(?,?,?,?,?,0,?,?,?,?,?,?,".APPSEQNO.",?,?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//account type change
	//$prseqno, $accttype, $userAudit, $sessionID
	function setNewCardType()
	{
		$query = "CALL setnewcardtype(?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end
	
	//card generation
	//$orderNo, $brseqno, $userAudit, $sessionID
	function generateCards()
	{
		$query = "CALL sp_generatecards(?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$bin
	function getCardFormat()
	{
		$query = "CALL sp_getcardformat(?, @expyears, @format, @wts, @grace, @minday, @err);";
		
		//$this->db->trans_start();
		$this->db->query($query, func_get_args());
		$result = $this->db->query("SELECT @expyears AS expyears, @format AS format, @wts AS wts, @grace AS grace, @minday AS minday, @err AS err");
		//$this->db->trans_complete(); 

		return $result;
	}
	//$orderNo, $brseqno
	function getCardOrder()
	{
		$query = "CALL sp_getcardorder(?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getLimitSeqno($brseqno, $acctType, $limitStr)
	{
		$query = "CALL sp_getLimitSeqno(?,?,?, @seqno, @err);";
		
		switch ($limitStr)
		{
			case 'ONLN':
				$limit = 'onlinelmt';
				break;
			case 'OFLN':
				$limit = 'offlinelmt';
				break;
		}
		
		//$this->db->trans_start();
		$this->db->query($query, func_get_args());
		$result = $this->db->query("SELECT @seqno as ". $limit .", @err as err");
		//$this->db->trans_complete(); 

		return $result;
	}
	//$acctType
	function getLimitPINCountMax()
	{
		$query = "CALL sp_getLimitpincountmax(?, @pinctr, @err)";
		
		//$this->db->trans_start();
		$this->db->query($query, func_get_args());
		$result = $this->db->query("SELECT @pinctr as pinctr, @err as err");
		//$this->db->trans_complete(); 

		return $result;
	}
	//$acctType
	function getAccountAllowsDefault()
	{
		$query = "CALL sp_getAccountallowsdefault(?,'CARD','CARD', @initAllows, @err)";
		
		//$this->db->trans_start();
		$this->db->query($query, func_get_args());
		$result = $this->db->query("SELECT @initAllows as initAllows, @err as err");
		//$this->db->trans_complete(); 

		return $result;
	}
	
	function generateCardSeries()
	{
		$query = "CALL sp_generatecardseries(@series, @err)";
		
		//$this->db->trans_start();
		$this->db->query($query, func_get_args());
		$result = $this->db->query("SELECT @series as series, @err as err");
		//$this->db->trans_complete(); 

		return $result;
	}
	//$format, $prcdcode, $bin, $series, $chkdigit, $brcode
	function getCardNo()
	{
		$query = "SELECT sf_getCardNo(?,?,?,?,?,?) as cardno";
		return $this->db->query($query, func_get_args());
	}
	//$cardno, $wts
	function getCheckDigit()
	{
		$query = "SELECT sf_getCheckDigit(?,?) as chkdgt";
		return $this->db->query($query, func_get_args());
	}
	//end
	
	//card embossing
	function getLastBatchNo()
	{
		$query = "CALL sp_getlastbatchno()";
		return $this->db->query($query);
	}

	function getCardForEmbossing()
	{
		$query = "CALL sp_getcardforembossing(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//$acctType
	function getStatusForEmboss()
	{
		$query = "CALL sp_getstatusforemboss(?)";
		return $this->db->query($query, func_get_args());
	}
	//$prseqno, $acctType, $batchNo, $status, $brseqno, $userAudit, $sessionID
	function setCardForEmbossing()
	{
		$query = "CALL sp_setcardforembossing(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$batchNo, $brseqno, userID, sessionID
	function getCardEmbossingByBatch()
	{
		$query = "CALL sp_getcardembossingbybatch(?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//seccode
	function generateSeq()
	{
		$query = "CALL sp_generateSeq(?, @embossno)";
		
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @embossno as embossno";			
		return $this->db->query($query);
	}
	//end
	
	//card enrollment
	//prtype
	function getDefaultTranAllows()
	{
		$query = "CALL sp_getdefaulttranallows(?)";
		return $this->db->query($query, func_get_args());
	}
	
	//prtype, acctType, prtype
	function getDefaultAllows()
	{
		$query = "CALL sp_getdefaultallows(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getChannelLocks()
	{
		$query = "CALL sp_getcodelist('CHLOCK')";
		return $this->db->query($query);
	}
	//bin, prkey, acctType, cifseqno, emboss, allows, fcash, ftype, fptr, xml, brseqno, ipAddress, workstation, userAudit, override, sessionID
	function insertCard()
	{
		$query = "CALL sp_insertcard(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//prseqno, cifseqno, accttype, status, allows, fcash, ftype, fptr, xml, brseqno, ipAddress, workstation, userAudit, override, sessionID
	function updateCard()
	{
		$query = "CALL sp_updatecard(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//end

	//card verification
	//brseqno, ipAddress, workstation, userID, override, sessionID
	function getCardForVerification()
	{
		$query = "CALL sp_getcardforverification(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//prseqno, prkey, brseqno, ipAddress, workstation, userID, override
	function verifyCard()
	{
		$query = "CALL sp_verifycard(?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	//end

	//log
	//$msgType, $brseqno, $sysVCode, $prKey, $prKey, $userID, $workstation, $xml
	function insertLogclixx()
	{
		$query = "CALL sp_insertlogclixx(?,990201,?,?,0,'ACCT','',?,'',?,'','','','','',?,'','WEB','WEB',?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	//card list
	//brseqno, ipAddress, workstation, userID, sessionID
	function getCardList()
	{
		//$query = "CALL sp_getcardlist(?,?,?,?,".APPSEQNO.",?)";
		//return $this->security->validateQuery($query, func_get_args());
		$query = "SELECT * FROM prmaster LIMIT ?,?";
		return $this->db->query($query, func_get_args());
	}
	
	function getCardStatus()
	{
		$query = "SELECT status, description, accttype, sorder, iseditable FROM prstatus WHERE prtype = 'CARD'";
		return $this->db->query($query);
	}
	
	function executeQuery($query)
	{
		$this->db->trans_begin();

		foreach ($query as $sql) {
			$this->db->query($sql);
			$this->db->insert_id();
		}
		
		//simulate error
		/*for ($i = 0; $i <= count($query); $i++) {
			if ($i = 40) {
				$this->db->query('dfjagkgfasdkhgkdasj jksgh asdkghg ' . count($query));
			} else {
				$this->db->query($sql);
			}
		}*/
		
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return TRUE;
		}
		//$query = "CALL sp_executequery(?)";
		//return $this->db->query($query, func_get_args());
	}
	
	function getMaxBills()
	{				
		$query = "CALL sp_getconfigvalue('BILLERSUBMAX', @max)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @max as maxbills";			
		return $this->db->query($query);
	}
	
	function getMaxAccount()
	{				
		$query = "CALL sp_getconfigvalue('ACCTNOMAX', @max)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @max as maxacct";			
		return $this->db->query($query);
	}
	
	function getDefFastCash()
	{
		$query = "CALL sp_getconfigvalue('CUSTOMIZEFASTCASH', @dfc)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @dfc as dfc";
		return $this->db->query($query);
	}
	
	//online limits
	//prseqno
	function getCardLimitList()
	{
		$query = "CALL sp_getcardlimitlist(?)";
		return $this->db->query($query, func_get_args());
	}
	
	//limitseqno
	function getCardDefaultLimitList()
	{
		$query = "CALL sp_getcarddefaultlimitlist(?)";
		return $this->db->query($query, func_get_args());
	}
	
	//accttype, limittype, brseqno
	function getCardLimits()
	{
		$query = "CALL sp_getcardlimits(?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	//prseqno, limitseqno, trancode, cycleavail, cyclemax, ctravail, ctrmax, 
	//tranmax, tranmin, cycle, duralimit, nonfeectrmax, nonfeetranmax, brseqno
	//ipAddress, workstation, userAudit, override, sessionID
	function updateOnlineLimit()
	{
		$query = "CALL sp_updateonlinelimit(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//prseqno, limitseqno, trancode, cycleavail, cyclemax, ctravail, ctrmax, tranmax, tranmin, cycle, duralimit, nonfeectrmax, nonfeetranmax, brseqno
	function updateOfflineLimit()
	{
		$query = "CALL sp_updateofflinelimit(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}
	
	//prseqno, limitseqno, brseqno, ipAddress, workstation, userAudit, override, sessionID
	function setOnlineDefaultCardLimit()
	{
		$query = "CALL sp_setonlinedefaultcardlimit(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//pinctr, prseqno, brseqno, ipAddress, workstation, userAudit, override, sessionID
	function setPINRetryMaxCount()
	{
		$query = "CALL sp_setpinretrymaxcount(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//offline limits
	//prseqno
	function getCardLimitList2()
	{
		$query = "CALL sp_getcardlimitlist2(?)";
		return $this->db->query($query, func_get_args());
	}
	//end
	
	//general settings
	
	//formatCode, brseqno, ipAddress, workstation, userAudit, override, sessionID
	function getAccountFormat()
	{
		$query = "CALL sp_getaccountformat(?,?,?,?,?,?,". APPSEQNO .",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//formatCode, formatType, formatValue, formatDesc, prodCode, xml, acctchar, brseqno, ipAddress, workstation, userAudit, override, sessionID
	function setAccountFormat()
	{
		$query = "CALL sp_setaccountformat(?,?,?,?,?,?,?,?,?,?,?,?,". APPSEQNO .",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//account verification
	//brseqno, ipAddress, workstation, userID, override, sessionID
	function getAccountForVerification()
	{
		$query = "CALL sp_getaccountforverification(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//prseqno, prkey, brseqno, ipAddress, workstation, userID, override, sessionID
	function verifyAccount()
	{
		$query = "CALL sp_verifyaccount(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	//end
	
	//limitseqno, limittype, accttype, grouptype, description, maxval, minval, pinctrdef, pinctrmax,
	//brseqno, ipAddress, workstation, userID, override, sessionID
	function updateDefaultOnlineLimit()
	{
		$query = "CALL updatedefaultonlinelimit(?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//limittype, acctype, grouptype, description, maxval, minval, pinctrdef, pinctrmax,
	//brseqno, ipAddress, workstation, userID, override, sessionID
	function insertDefaultOnlineLimit()
	{
		$query = "CALL sp_insertdefaultonlinelimit(?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//accttype
	function getDefaultOnlineLimitList()
	{
		$query = "CALL sp_getdefaultonlinelimitlist('ONLN',?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getAccountTypeLimit()
	{
		$query = "CALL sp_getaccounttypelimit";
		return $this->db->query($query);
	}
	//trxcode
	function getTransactionAllowsLimit()
	{
		$query = "CALL sp_gettransactionallowslimit(?,?)";
		return $this->db->query($query, func_get_args());
	}
	//limitseqno, trxcode, cyclemax, cycledef, ctrmax, ctrdef, tranmax, tranmaxdef, tranmin, tranmindef, cycle, duralimit,
	//nonfeectr, nonfeectrdef, nonfeetran, nonfeetrandef, nonfeecycle, brseqno, ipaddress, workstation, userid, override, sessionid
	function setDefaultTranOnlineLimit()
	{
		$query = "CALL sp_setdefaulttranonlinelimit(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	
	//limitseqno
	function getCardDefaultOnlineLimits()
	{
		$query = "SELECT a.*, b.description, b.isamtlimit, c.brseqno FROM limitdef a, trxlistx b, limitlst c WHERE b.islimit = 'Y' AND a.limitseqno = ? AND NOT a.limitseqno = 0 AND a.trxcode = b.trxcode AND a.limitseqno = c.limitseqno";
		return $this->db->query($query, func_get_args());
	}
	//limitseqno, brseqno, ipAddress, workstation, userID, override, sessionID
	function deleteDefaultOnlineLimit()
	{
		$query = "CALL sp_deletedefaultonlinelimit(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//limitseqno, trxcode, brseqno, ipAddress, workstation, userID, override, sessionID
	function deleteTranOnlineLimit()
	{
		$query = "CALL sp_deletetranonlinelimit(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//limitseqno
	function getTranLimitsForUpdate()
	{
		$query = "CALL sp_gettranlimitsforupdate(?)";
		return $this->db->query($query, func_get_args());
	}
	//prseqno, brseqno, ipAddress, workstation, userID, override, sessionID
	function resetPINRetryCount()
	{
		$query = "CALL sp_resetpinretrycount(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}
	
	//default PIN generation
	
	//cardBIN
	function getCardListForPINGen()
	{
		$query = "CALL sp_getCardListForPINGen(?)";
		return $this->db->query($query, func_get_args());
	}
	
	function getPINGenBatch()
	{
		$query = "CALL sp_getPINGenBatch(@seqno, @stepcount)";
		$this->db->query($query);
		
		$query = "SELECT @seqno AS batchno, @stepcount AS stepcount";			
		return $this->db->query($query);
	}
	//cardBIN
	function getPINLenght()
	{
		$query = "CALL sp_getPINLenght(?, @minpin, @maxpin, @errno, @errmsg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @minpin AS minpin, @maxpin AS maxpin, @errno AS errno, @errmsg AS errmsg";			
		return $this->db->query($query);
	}
	//prseqno, xml1
	function updateCardDefPIN()
	{
		$query = "CALL sp_updateCardDefPIN(?,?)";
		$this->db->query($query, func_get_args());
	}
	//seqno
	function updatePINGenSequence()
	{
		$query = "CALL sp_updatePINGenSequence(?)";
		$this->db->query($query, func_get_args());
	}
	//accttype
	function getAcctStat()
	{
		$query = "SELECT status AS statcode, description FROM prstatus WHERE prtype = 'ACCT' AND accttype = ?";			
		return $this->db->query($query, func_get_args());
	}
	
	//prkey
	function updateAcctStatus()
	{
		$query = "CALL sp_changeacctstatus(?,?,?,?,?,?,@errorno,@errormsg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @errorno AS errno, @errormsg AS errmsg";			
		return $this->db->query($query);
	}
	
	function removeAcct()
	{
		$query = "CALL sp_removeacct(?,?,?,?,?,?,@errorno,@errormsg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @errorno AS errno, @errormsg AS errmsg";
		return $this->db->query($query);	
	}

	function searchAccountbyCIF()
	{
		$query = "SELECT 
			a.prseqno, 
			a.prkey AS acctno, 
			a.accttype, 
			b.description 
			FROM 
			prmaster a 
			LEFT JOIN accttype b ON (a.accttype = b.accttype) 
			LEFT JOIN branches c ON (a.brseqno = c.brseqno) 
			WHERE a.cifseqno = ? AND c.brseqno = ? AND a.accttype IN(10,20) 
			
			UNION ALL 
			
			SELECT a.prseqno, c.prkey AS acctno, b.accttype, b.description 
			FROM prlinkxx a, 
			accttype b, 
			prmaster c 
			WHERE a.prseqno = c.prseqno 
			AND b.accttype = c.accttype 
			AND a.prtype = 'CIF' 
			AND a.pseqnolink = ? 
			AND b.accttype IN(10,20) 
			AND c.brseqno = ? ORDER BY 3";
		return $this->db->query($query, func_get_args());	
	}

	function validateAccountLink()
	{
		$query = "SELECT count(prseqno) AS link FROM prlinkxx WHERE pseqnolink = ?";
		return $this->db->query($query, func_get_args());	
	}
	// manual select! for temporary used only (PILOT) : track id (7999)
	function validateAccount()
	{
		$query = "SELECT count(a.prseqno) AS cntr FROM prmaster a, prdetail b WHERE a.prseqno = b.prseqno AND a.prtype = 'ACCT' AND a.prkey = ?";
		return $this->db->query($query, func_get_args());	
	}
	
	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function insertCardType()
	{
		$query = "CALL sp_insertcardtype(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?,?)";
		return $this->db->query($query, func_get_args());	
	}
	
	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function editCardType()
	{
		$query = "CALL sp_editcardtype(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?,?)";
		return $this->db->query($query, func_get_args());	
	}

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function getCardTypeDesc()
	{
		$query = "SELECT description AS acctname FROM accttype WHERE accttype = ?";
		return $this->db->query($query, func_get_args());	
	}

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function getCardIssuanceForApproval()
	{
		$query = "CALL sp_getcardissuanceapproval(?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());	
	}

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function approveCardIssuance()
	{
		$query = "CALL sp_approvecardissuance(?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());	
	}

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function updateCardForIssuance()
	{
		$query = "CALL sp_updatecardforissuance(?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());	
	}

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function getCardProductInfo()
	{
		$query = "SELECT accttype, description, acctcode, xml1 FROM accttype WHERE accttype = ? AND prtype = 'CARD'";
		return $this->db->query($query, func_get_args());	
	}	

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function getavailableaccttype()
	{
		$query = "SELECT accttype FROM accttype";
		return $this->db->query($query, func_get_args());	
	}	

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function getavailablecardstat()
	{
		$query = "SELECT status, description FROM prstatus WHERE accttype = (SELECT accttype FROM accttype WHERE prtype = 'CARD' LIMIT 1)";
		return $this->db->query($query, func_get_args());	
	}	 

	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function batchcifacctupdate()
	{
		$query = "CALL sp_updatebatchacctinfo(?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());	
	}	
	// producttype, productdesc, productcode, bin, currency, xrate, groupno, groupname, subgroupno, aclass, isdefault, useraudit, override, wkstn, xml1, islimit, isfee, ispersonalized, APPSEQNO, isgeneric
	function updateEmbossName()
	{
		$query = "CALL sp_updateEmbossName(?,?,@errno,@errmsg)";
		$this->db->query($query, func_get_args());
		
		$query = "SELECT @errno AS errno, @errmsg AS errmsg";
		return $this->db->query($query);
	}

	//
	function checkiftokencolexists()
	{
		$query = "SELECT COUNT(SCHEMA_NAME) AS PCIDSS INTO @PCIDSS FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'CoreSecurity'";//"CALL checkiftokencolexists(?)";
		$this->db->query($query, func_get_args());

		$query = "SELECT @PCIDSS AS PCIDSS;";
		return $this->db->query($query);
	}
	//
	function exportcardlist()
	{
		$query = "SELECT a.prseqno AS refno, CoreSecurity.GetDataFromToken(a.TokenID) AS cardno, b.brname, ".
			"CONCAT(c.firstname,' ',c.middlename,', ',c.lastname) AS custname ".
			"FROM prmaster a ".
			"LEFT JOIN branches b ON (a.brseqno = b.brseqno) ".
			"LEFT JOIN customer c ON (a.cifseqno = c.cifseqno) ".
			"WHERE a.prtype = 'CARD' ORDER BY a.prseqno;";
		return $this->db->query($query);
	}
	function exportcardacct()
	{
		$query = "SELECT a.prseqno AS refno, CoreSecurity.GetDataFromToken(a.TokenID) AS cardno, b.pseqnolink AS acct FROM prmaster a, prlinkxx b WHERE a.prseqno = b.prseqno ORDER BY b.prseqno;";
		return $this->db->query($query);
	}
	function getacctdtlx()
	{
		$query = "SELECT a.prkey AS acctno FROM prmaster a WHERE a.prseqno = ? AND a.prtype = 'ACCT';";
		return $this->db->query($query, func_get_args());
	}
	//status,prseqno,workstation,useraudit,sessionid
	function AuditChangeCardStatus()
	{
		$query = "CALL sp_AuditChangeCardStatus(?,?,?,?,?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//status,prseqno,workstation,useraudit,sessionid
	function getCurrentCardInfo()
	{
		$query = "CALL sp_getcurrentcardinfo(?)";
		return $this->security->validateQuery($query, func_get_args());
	}
	//$msgType, $brseqno, $sysVCode, $prKey, $prKey, $userID, $workstation, $xml
	function insertAuditLogclixx()
	{
		$query = "CALL sp_insertlogclixx(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		return $this->db->query($query, func_get_args());
	}

	function getCardStatusDesc()
	{
		$query = "SELECT description FROM prstatus WHERE prtype = 'CARD' AND accttype = ? AND status = ?";
		return $this->db->query($query, func_get_args());
	}

	function getAccountLength()
	{
		$query = "SELECT formatvalue FROM coreapp_fusion.formatxx WHERE formattype = 'ACCT' AND formatcode = 10";
		return $this->db->query($query, func_get_args());
	}

	function setcustomertocard()
	{
		$query = "CALL sp_setcustomertocard(?,?)";
		return $this->security->validateQuery($query, func_get_args());
	}

	function setEMVFormat()
	{
		$query = "CALL sp_setemvsettings(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}

	function getEMVFormat()
	{
		$query = "CALL sp_getemvsettings(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}

	function setCardAsEMV()
	{
		$query = "CALL sp_setemvpercard(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}

	function updateCardAsEMV()
	{
		$query = "CALL sp_updateemvpercard(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->security->validateQuery($query, func_get_args());
	}

	function getEMVFormatPerCard()
	{
		$query = "CALL sp_getemvpercard(?,?,?,?,?,?,".APPSEQNO.",?)";
		return $this->db->query($query, func_get_args());
	}

	function getembnotifrecipients()
	{
		$query = 'CALL coreapp_fusion.getembnotifrecipients()';
		return $this->db->query($query);
	}

	function getencryptionevent()
	{
		$query = 'CALL coresys_fusion.getencryptionevent()';
		return $this->db->query($query);
	}
}
/* End of file card_model.php */
/* Location: ./application/models/coreapp/card_model.php */