<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DebitBillsTrx extends CI_Controller {	

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
		$this->load->model('coreapp/bpay_model');
		$this->load->library('pdf');
		$this->load->library('shortxml');
		
		$reports = $this->reports_model;
		$bpay = $this->bpay_model;
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
		
		$result = $reports->getBillsPaymentTransactions($reportLog, $branchCode, $dtFrom, $dtTo);
		$tpt = $result->result_array();
		
		//echo '<pre>'.print_r($result).'</pre>';
		
		//init array
		$data = array();	
		$contentall = '';
		
		if ($result->num_rows() === 0) {
			$data[' '] = array(
				'content' => '<tr><td></td></tr><tr><td colspan="16" align="center"><h3><font color="red">"No activity for the day"</font></h3></td></tr>',
				'wdl' => 0,
				'trn' => 0,
				'pay' => 0,
				'dep' => 0,
				'adv' => 0,
				'load'=> 0,
				'ibftreq' => 0,
				'ibftwdl' => 0,
				'ibfttrn' => 0
			);
			
			$contentall = $data[' ']['content'];
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
			$data[$row['termcode']] = array(
				'content' => '',
				'wdl' => 0,
				'trn' => 0,
				'pay' => 0,
				'dep' => 0,
				'adv' => 0,
				'load'=> 0,
				'ibftreq' => 0,
				'ibftwdl' => 0,
				'ibfttrn' => 0
			);
		}
		
		foreach ($tpt as $row) {
			$xml->setXML($row['xml1'] . $row['xml2'] . $row['xml3'] . $row['xml4'] . $row['xml5'] . $row['xml6']);
			
			//echo '<pre>'.print_r($row).'</pre>';
			$logkey = str_pad($row['logseqno'], 8, '0', STR_PAD_LEFT);
			//$traceNo = $row['chseqno'];
			
			//acct1
			$acct1 = $row['acct1'];
			$aAlign = NULL;
			if (!$acct1) {
				$acct1 = '-';
				$aAlign = ' align="center"';
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
			//end
			
			$amtReq = $row['amtreq'];
			$amtAth = $core->currency($row['amtath']);
			
			if (!array_key_exists($row['termcode'], $data)) {
				$data[$row['termcode']] = NULL;
			}
			
			$inst = $xml->getVALUE('INSTNAME');
			if ($inst === '') {
				
				$result->free_result();
				$result->next_result();
				
				$instcode = intval($row['instcode']);
				$result = $bpay->getInstDesc($instcode);
				$r = $result->row_array();
				
				if ($result->num_rows() === 0) {
					$inst = '';
				} else {
					$inst = $r['description'];
				}

			}
			
			$result->free_result();
			$result->next_result();
			
			$fitCode = intval($row['acqcode']);
			$result = $reports->getFitName($fitCode);
			$r = $result->row_array();
			
			if ($result->num_rows() === 0) {
				$fitName = '';
			} else {
				$fitName = $r['isomnem'];
			}

			$result->free_result();
			$result->next_result();

			
			$fitCode = intval($row['acqcode']);
			
			$subsNo = trim($xml->getVALUE('SUBSNO'));
			if ($subsNo === '') {
				$subsNo = $row['acct2'];
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
				<td width="200">'.$row['termcode'].'</td>
				<td width="150">'. $traceNo .'</td>
				<td width="220">'. $sequenceNo .'</td>
				<td width="150">'. $core->formatDate('H:i:s', $row['dtlog']) .'</td>
				<td width="150">'. $core->formatDate('m/d/y', $row['dtlog']) .'</td>
				<td width="420">'. substr($row['prkey1'], 0, 6).'- '.substr($row['prkey1'], 6).'</td>
				<td width="130">'. $fitName .'</td>
				<td width="90">'. $row['msgtype'] .'</td>	
				<td width="180">'. $mnemonic .'</td>		
				<td width="300" '. $aAlign .'>'. substr($acct1, -14) .'</td>
				<td width="120" align="center">'. $acctDesc .'</td>
				<td width="90">'. $row['sysvcode'] .'</td>
				<td width="500">'. $inst .'</td>
				<td width="350">'. $subsNo .'</td>
				<td width="150" align="right">'. $core->currency($amtReq) .'</td>
				<td width="150" align="right">'. $amtAth .'</td>
			</tr>';
			
			$addAmt = floatval($row['amtath']);
			
			$contentall .= $content;
			
			switch ($row['trxtype1']) {
				case 'WDL':
					//$data[$row['termcode']]['wdl'] += $addAmt;
					if (!in_array($mnemonic, array('ELOADSA', 'ELOADCA')) || intval($xml->getValue('IBFTPTR')) !== IBFTPTR) {
						$wdl += $addAmt;
					} else {
						$load += $addAmt;
					}
					break;
					//$data[$row['termcode']]['trn'] += $addAmt;
				case 'TRN':
					$trn += $addAmt;
					break;
					//$data[$row['termcode']]['pay'] += $addAmt;
				case 'PAY':
					$pay += $addAmt;
					break;
					//$data[$row['termcode']]['dep'] += $addAmt;
				case 'DEP':
					$dep += $addAmt;
					break;
					//$data[$row['termcode']]['adv'] += $addAmt;
				case 'ADV':
					$adv += $addAmt;
					break;
					//$data[$row['termcode']]['load'] += $addAmt;
				case 'LOAD':
					$load += $addAmt;
					break;
				case 'IBFT':
					switch ($mnemonic) {
						case 'IBFTSA':
						case 'IBFTCA':
						case 'IBFTBTSA':
						case 'IBFTBTCA':
							$ibftreq += $addAmt;
							//$data[$row['termcode']]['ibftreq'] += $addAmt;
							break;
						case 'IBFTWDSA':
						case 'IBFTWDCA':
							$ibftwdl += $addAmt;
							//$data[$row['termcode']]['ibftwdl'] += $addAmt;
							break;
						case 'IBFTTRSA':
						case 'IBFTTRCA':
							$ibfttrn += $addAmt;
							//$data[$row['termcode']]['ibfttrn'] += $addAmt;
							break;
					}
					//rules for ELD 8882 issue
					if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
						$data[$row['termcode']]['load'] += $addAmt;
					}
					//end	
					
					break;
			}
			//$data[$row['termcode']]['content'] .= $content;
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
		$pdf->SetTitle('Debit Bills Transactions');
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
		$pdf->SetFont('helvetica', '', 9, '', true);
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		
			$pdf->AddPage('L', 'Legal');
			
			// Set some content to print
			$html = '<style>
			
			th {
				text-transform: uppercase;
				font-weight: bold;
				text-align: left;
			}
			</style>
			<h2>'.$instName.'</h2>
			<h1>DEBIT BILLS TRANSACTIONS</h1>
			<h3>'. $branchName .'</h3>
			<h4>Transaction Date(s): '. $reportDate .'</h4>
			<table cellspacing="20" width="100%">
			<thead>
				<tr>
					<th width="200">TERMINAL</th>
					<th width="150">TRACE NO.</th>
					<th width="220">SEQUENCE NO.</th>
					<th width="150">TIME</th>
					<th width="150">DATE</th>
					<th width="420">BANK ID / CARD #</th>
					<th width="130">NAME</th>
					<th width="90">TC</th>
					<th width="180">TRXCODE</th>
					<th width="300">ACCOUNT</th>
					<th width="120" align="center">TY</th>
					<th width="90">VCD</th>
					<th width="500" align="center">INST</th>
					<th width="350">SUBSCRIBER NUMBER</th>
					<th width="150">TRX AMT</th>
					<th width="150">AMT AUTH</th>
				</tr>
			</thead>
			<tbody>
				'. $contentall .'
			</tbody>
			<tfoot>
				<tr>
					<td colspan="14">&nbsp;</td>
				</tr>
			</tfoot>
			</table>';
			
			$html .= '<table cellspacing="18" width="100%">
			<thead>
				<tr>
					<th align="center">&nbsp;</th>
					<th align="center">WITHDRAWAL</th>
					<th align="center">FUND TRANSFER</th>
					<th align="center">PAYMENTS</th>
					<th align="center">DEPOSITS</th>
					<th align="center">CASH ADVANCE</th>
					<th align="center">PREPAID RELOAD</th>
					<th align="center">IBFT REQ</th>
					<th align="center">IBFT WDL</th>
					<th align="center">IBFT TRN</th>
				</tr>
			</thead>
			<tbody>
				<tr align="right">
					<td><b>TOTAL:</b></td>
					<td>'. $core->currency($wdl) .'</td>
					<td>'. $core->currency($trn) .'</td>
					<td>'. $core->currency($pay) .'</td>
					<td>'. $core->currency($dep) .'</td>
					<td>'. $core->currency($adv) .'</td>
					<td>'. $core->currency($load) .'</td>
					<td>'. $core->currency($ibftreq) .'</td>
					<td>'. $core->currency($ibftreq) .'</td>
					<td>'. $core->currency($ibfttrn) .'</td>
				</tr>
			</tbody>
			</table>';
			// Print text using writeHTMLCell()
			$pdf->writeHTML($html, true, false, true, false, '');
		
		$pdf->Output('debitbillstrx_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file debitbillstrx.php */
/* Location: ./application/reports/debitbillstrx.php */