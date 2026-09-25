<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Browse extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ACCNTMGMT_NO);

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
		
		
		//account types
		if (!$accountTypes = $this->cache->get($this->core->getSessionID() . 'accountTypes')) {
			$result = $this->card_model->getAccountType();
			
			$accountTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'accountTypes', $accountTypes, CACHE_TTL);
		}
		
		$data['accountTypeList'] = NULL;
		foreach ($accountTypes as $row) {
			$data['accountTypeList'] .= "'<option value=\"". $row['accttype'] ."\">". strtoupper($row['description']) ."</option>'+";
		}
		//end
		
		$prType = $this->input->post('termType', TRUE);
		$data['sessionExp'] = $this->core->getSessionExp();
		
		$this->load->view('accounts/browse', $data);
	}
	
	function getData()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$db = $this->load->database(DB1, TRUE);
		
		//$db->trans_begin();
		
		/* dt columns */
		$aColumns = array( 'a.prkey', 'a.cifseqno', 'a.prtype', 'a.dtlastact', 'a.status' );
		
		/* all columns */
		$sColumns = array( 'a.prkey', 'a.cifseqno', 'a.prtype', 'a.accttype', 'a.dtlastact', 'a.prseqno', 'a.status', 'b.firstname', 'b.middlename', 'b.lastname' );
		
		/* filter columns */
		$fColumns = array( 'a.prkey', 'a.cifseqno', 'a.dtlastact', 'a.prseqno', 'b.firstname', 'b.middlename', 'b.lastname' );
		
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
		
		/*$cardStatus = $_GET['cardStatus'];
		if ($cardStatus != 0) {
			$custom .= " AND a.status = ". $cardStatus;
		}*/
		$acctType = $_GET['acctType'];
		if ($acctType != 0) {
			$custom .= " AND a.accttype = ". $acctType;
		}
		
		//branch filter
		$brFilter = '';
		if (!$this->core->isHeadOffice()) {
			$brseqno = $this->core->getBranchID();
			$brFilter = "AND a.brseqno = ". $brseqno;
		}
		
		$sWhere = "WHERE (a.prtype = 'ACCT' ". $brFilter . $custom;//FRANZ
		if ( $_GET['sSearch'] !== '' )
		{
			$sWhere .= ") AND (";
			for ( $i = 0 ; $i < count($fColumns); $i++ )
			{
				$sWhere .= $fColumns[$i]. " LIKE '%". $db->escape_str( $_GET['sSearch'] ). "%' OR ";
			}
			$sWhere = substr_replace( $sWhere, '', -3 );
		}
		$sWhere .= ")";
		
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
			" FROM ". $sTable .
			" a LEFT JOIN customer b ON (a.cifseqno = b.cifseqno) ".
			$sWhere .
			$sOrder .
			$sLimit;
		$rResult = $db->query( $sQuery );
		
		/* Data set length after filtering */
		$sQuery = "SELECT FOUND_ROWS() as rows";
		$rResultFilterTotal = $db->query( $sQuery );
		$aResultFilterTotal = $rResultFilterTotal->row_array();
		$iFilteredTotal = $aResultFilterTotal['rows'];
		
		/* Total data set length */
		$sQuery = "SELECT COUNT(".$sIndexColumn.") as count".
			" FROM ". $sTable ." a WHERE (prtype = 'ACCT'". $brFilter .")";
		$rResultTotal = $db->query( $sQuery );
		$aResultTotal = $rResultTotal->row_array();
		$iTotal = $aResultTotal['count'];
		
		//account type
		if (!$accountTypes = $this->cache->get($this->core->getSessionID() . 'accountTypes')) {
			$sQuery = "CALL sp_getaccounttype()";
						
			$result = $db->query( $sQuery );
			$accountTypes = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'accountTypes', $accountTypes, CACHE_TTL);
		}
		
		//account status
		if (!$accountStatus = $this->cache->get($this->core->getSessionID() . 'accountStatus')) {
			$sQuery = "SELECT accttype, status, description FROM prstatus WHERE prtype = 'ACCT'";
			
			$result = $db->query( $sQuery );
			$accountStatus = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'accountStatus', $accountStatus, CACHE_TTL);
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
			
			//account type
			$accountTypeDesc = 'UNKNOWN';
			foreach ($accountTypes as $stat) {
				if ($aRow['accttype'] === $stat['accttype']) {
					$accountTypeDesc = $stat['description'];
					break;
				} else {
					$accountTypeDesc = 'UNKNOWN';
				}
			}
			
			//account status
			foreach ($accountStatus as $stat) {
				if ($aRow['status'] === $stat['status'] && $aRow['accttype'] === $stat['accttype']) {
					$accountStatDesc = $stat['description'];
					$accountStat = $stat['status'];
					break;
				} else {
					$accountStatDesc = 'Unknown';
					$accountStat = 0;
				}
			}
			
			$aaData[] = array(
				$aRow['prkey'],
				$aRow['cifseqno'] !== '0'? $aRow['cifseqno'] .': '. $aRow['lastname'] .', '. $aRow['firstname'] .' '. $aRow['middlename'] : 'Generic',
				$accountTypeDesc,
				$aRow['dtlastact'] ? $this->core->formatDate('F j, Y h:i:s A', $aRow['dtlastact']) : 'None',
				$accountStatDesc,
				$aRow['accttype'],
				$accountStat
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
			'iTotalDisplayRecords' => $iFilteredTotal,
			'aaData' => $aaData/*,
			'franz' => $franz*/
		));
	}
	
	function cache()
	{
		$this->load->model('coreapp/card_model');
		
		$accntNo = $this->input->post('number', TRUE);
		$accntType = $this->input->post('accntType', TRUE);
		
		if ($accntType == 'SAVINGS ACCOUNT') {
			$accntType = 10;	
		} elseif ($accntType == 'CHECKING ACCOUNT'){
			$accntType = 20;	
		}
		
		$result = $this->card_model->getAccountInfoByKey($accntNo, $accntType);
		
		//log
		$userID = $this->core->getUserID();
		$workstation = $this->core->getWorkstation();
		$ipAddress = $this->core->getIPAddress();
		$brseqno = $this->core->getBranchID();
		
		$logXML = '<AC>'. $accntNo .'</>'.
			'<IP>'. $workstation .'</>'.
			'<IPADDR>'. $ipAddress .'</>'.
			'<BRSEQN0>'. $brseqno .'</>';
		
		if ($result->num_rows() > 0) {
			$_SESSION['accountInfo'] = $result->row_array();
			
			$msgType = 41;
			$sysVCode = 0;
			
			$success = TRUE;
			$message = NULL;
		} else {
			//voided
			$msgType = 43;
			$sysVCode = 7101;
			
			$logXML .= '<SYSVMINI>ACCOUNT INVALID</>'.
				'<SYSVDESC>Account Not Found</>';
				
			$success = FALSE;
			$message = 'Account Not Found';
		}
		
		$result->free_result();
		$result->next_result();
		
		$this->card_model->insertLogclixx(
			$msgType, $brseqno, $sysVCode, $accntNo,
			$accntNo, $userID, $workstation, $logXML
		);
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
	
	function delete()
	{
		
		$this->load->model('coreapp/card_model');
		
		$acctNo 	 = $this->input->post('number', TRUE);
		$acctType	 = $this->input->post('accntType', TRUE);
		$brseqno     = $this->core->getBranchID();
		$ipaddress   = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit   = $this->core->getUserID();
		$sessionID   = $this->core->getSessionID();
	 	
 		$result = $this->card_model->removeAcct(
			$acctNo,
			$acctType,
			$ipaddress,
			$workstation,
			$userAudit,
			$sessionID
		);
		
		$this->session->unset_userdata('userOverride');
		$row = $result->row_array();
		
		if ($row['errno'] === '0') {
			$success = TRUE;
			$message = 'Account <strong>['. $acctNo .']</strong> removed successfully';
		} else {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}