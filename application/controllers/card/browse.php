<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Browse extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDMGMT_NO);
		
		$this->load->model('coreapp/user_model');

		$result = $this->user_model->checkLogin($this->core->getUserID(), $this->core->getSessionID());

		$row = $result->row_array();

		if (intval($row['errno']) > 0) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Invalid Login Session. Please relogin'
			));
			exit();
		}
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		
		//cache problem
		$this->cache->clean();
		//card status
		if (!$cardStatus = $this->cache->get($this->core->getSessionID() . 'cardStatus')) {			
			$result = $this->card_model->getCardStatus();
			$cardStatus = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'cardStatus', $cardStatus, CACHE_TTL);
		}
		
		$data['cardStatusList'] = NULL;
		$statusx = array();
		foreach ($cardStatus as $key => $row) {
			$status = $row['status'];
			$desc = $row['description'];
			
			if (! in_array($status, $statusx) ) {
				$data['cardStatusList'] .= "'<option value=\"". $status ."\">". strtoupper($desc) ."</option>'+";
				$statusx[] = $status;
			}
		}
		//end
		
		//card types
		if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
			$result = $this->card_model->getCardType('N');
			$cardType = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
		}
		
		$data['cardTypeList'] = NULL;
		foreach ($cardType as $row) {
			$data['cardTypeList'] .= "'<option value=\"". $row['accttype'] ."\">". strtoupper($row['description']) ."</option>'+";
		}
		//end


		$data['sessionExp'] = $this->core->getSessionExp();
		
		$this->load->view('card/cardlist', $data);
	}
	
	function getData()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$db = $this->load->database(DB1, TRUE);
		
		//$db->trans_begin();
		
		/* dt columns */
		$aColumns = array( 'a.prkey', 'a.cifseqno', 'a.prtype', 'a.dtlastact', 'a.status' );
		
		/* all columns */
		$sColumns = array( 'a.prkey', 'a.cifseqno', 'a.prtype', 'a.accttype', 'a.dtlastact', 'a.prseqno', 'a.status', 'b.firstname', 'b.middlename', 'b.lastname');
		if ($this->core->isCoreEncrypt()) {
			array_push($sColumns, 'a.TokenID');
		} else {
			array_push($sColumns, '"" AS TokenID');
		}
		
		/* filter columns */
		$fColumns = array( 'a.prkey', 'a.cifseqno', 'a.dtlastact', 'a.prseqno', 'b.firstname', 'b.middlename', 'b.lastname');
		
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = 'prkey';
		
		/* DB table to use */
		$sTable = 'prmaster';
		
		/* 
		 * Paging
		 */
		$sLimit = NULL;
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] !== '-1' )
		{
			$sLimit = " LIMIT ". $db->escape_str( $_GET['iDisplayStart'] ). ", ".
				$db->escape_str( $_GET['iDisplayLength'] );
		}
		
		/*
		 * Ordering
		 */
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = 'ORDER BY ';
			for ( $i = 0 ; $i < intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] === TRUE )
				{
					$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
						".$db->escape_str( $_GET['sSortDir_'.$i] ) .", ";
				}
			}
			
			$sOrder = substr_replace( $sOrder, '', -1 );
			if ( $sOrder === 'ORDER BY' )
			{
				$sOrder = '';
			}
		}
		
		
		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		 
		//custom
		$custom = '';
		
		$cardStatus = $_GET['cardStatus'];
		if ($cardStatus != 0) {
			$custom .= " AND a.status = ". $cardStatus;
		}
		$cardType = $_GET['cardType'];
		if ($cardType != '-1') {
			$custom .= " AND a.accttype = ". $cardType;
		}
		
		//branch filter
		$brFilter = '';
		if (!$this->core->isHeadOffice()) {
			$brseqno = $this->core->getBranchID();
			$brFilter = "AND a.brseqno = ". $brseqno;
		}
		
		$sWhere = "WHERE a.prtype = 'CARD' ". $brFilter . $custom;//FRANZ
		if ( $_GET['sSearch'] !== '' )
		{
			$sWhere .= " AND (";
			for ( $i = 0 ; $i < count($fColumns); $i++ )
			{
				$sWhere .= $fColumns[$i]. " LIKE '%". $db->escape_str( $_GET['sSearch'] ). "%' OR ";
			}
			$sWhere = substr_replace( $sWhere, '', -3 );
			$sWhere .= ")";
		}
		
		/* Individual column filtering */
		for ( $i = 0; $i < count($aColumns); $i++ )
		{
			if ( $_GET['bSearchable_'.$i] === TRUE && $_GET['sSearch_'.$i] !== '' )
			{
				if ( $sWhere === '' )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= $aColumns[$i]. " LIKE '%". $db->escape_str($_GET['sSearch_'. $i]). "%' ";
			}
		}
		
		/*
		 * SQL queries
		 * Get data to display
		 */
		$franz = $sQuery = "SELECT SQL_CALC_FOUND_ROWS ".
			str_replace(' , ', ' ', implode(', ', $sColumns)).
			" FROM ". $sTable ." a".
			" LEFT JOIN customer b ON (a.cifseqno = b.cifseqno) ".
			" LEFT JOIN branches c ON (a.brseqno = c.brseqno) ".
			$sWhere .
			$sOrder .
			$sLimit;
		$rResult = $db->query( $sQuery );
		
		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() AS rows";
		$rResultFilterTotal = $db->query( $sQuery );
		$aResultFilterTotal = $rResultFilterTotal->row_array();
		$iFilteredTotal = $aResultFilterTotal['rows'];
		
		/* Total data set length */
		$sQuery = "SELECT COUNT(".$sIndexColumn.") AS count".
			" FROM ". $sTable ." a WHERE (prtype = 'CARD'". $brFilter .")";
		$rResultTotal = $db->query( $sQuery );
		$aResultTotal = $rResultTotal->row_array();
		$iTotal = $aResultTotal['count'];
		
		//card type
		if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
			$sQuery = "CALL sp_getcardtype('N')";
						
			$result = $db->query( $sQuery );
			$cardType = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
		}
		
		//card status
		if (!$cardStatus = $this->cache->get($this->core->getSessionID() . 'cardStatus')) {
			$this->load->model('coreapp/card_model');
			//$sQuery = "CALL sp_getcodelist('CARDSTAT')";
			
			//$result = $db->query( $sQuery );
			$result = $this->card_model->getCardStatus();
			$cardStatus = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'cardStatus', $cardStatus, CACHE_TTL);
		}
		
		/*
		 * Output
		 */
		$aaData = array();
		foreach ($rResult->result_array() as $aRow) {
			/*$row = array();
			for ( $i = 0; $i<count($aColumns); $i++ )
			{
				if ( $aColumns[$i] === 'version' )
				{
					//Special output formatting for 'version' column
					$row[] = ($aRow[ $aColumns[$i] ] === 0) ? '-' : $aRow[ $aColumns[$i] ];
				}
				else if ( $aColumns[$i] !== ' ' )
				{
					//General output
					$row[] = $aRow[ $aColumns[$i] ];
				}
			}*/
			
			//card type
			foreach ($cardType as $stat) {
				if ($aRow['accttype'] === $stat['accttype']) {
					$cardTypeDesc = $stat['description'];
					break;
				} else {
					$cardTypeDesc = 'Unknown';
				}
			}
			
			//card status
			foreach ($cardStatus as $stat) {
				if(isset($stat['status'])) {
				if (intval($aRow['status']) == intval($stat['status'])) {
					$cardStatDesc = $stat['description'];
					break;
				} else {
					$cardStatDesc = 'Unknown';
				}
				}
			}

			$cardStatDesc = isset($cardStatDesc) ? $cardStatDesc : NULL;
			
			$aaData[] = array(
				$aRow['prkey'],
				$aRow['cifseqno'] !== '0'? $aRow['cifseqno'] .': '. $aRow['lastname'] .', '. $aRow['firstname'] .' '. $aRow['middlename'] : 'Generic',
				$cardTypeDesc,
				$aRow['dtlastact'] ? $this->core->formatDate('F j, Y h:i:s A', $aRow['dtlastact']) : 'None',
				$cardStatDesc,
				($this->core->isCoreEncrypt() ? $aRow['TokenID'] : '')
			);
		}

		/*if ($db->trans_status() === FALSE) {
			$db->trans_rollback();
		} else {
			$db->trans_commit();
		}*/

		echo json_encode( array(
			'sEcho' => intval($_GET['sEcho']),
			'iTotalRecords' => $iTotal,
			'cardstatus' => $cardStatus,
			'iTotalDisplayRecords' => $iFilteredTotal,
			'aaData' => $aaData//,
			//'franz' => $franz
		));
	}
	
	function cache()
	{
		$this->load->model('coreapp/card_model');
		//$this->load->library('session');
		$this->load->library('shortxml');
		
		$session = $this->session;
		$core	 = $this->core;
		$card	 = $this->card_model;
		$xml 	 = $this->shortxml;
		$input 	 = $this->input;
		
		$branchID = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->checkLogin($userAudit, $sessionID);
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
				
		if ($row['errno'] === '8') {
			echo json_encode(array(
				'success' => FALSE,
				'message' => $row['errmsg'],
				'errorno' => $row['errno']
			));
			exit();
		}

		$details = array();
		
		$cardNo 	 = $input->post('number', TRUE);
		$rOnly 		 = $input->post('readonly', TRUE);
		$module 	 = $input->post('module', TRUE);
		$tokenid 	 = $input->post('tokenid', TRUE);
		$coreencrypt = $this->core->isCoreEncrypt();

		if ($coreencrypt) {
			$cardNo = $tokenid;
		}
		
		$result = $card->getCardInfo($coreencrypt, $cardNo, $branchID, $ipAddress, $workstation, $userAudit);
		
		$row 	 = $result->row_array();
		$errNo 	 = $row['errno'];
		$errMsg  = $row['errmsg'];
				
		$xml->setXML($row['acctxml']);
		
		$acctno = $xml->getValue('ACCTNO');
		$acct   = $xml->getValue('ACCT');
		
		if ($acctno === 'NONE') {
			$primay = $acctno;
		} else {
			$primay = $acct .'-'. $acctno;
		}

		$_SESSION['cardInfo'] = array(
			'cardNo'		=> $row['prkey'] ? $row['prkey'] : NULL,
			'cifseqno'		=> $row['cifseqno'],
			'brseqno'		=> $row['brseqno'],
			'brname'		=> $row['brname'],
			'prseqno'		=> $row['prseqno'],
			'cardBIN'		=> $cardNo,
			'custName' 		=> ($row['cifseqno'] == TRUE ? $row['lname'] .', '. $row['fname'] .' '. $row['mname'] : '***GENERIC***'), //if cifseqno is NULL then name = ***GENERIC***
			'cardStatus' 	=> $row['status'],
			'cardStatDesc'	=> strtoupper($row['statdesc']),
			'cardType' 		=> $row['acctdesc'],
			'dateInitIssue' => $core->formatDate('F j, Y g:i A', $row['dtenroll']),
			'issueCount' 	=> $row['issuecnt'],
			'dateActivated' => $core->formatDate('F j, Y g:i A', $row['dtactive']),
			'dateExpiry' 	=> $core->formatDate('F j, Y', $row['dtexpire']),
			'lastActivity' 	=> $core->formatDate('F j, Y g:i A', $row['lastact']),
			'primary'		=> $primay,
			'allows'		=> $row['allows'],
			'acctType'		=> $row['accttype'],
			'pinctr'		=> $row['pinctr'],
			'pinctrmax'		=> $row['pinctrmax'],
			'readonly'		=> ($module == 'cardissuanceapproval' ? TRUE : FALSE),
			'module'		=> $module,
			'xml1'			=> $row['xml1'] ? $row['xml1'] : NULL
		);
		
		//$session->unset_userdata('cardInfo');
		//$session->set_userdata($info);
		
		echo json_encode(array(
			'success' => TRUE,
			'cardinfo' => $_SESSION['cardInfo'],
			'params' => $tokenid.'-'.$branchID.'-'.$ipAddress.'-'.$workstation.'-'.$userAudit
		));
	}
	
	function getcardlist()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->library('zip');
		
		$card 	   = $this->card_model;
		$core	   = $this->core;
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->checkLogin($userAudit, $sessionID);
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();

		
		if ($row) {		

			$result = $card->exportcardlist(); 
			$result = $result->result_array();

			$data1 = '';
			foreach ($result AS $row) {
				$card = $row['cardno'];
				$customer = $row['custname'];
				$branch = $row['brname'];
				$refno = $row['refno'];

				$data1 .= $card.','.$customer.','.$branch."\r\n";
			}

			$result->free_result();
			$result->next_result();

			$result = $card->exportcardacct(); 
			$result = $result->result_array();

			$refno = '';
			$data2 = '';
			foreach ($result AS $row) {
				$card = $row['cardno'];
				$acctref = $row['acct'];

				$acctx = $card->getacctdtlx($acct);
				$rowx = $acctx->row_array();

				$acctno = $rowx['acctno'];
				if($refno != $row['refno']) {
					$data2 .= "[".$refno."] :\r\n";
				} else {
					$data2 .= "= ".$acctno."\r\n";
				}
				$refno = $row['refno'];
			}

			$data = array(
				'cardlist'.date("Ymd").'csv' => $data1,
				'cardacct'.date("Ymd").'txt' => $data2
				
			);
			
			$this->zip->add_data($data);
			$this->zip->download('CARDLIST'. date("Ymd") .'.zip'); 
		} else {
			$this->load->helper('url');
			redirect('#welcome');
		}
	}
}