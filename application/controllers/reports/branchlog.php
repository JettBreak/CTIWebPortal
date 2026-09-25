<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BranchLog extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(REPBRANCHLOG_NO);
		
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
		$this->load->model('coresys/reports_model');
		$reports = $this->reports_model;
		
		//branches combobox
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$branchList = NULL;
		foreach ($branches as $row) {
			$branchList .= '<option value="'. $row['brseqno'] .'" brname="'. $row['brname'] .'">'. $row['brname'] .'</option>';
		}
		
		$data['branches'] = NULL;
		if ($this->core->canRep()) {
			$data['branches'] = '<tr>
				<td><label for="branchList">Branch:</label></td>
				<td><select name="branch" id="branchList" style="width:170px">
						<option value="0" brname="ALL BRANCHES">ALL</option>
						'. $branchList .'
					</select></td>
			</tr>';
		}
		//$result = $reports->getProcessList();
		$result = $reports->getTrxListForReport();
		
		$forISSOnly = array(
			990200, 990201, 990202, 990203, 990204, //account mgmt
			990411, 990412, 990413, //allows setup
			990321, //add bills subscriber
			990601, 990602, 990603, //area mgmt
			990320, //bills payment list
			990610, 990611, 990612, //branch mgmt
			990312, //cancel order history
			990306, 990307, 990309, 990315, 990314, 990404, 990318, 990310, 990305, 990304, 990303, 990308,
			990401, 990402, 990403, 990302, 990300, 990316, 990004, 990330, 990322, 990340, 990341, 990342,
			990301, 990313, 990331, 990317, //card mgmt
			990101, 990100, 990103, //cust mgmt
			990503, 990550//reports
		);
		
		
		$data['procList'] = NULL;
		foreach ($result->result_array() as $row) {
			$trxcode = $row['trxcode'];
			if ($_SESSION['inst']['appType'] === 'ACQ') {
				if ( in_array($trxcode, $forISSOnly) ) {
					continue;
				}
			}
			
			$data['procList'] .= '<option value="'. $trxcode .'">'. $row['description'] .'</option>';
		}
		
		if ($result->num_rows() === 0) {
			$this->load->helper('url');
			redirect('/pdfviewer/norecord');
			exit();
		}
		
		$data['date'] = date('m/d/Y');
		$this->load->view('reports/branchlog', $data);
	}
	
	function preview()
	{
		$this->load->model('coresys/reports_model');
		$this->load->library('core');
		$this->load->library('shortxml');
		$this->load->library('shortxmr');
		$this->load->library('pdf');
		$this->load->helper('url');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$xml 	 = $this->shortxml;
		$xmr 	 = $this->shortxmr;
		$input	 = $this->input;
		$pdf 	 = $this->pdf;		
		
		$core->checkUserAllows(REPBRANCHLOG_NO);
		
		$userName = $core->getUserName();
		$instName = $core->getInstName();
		$branchName = $core->getBranchName();
		
		if ($core->canRep()) {
			$brseqno = $this->input->post('branch', TRUE);
			$branchName = $this->input->post('branchName', TRUE);
		} else {
			$brseqno = $core->getBranchID();
			$branchName = $core->getBranchName();
		}
		
		$procDate	= $core->formatDate('Y-m-d', $input->post('procDate', TRUE));
		$procStats	= $input->post('procStats', TRUE);
		$trxcode	= $input->post('trxcode', TRUE);
		
		$result = $reports->getBranchLog($brseqno, $procDate, $procStats, $trxcode);
		
		if ($result->num_rows() === 0) {
			redirect('/pdfviewer/norecord');
			exit();
		}
		
		$currentDate = $core->formatDate('F j, Y', $input->post('procDate', TRUE));
		$dateSaveFormat = $core->formatDate('mdY', $currentDate);
		
		$data = NULL;
		foreach ($result->result_array() as $row) {
			$msgtype = $row['msgtype'];
			$xml->setXML($row['xml1']);
			$xmr->setXMR($row['xml1']);
			$trxcode = $row['trxcode'];
			
			$detail = NULL;
			if ($msgtype === '43') { //voided transactions
				switch ($trxcode)
				{
					case '990100':
						$detail = 'Customer: '. $xml->getValue('LN') .', '. $xml->getValue('FN') .' '. $xml->getValue('MN');
						break;
					case '990200';
					case '990202';
					case '990201';
					case '990203';
					case '990204';
						$detail = 'Account: '. $row['acct1'];
						break;	
					case '990302';
						$detail = 'Old Card: '. $xml->getValue('PRKEY');
						break;
					case '990301';
					case '990305';
					case '990306':
					case '990307':
					case '990309':
					case '990321':
					case '990322':
					case '990401':
					case '990402':
					case '990403':
					case '990404':
					case '990405':
						$detail = 'Card: '. $xml->getValue('PRKEY');
						break;
					default:
						break;
				}
				$detail .= '<br />Error ['. $row['sysvcode'] .']: '. $row['shortdescription'];
			} else {
				switch ($trxcode)
				{
					case '990100':
					case '990101':
					case '990102':
						//if search by CIFNO 
						$custDetail = NULL;
						if ($xml->getValue('ID') !== '') {
							$custDetail = '#'. $xml->getValue('ID') .' ';
						}
						$custDetail .= $xml->getValue('LN') .', '. $xml->getValue('FN') .' '. $xml->getValue('MN');
						$detail = 'Customer: '. $custDetail;
						break;
					case '990200':
					case '990202':
						$detail = 'Account: '. $xml->getValue('AC');
						break;
					case '990201':
						$detail = 'Account: '. $xml->getValue('AC') .' '. $xml->getValue('ACTYPE') .' '. $xml->getValue('ACOWNER'); 
						break;
					case '990203':
					case '990204':
						//$detail = 'Account: '. $xml->getValue('ACTYPE') .' '. $xml->getValue('LN') .', '. $xml->getValue('FN') .' '. $xml->getValue('MN');
						$detail = 'Account: '. $row['acct1'];
						break;
					case '990301':
					case '990305':
						$detail = 'Card: '. $xml->getValue('PRKEY')
								.'<br />Customer: '. $xml->getValue('CUSTNAME');
						break;
					case '990302':
						$detail = 'Old Card: '. $xml->getValue('PRKEY')
							.'<br />New Card: '. $xml->getValue('PRKEY2');
							//.'<br />Remarks: '. $xml->getValue('REMARKS');
						break;
					case '990303':
						$detail = 'Quantity: '. $xml->getValue('ORQTY') 
								.' Card Type: '. $xml->getValue('CTYPE') .' - '. $xml->getValue('CTYPENAME');
						if ($xml->getValue('CUSTNAME') !== '') {
							$detail .= '<br />Customer: '. $xml->getValue('CUSTNAME');
						}
						break;
					case '990306':
					case '990307':
					case '990404':
						$detail = 'Card: '. $xml->getValue('PRKEY')
								.'<br/ >Account: '. $row['acct1'] .' - '. $xml->getValue('ACTYPE');
						break;
					case '990309':
						$detail = 'Card: '. $xml->getValue('PRKEY') .' Status: '. $xml->getValue('STATUS') .' - '. $xml->getValue('STATDESC');
						break;
					case '990312':
						$detail = 'Order No.: '. $xml->getValue('ORDERNO');
						break;
					case '990313':
						$detail = 'Order No.: '. $xml->getValue('ORDERNO');
						break;
					case '990330':
					case '990331':
						$detail = 'Card: '. $xml->getValue('PRKEY')
						.' Mobile No.: '. $xml->getValue('MOBILENO');
						break;
					case '990321':
					case '990322':
					case '990401':
					case '990402':
					case '990405':
						$detail = 'Card: '. $xml->getValue('PRKEY') .' Bill:'. $xml->getValue('BILLID') .' '. $xml->getValue('BILLSUBNO') .' '. $xml->getValue('BILLNAME');
						break;
					case '990403':
						$detail = 'Card: '. $xml->getValue('PRKEY') .' Remarks: '. $xml->getValue('REMARKS');
						break;
					case '990510':
					case '990511':
					case '990512':
					case '990513':
					case '990514':
					case '990515':
					case '990516':
					case '990517':
					case '990518':
					case '990519':
						$detail = 'LUNO: '. $xml->getValue('LUNO');
						break;
					case '990550':
					case '990551':
						$detail = 'Terminal Code: '. $xml->getValue('TERMCODE');
						break;
					case '990552':
					case '990553':
						$detail = 'Terminal Code: '. $xml->getValue('TERMCODE').
									', Issue No. '. $xml->getValue('ISSUENO');
						break;
					default:
						$detail = '-';
						break;
				}
			}
			
			$desc = NULL;
			if (!$row['description']) {
				switch ($trxcode) {
					case '990502':
						$desc = 'TRANSACTION DOWNLOAD RECORD';
						break;
					case '990501':
						$desc = 'TRANSACTION DOWNLOAD LIST';
						break;
					case '990322':
						$desc = 'REMOVE BILLS SUBSCRIBER';
						break;
					case '990505':
						$desc = 'PROCESS DOWNLOAD RECORD';
						break;
					case '990504':
						$desc = 'PROCESS DOWNLOAD LIST';
						break;
					case '990503':
						$desc = 'PREVIEW CARD INVENTORY';
						break;
					case '990330':
						$desc = 'MOBILE REGISTRATION';
						break;
					case '990001':
						$desc = 'LOGIN';
						break;
					case '990102':
						$desc = 'EDIT CUSTOMER ENTRY';
						break;
					case '990048':
						$desc = 'GET TRANSACTION INFO';
						break;
					case '990331':
						$desc = 'DELETE MOBILE LINK';
						break;
					case '990100':
						$desc = 'CUSTOMER SEARCH';
						break;
					case '990004':
						$desc = 'CHANGE CLIENT PASSWORD';
						break;
					case '990300':
						$desc = 'CARD SEARCH';
						break;
					case '990302':
						$desc = 'CARD REPLACEMENT REQUEST';
						break;
					case '990403':
						$desc = 'CARD PIN RESET REQUEST';
						break;
					case '990402':
						$desc = 'CARD PIN CHANGE';
						break;
					case '990401':
						$desc = 'CARD PIN ACTIVATION';
						break;
					case '990303':
						$desc = 'CARD ORDER REQUEST';
						break;
					case '990304':
						$desc = 'CARD ORDER LIST';
						break;
					case '990305':
						$desc = 'CARD NUMBER INFO';
						break;
					case '990309':
						$desc = 'CARD CHANGE STATUS';
						break;
					case '990405':
						$desc = 'CARD BATCH PIN CHANGE REQUEST';
						break;
					case '990301':
						$desc = 'CARD ACTIVATION';
						break;
					case '990307':
						$desc = 'CARD ACCOUNT REMOVE';
						break;
					case '990306':
						$desc = 'CARD ACCOUNT ADD';
						break;
					case '999100':
						$desc = 'BILLS PAYMENT CHECK DIGIT VALIDATION';
						break;
					case '990320':
						$desc = 'BILLS PAYMENT LIST';
						break;
					case '990406':
						$desc = 'BATCH PAYROLL CREDIT';
						break;
					case '990321':
						$desc = 'ADD BILLS SUBSCRIBER';
						break;
					case '990203':
						$desc = 'ACCOUNT VERIFY';
						break;
					case '990201':
						$desc = 'ACCOUNT INFO';
						break;
					case '990204':
						$desc = 'ACCOUNT IMPORT';
						break;
					case '990205':
						$desc = 'ACCOUNT VERIFICATION LIST';
						break;
					case '990310':
						$desc = 'CARD ORDER HISTORY';
						break;
					case '990311':
						$desc = 'UPDATE CARD ORDER';
						break;
					case '990312':
						$desc = 'CANCEL CARD ORDER';
						break;
					case '990313':
						$desc = 'DELETE CARD ORDER';
						break;
					case '990314':
						$desc = 'CARD GENERATION LIST';
						break;
					case '990315':
						$desc = 'CARD EMBOSSING';
						break;
					case '990510':
						$desc = 'TERMINAL UP';
						break;
					case '990511':
						$desc = 'TERMINAL DOWN';
						break;
					case '990512':
						$desc = 'TERMINAL RESET';
						break;
					case '990513':
						$desc = 'GET TERMINAL INFO';
						break;
					case '990514':
						$desc = 'GET SUPPLY COUNTERS';
						break;
					case '990515':
						$desc = 'SYNC DATE/TIME';
						break;
					case '990516':
						$desc = 'TAG TERMINAL FOR DEPLOYMENT';
						break;
					case '990517':
						$desc = 'TAG TERMINAL UNDER MAINTENANCE';
						break;
					case '990518':
						$desc = 'TERMINAL LOAD';
						break;
					case '990519':
						$desc = 'GENERATE KEY';
						break;
					case '990601':
						$desc = 'CREATE AREA';
						break;
					case '990602':
						$desc = 'EDIT AREA';
						break;
					case '990603':
						$desc = 'DELETE AREA';
						break;
					case '990604':
						$desc = 'CREATE DEPARTMENT';
						break;
					case '990605':
						$desc = 'EDIT DEPARTMENT';
						break;
					case '990606':
						$desc = 'DELETE DEPARTMENT';
						break;
					case '990610':
						$desc = 'CREATE BRANCH';
						break;
					case '990611':
						$desc = 'EDIT BRANCH';
						break;
					case '990612':
						$desc = 'DELETE BRANCH';
						break;
					case '990613':
						$desc = 'INSERT WEB USER';
						break;
					case '990316':
						$desc = 'CARD VERIFICATION LIST';
						break;
					case '990317':
						$desc = 'VERIFY CARD';
						break;
					case '990103':
						$desc = 'DELETE CIF';
						break;
					case '990340':
						$desc = 'RESET PIN RETRY COUNT';
						break;
					case '990341':
						$desc = 'SET ONLINE DEF CARD LIMIT';
						break;
					case '990342':
						$desc = 'SET PIN RETRY MAX CTR';
						break;
					case '990550':
						$desc = 'GET ISSUE LIST';
						break;
					case '990551':
						$desc = 'INSERT ISSUE LOG';
						break;
					case '990552':
						$desc = 'UPDATE ISSUE LOG';
						break;
					case '990553':
						$desc = 'REMOVE ISSUE LOG';
						break;
					case '990711':
						$desc = 'NEW SERVICE CHARGE';
						break;
					case '990712':
						$desc = 'UPDATE SERVICE CHARGE';
						break;
					case '990713':
						$desc = 'REMOVE SERVICE CHARGE';
						break;
					case '990700':
						$desc = 'TEMPLATE DELETE';
						break;
					case '990345':
						$desc = 'SET DEFAULT ONLINE LIMIT';
						break;
					case '990614':
						$desc = 'UPDATE USER';
						break;
					case '990410':
						$desc = 'SET ACCOUNT FORMAT';
						break;	
					default:
						$desc = 'UNKNOWN: '.$trxcode;
						break;
				}
			} else {
				$desc = $row['description'];
			}
			
			$time = $core->formatDate('h:i:s A', $row['dtlog']);
			$refNo = $row['logseqno'];
			$status = $row['stat'];
			$userID = $row['acqcode'];
			
			if ($xml->getValue('OVERUSER') !== '') {
				$overUser = $xml->getValue('OVERUSER');
				$align = NULL;
			} else {
				$overUser = '-';
				$align = 'align="center"';
			}

			if ($detail === '-') {
				$detailAlign = 'align="center"';
			} else {
				$detailAlign = NULL;
			}
			
			$workstation = $row['tpkey'];
			
			$data .= '<tr>'.
				'<td width="200">'. $time .'</td>'.
				'<td width="150">'. $refNo .'</td>'.
				'<td width="500">'. $desc .'</td>'.
				'<td width="200">'. $status .'</td>'.
				'<td width="200">'. $userID .'</td>'.
				'<td width="200"'. $align .'>'. $overUser .'</td>'.
				'<td width="350">'. $workstation .'</td>'.
				'<td width="800"'. $detailAlign .'>'. $detail .'</td>'.
			'</tr>';
		}
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('Branch Audit Log Report');
		$pdf->SetSubject('Card');
		$pdf->SetKeywords(NULL);
		
		// set default header data
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		$pdf->SetHeaderData(NULL);
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setUser($userName);
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		// ---------------------------------------------------------
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('helvetica', '', 7, '', true);
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage('L', 'Letter');
		
		// Set some content to print
		// Set some content to print
		$html = '<style>
		h1, h2, h3, h4, h5 {
			text-align: center;
		}
		th {
			text-transform: uppercase;
			font-weight: bold;
			text-align: center;
		}
		</style>
		<h2>'.$instName.'</h2>
		<h1>BRANCH AUDIT LOG REPORT</h1>
		<h3>'. $branchName .'</h3>
		<h4>'. $currentDate .'</h4>
		<table cellspacing="18" width="100%">
			<tr>
				<th width="200">TIME</th>
				<th width="150">REF. NO.</th>
				<th width="500">PROCESS TYPE</th>
				<th width="200">STATUS</th>
				<th width="200">USER</th>
				<th width="200">OVERRIDE</th>
				<th width="350">WORKSTATION</th>
				<th width="800">DETAILS</th>
			</tr>
			'. $data .'
		</table>';
		
		// Print text using writeHTMLCell()
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->Output('branchlog_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file branchlog.php */
/* Location: ./application/reports/branchlog.php */