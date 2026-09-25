<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POSLoanPayAcqReport extends CI_Controller {	

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(OTHERCARDHOLDERATOURTERM_NO);
	}
	
	function preview()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/reports_model');
		$this->load->library('pdf');
		$this->load->library('shortxml');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$pdf 	 = $this->pdf;		
		$xml	 = $this->shortxml;
		
		$userName  = $core->getUserName();
		$instName  = $core->getInstName();
		$finswitch = $core->getFINSWITCH();
		
		if ($core->canRep()) {
			$branchCode = $this->input->post('branch', TRUE);
			$branchName = $this->input->post('branchName', TRUE);
		} else {
			$branchCode = $core->getBranchCode();
			$branchName = $core->getBranchName();
		}
		
		$userAudit  = $core->getUserID();
		$sessionID  = $core->getSessionID();
		
		$termType = $this->input->post('termType', TRUE);
		$dtFrom = $core->formatDate('Y-m-d', $this->input->post('dtFrom', TRUE));
		$dtTo = $core->formatDate('Y-m-d', $this->input->post('dtTo', TRUE));
		$reportLog = $this->input->post('reports', TRUE);
		
		if ($dtFrom !== $dtTo) {
			//if same month, outputs: January 1 - 10, 2011
			if ($core->formatDate('Y-m', $dtFrom) === $core->formatDate('Y-m', $dtTo)) {
				$reportDate = $core->formatDate('F j', $dtFrom) .' - '. $core->formatDate('j, Y', $dtTo);
			} else {
				$reportDate = 'From '. $core->formatDate('F j, Y', $dtFrom) .' to '. $core->formatDate('F j, Y', $dtTo);
			}
			$dateSaveFormat = $core->formatDate('mdY', $dtFrom) .'-'. $core->formatDate('mdY', $dtTo);
		} else {
			$reportDate = $core->formatDate('F j, Y', $dtFrom);
			$dateSaveFormat = $core->formatDate('mdY', $dtFrom);
		}
		
		$result = $reports->getPOSLoanPayment($reportLog, $branchCode, $finswitch, $dtFrom, $dtTo);
		$tpt = $result->result_array();
		
		//echo '<pre>'.print_r($tpt).'</pre>';
		
		//init array
		$data = array();
		if ($result->num_rows() === 0) {
			$data[' '] = array(
					'content' => '<tr><td></td></tr><tr><td colspan="16" align="center"><h3><font color="red">"No activity for the day"</font></h3></td></tr>',
					'wdl' => 0,
					'trn' => 0,
					'pay' => 0,
					'dep' => 0,
					'adv' => 0,
					'pos' => 0,
					'rvrsl' => 0
			);
		}
		
		if (!$acctTypes = $this->cache->get($this->core->getSessionID() . 'acctTypes')) {
			$result->free_result();
			$result->next_result();
			
			$this->load->model('coreapp/card_model');	
			$result = $this->card_model->getAccountType();
			$acctTypes = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'acctTypes', $acctTypes, CACHE_TTL);
		}
		
		$wdl = 0;
		$trn = 0;
		$pay = 0;
		$dep = 0;
		$adv = 0;
		$load= 0;
		$ibftreq = 0;
		$ibftwdl = 0;
		$ibfttrn = 0;
		
		foreach ($tpt as $row) {
			//echo '<pre>'.print_r($row).'</pre>';
			$data[$row['termcode']] = array(
					'content' => '',
					'wdl' => 0,
					'trn' => 0,
					'pay' => 0,
					'dep' => 0,
					'adv' => 0,
					'pos' => 0,
					'rvrsl' => 0
			);
		}
		
		$total = array (
			'wdlSubTotal' => 0,
			'trnSubTotal' => 0,
			'paySubTotal' => 0,
			'depSubTotal' => 0,
			'advSubTotal' => 0,
			'posSubTotal' => 0,
			'rvrslSubTotal' => 0
			);
			
		$contentall = '';
		
		
		foreach ($tpt as $row) {
			$xml->setXML($row['xml1'] . $row['xml2'] . $row['xml3'] . $row['xml4'] . $row['xml5'] . $row['xml6']);
			$xml->setXML($row['xml1'] . $row['xml2']);
			
			$logkey = str_pad($row['logseqno'], 8, '0', STR_PAD_LEFT);
			//$traceNo = $row['chseqno'];
			
			//acct1
			$acct1 = $row['acct1'];
			$a1Align = NULL;
			if (!$acct1) {
				$acct1 = '-';
				$a1Align = ' align="center"';
			}
			//end
			
			//acct2
			$acct2 = $row['acct2'];
			$a2Align = NULL;
			if (!$acct2) {
				$acct2 = '-';
				$a2Align = ' align="center"';
			}
			//end
			
			//get account code (TY)
			$acctType = $row['accttype1'];
			$acctDesc = NULL;
			foreach ($acctTypes as $r) {
				if ($r['accttype'] === $acctType) {
					$acctDesc = $r['acctcode'];
				}
			}
			//end
			
			//remarks
			$remarks = $row['shortdescription'];
			$rAlign = NULL;
			if (!$remarks) {
				$remarks = '-';
				$rAlign = ' align="center"';
			}
			//end
			
			$acqCode = $xml->getVALUE('ACQCODE_MNEM');
			$acqAlign = NULL;
			if ($acqCode === 'null') {
				$acqCode = '-';
			}
			
			$amtReq = $row['amtreq'];
			$amtAth = $core->currency($row['amtath']);
			
			if (!array_key_exists($row['termcode'], $data)) {
				$data[$row['termcode']] = NULL;
			}
			
			//rules for ELD 8882 issue
			$mnemonic = $row['mnemonic'];
			switch ($mnemonic) {
				case 'IBFTSA':
				case 'IBFTBTSA':
				case 'IBFTWDSA':
				case 'IBFTTRSA':
				case 'WDLSA':
					if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
						$mnemonic = 'LOADSA';
					}
					break;
				case 'IBFTCA':			
				case 'IBFTBTCA':
				case 'IBFTWDCA':
				case 'IBFTTRCA':
				case 'WDLCA':
					if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
						$mnemonic = 'LOADCA';
					}
					break;
			}
			//end
			
			// if ACQ is true
				if ($row['termtype'] === 'ATM' && $row['chname'] === $finswitch) {
					
					$traceNo = str_pad($row['chseqno'], 6, '0', STR_PAD_LEFT);
					$sequenceNo = str_pad($row['tpseqno'], 6, '0', STR_PAD_LEFT);
					
				} else {
					
					$traceNo = str_pad($row['tpseqno'], 6, '0', STR_PAD_LEFT);
					$sequenceNo = str_pad($row['chseqno'], 6, '0', STR_PAD_LEFT);
					
				}
			
			$content = '<tr>
				<td width="160">'. $logkey .'</td>
				<td width="120">'. $traceNo .'</td>
				<td width="170">'. $sequenceNo .'</td>
				<td width="130">'. $core->formatDate('H:i:s', $row['dtlog']) .'</td>
				<td width="130">'. $core->formatDate('m/d/y', $row['dtlog']) .'</td>
				<td width="100">'. $row['tpluno'] .'</td>
				<td width="320">'. substr($row['prkey1'], 0, 6).'- '.substr($row['prkey1'], 6).'</td>
				<td width="70" align="center">'. $row['msgtype'] .'</td>	
				<td width="190">'. $mnemonic .'</td>	
				<td width="250" '. $a1Align .'>'. substr($acct1, -14) .'</td>
				<td width="70" align="center">'. $acctDesc .'</td>
				<td width="90">'. $row['sysvcode'] .'</td>	
				<td width="300" '. $rAlign .'>'. $remarks .'</td>		
				<td width="300" align="center">'. $acqCode .'</td>		
				<td width="150" align="right">'. $core->currency($amtReq) .'</td>
				<td width="150" align="right">'. $amtAth .'</td>
			</tr>';
			
			$addAmt = floatval($row['amtath']);
			switch ($row['trxtype1']) {
				case 'WDL':
					//$wdl += $addAmt;
					$data[$row['termcode']]['wdl'] += $addAmt;
					break;
				case 'TRN':
					//$trn += $addAmt;
					$data[$row['termcode']]['trn'] += $addAmt;
					break;
				case 'PAY':
					//$pay += $addAmt;
					$data[$row['termcode']]['pay'] += $addAmt;
					break;
				case 'DEP':
					//$dep += $addAmt;
					$data[$row['termcode']]['dep'] += $addAmt;
					break;
				case 'ADV':
					//$adv += $addAmt;
					$data[$row['termcode']]['adv'] += $addAmt;
					break;
				case 'POS':
					//$adv += $addAmt;
					$data[$row['termcode']]['pos'] += $addAmt;
					break;
				case 'IBFT':
					switch ($mnemonic) {
						case 'IBFTSA':
						case 'IBFTCA':
						case 'IBFTBTSA':
						case 'IBFTBTCA':
							//$ibftreq += $addAmt;
							$data[$row['termcode']]['ibftreq'] += $addAmt;
							break;
						case 'IBFTWDSA':
						case 'IBFTWDCA':
							//$ibftwdl += $addAmt;
							$data[$row['termcode']]['ibftwdl'] += $addAmt;
							break;
						case 'IBFTTRSA':
						case 'IBFTTRCA':
							//$ibfttrn += $addAmt;
							$data[$row['termcode']]['ibfttrn'] += $addAmt;
							break;
					}
					//rules for ELD 8882 issue
					if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
						$data[$row['termcode']]['load'] += $addAmt;
					}
					//end	
					
					break;
			}
			$data[$row['termcode']]['content'] .= $content;
		}
		
		/*$totals = '<tr align="right">
			<td><b>TOTAL:</b></td>
			<td>'. $core->currency($wdl) .'</td>
			<td>'. $core->currency($trn) .'</td>
			<td>'. $core->currency($pay) .'</td>
			<td>'. $core->currency($dep) .'</td>
			<td>'. $core->currency($adv) .'</td>
			<td>'. $core->currency($load) .'</td>
			<td>'. $core->currency($ibftreq) .'</td>
			<td>'. $core->currency($ibftwdl) .'</td>
			<td>'. $core->currency($ibfttrn) .'</td>
		</tr>';*/
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('POS Loan Payment, Acquirer Report');
		$pdf->SetSubject(NULL);
		$pdf->SetKeywords(NULL);
		
		// set default header data
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		$pdf->SetHeaderData(NULL);
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setUser($core->getUserName());
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
		
		foreach ($data as $key => $d) {
			$pdf->AddPage('L', 'Letter');
			
			// Set some content to print
			$html = '<style>
			
			th {
				text-transform: uppercase;
				font-weight: bold;
				text-align: left;
			}
			</style>
			<h2>'.$instName.'</h2>
			<h1>POS LOAN PAYMENT, ACQUIRER REPORT : '.$key.'</h1>
			<h3>'. $branchName .'</h3>
			<h4>Transaction Date(s): '. $reportDate .'</h4>
			<table cellspacing="10" width="100%">
			<thead>
				<tr>
					<th width="160">LOGKEY</th>
					<th width="120">TRACE NO.</th>
					<th width="170">SEQUENCE NO.</th>
					<th width="130">TIME</th>
					<th width="130">DATE</th>
					<th width="100">LUNO</th>
					<th width="320">BANK ID / CARD #</th>
					<th width="70" align="center">TC</th>
					<th width="190">TRXCODE</th>
					<th width="250" align="center">ACCOUNT</th>
					<th width="70" align="center">TY</th>
					<th width="90" align="center">VCD</th>
					<th width="300" align="center">REMARKS</th>
					<th width="300" align="center">ACQUIRER</th>
					<th width="150">TRX AMT</th>
					<th width="150">AMT AUTH</th>
				</tr>
			</thead>
			<tbody>
				'. $d['content'] .'
			</tbody>
			<tfoot>
				<tr>
					<td colspan="14">&nbsp;</td>
				</tr>
			</tfoot>
			</table>';
			
			$html .= '<table cellspacing="18" width="100%">
			<thead>
				<tr align="center">
					<th align="right">&nbsp;</th>
					<th align="right">WITHDRAWAL</th>
					<th align="right">FUND TRANSFER</th>
					<th align="right">PAYMENTS</th>
					<th align="right">POS LOAN PAYMENT</th>
					<th align="right">CASH ADVANCE</th>
					<th align="right">REVERSAL</th>
				</tr>
			</thead>
			<tfoot>
				<tr align="right">
					<td><b>TOTAL:</b></td>
					<td>'. $core->currency($d['wdl']) .'</td>
					<td>'. $core->currency($d['trn']) .'</td>
					<td>'. $core->currency($d['pay']) .'</td>
					<td>'. $core->currency($d['pos']) .'</td>
					<td>'. $core->currency($d['adv']) .'</td>
					<td>'. $core->currency($d['rvrsl']) .'</td>
				</tr>
			</tfoot>
			</table>';
			// Print text using writeHTMLCell()
			
			/*<tr align="right">
					<td><b>SUB TOTAL:</b></td>
					<td>'. $core->currency($total['wdlSubTotal'] += $d['wdl']) .'</td>
					<td>'. $core->currency($total['trnSubTotal'] += $d['trn']) .'</td>
					<td>'. $core->currency($total['paySubTotal'] += $d['pay']) .'</td>
					<td>'. $core->currency($total['posSubTotal'] += $d['pos']) .'</td>
					<td>'. $core->currency($total['advSubTotal'] += $d['adv']) .'</td>
					<td>'. $core->currency($total['rvrslSubTotal'] += $d['rvrsl']) .'</td>
				</tr>*/
			
			$pdf->writeHTML($html, true, false, true, false, '');
		}
		$pdf->Output('posloanpayreport_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file postrxreport.php */
/* Location: ./application/reports/postrxreport.php */