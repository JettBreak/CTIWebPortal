<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AutoLoadTrxReport extends CI_Controller {	

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
		
		$userName = $core->getUserName();
		$instName = $core->getInstName();
		$reportLog = $this->input->post('reports', TRUE);
		
		if ($core->canRep()) {
			$branchCode = $this->input->post('branch', TRUE);
			$branchName = $this->input->post('branchName', TRUE);
		} else {
			$branchCode = $core->getBranchCode();
			$branchName = $core->getBranchName();
		}
		
		$userAudit  = $core->getUserID();
		$sessionID  = $core->getSessionID();
		$finswitch = $core->getFINSWITCH();
		$onusCode  = $core->getONUSCODE();
		$bankcode  = $core->getBANKCODE();
		
		$termType = $this->input->post('termType', TRUE);
		$dtFrom = $core->formatDate('Y-m-d', $this->input->post('dtFrom', TRUE));
		$dtTo = $core->formatDate('Y-m-d', $this->input->post('dtTo', TRUE));
		
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
		
		$result = $reports->getAutoloadTransperTerminal($reportLog, $branchCode, $termType, $onusCode, $dtFrom, $dtTo);
		$tpt = $result->result_array();
		
		//echo '<pre>'.print_r($tpt).'</pre>';
		
		$data = array();
		if ($result->num_rows() === 0) {
			$data[' ']  = array(
					'content' => '<tr><td></td></tr><tr><td colspan="18" align="center"><h3><font color="red">"No activity for the day"</font></h3></td></tr>',
					'loadsaApprv' => 0,
					'loadsaRvrsl' => 0,
					'loadsaRejct' => 0,
					'loadcaApprv' => 0,
					'loadcaRvrsl' => 0,
					'loadcaRejct' => 0
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
		
		//init array
		foreach ($tpt as $row) {
			//echo '<pre>' . print_r($row) . '</pre>';
			$data[$row['termcode']] = array(
					'content' => '',
					'loadsaApprv' => 0,
					'loadsaRvrsl' => 0,
					'loadsaRejct' => 0,
					'loadcaApprv' => 0,
					'loadcaRvrsl' => 0,
					'loadcaRejct' => 0
			);
		}
		
		$totalIBFT = array (
			'loadsaApprvSubTotal' => 0,
			'loadsaRvrslSubTotal' => 0,
			'loadsaRejctSubTotal' => 0,
			'loadcaApprvSubTotal' => 0,
			'loadcaRvrslSubTotal' => 0,
			'loadcaRejctSubTotal' => 0
			);
			
		$contentall = '';
		
		foreach ($tpt as $row) {
			$xml->setXML($row['xml1'] . $row['xml2']);
			$xml->setXML($row['xml1'] . $row['xml2'] . $row['xml3'] . $row['xml4'] . $row['xml5'] . $row['xml6']);
			
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
			if ($remarks) {
				$remarks = '-';
				$rAlign = ' align="center"';
			}
			//end
			
			//remarks
			$acquirer = $xml->getVALUE('ACQCODE_MNEM');
			if (!$acquirer || $acquirer === 'null') {
				$acquirer = '-';
			}
			//end
			
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
					if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
						$mnemonic = 'LOADSA';
					}
					break;
				case 'IBFTCA':			
				case 'IBFTBTCA':
				case 'IBFTWDCA':
				case 'IBFTTRCA':
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
			
			if (intval($bankcode) === intval($row['isscode']) && 
				intval($bankcode) === intval($row['acqcode'])){
				if ($row['termtype'] === 'ATM' && 
					$row['chname'] === $finswitch) {
					continue;		
				}
			}
			
			$content = '<tr>
				<td width="140">'. $logkey .'</td>
				<td width="120">'. $traceNo .'</td>
				<td width="160">'. $sequenceNo .'</td>
				<td width="130">'. $core->formatDate('H:i:s', $row['dtlog']) .'</td>
				<td width="130">'. $core->formatDate('m/d/y', $row['dtlog']) .'</td>
				<td width="100">'. $row['tpluno'] .'</td>
				<td width="320">'. substr($row['prkey1'], 0, 6).'- '.substr($row['prkey1'], 6).'</td>
				<td width="110" align="center">'. (!$row['issname'] ? '-' : $row['issname']) .'</td>
				<td width="250" '. $a1Align .'>'. substr($acct1, -14) .'</td>
				<td width="60">'. $acctDesc .'</td>
				<td width="60">'. $row['msgtype'] .'</td>	
				<td width="150">'. $mnemonic .'</td>
				<td width="110" align="center">'. $acquirer .'</td>
				<td width="250" '. $a2Align .'>'. substr($acct2, -14) .'</td>	
				<td width="70">'. $row['sysvcode'] .'</td>	
				<td width="270" '. $rAlign .'>'. $remarks .'</td>		
				<td width="130" align="right">'. $core->currency($amtReq) .'</td>
				<td width="150" align="right">'. $amtAth .'</td>
			</tr>';
			
			$addAmt = floatval($row['amtath']);
			//echo '<pre>'.print_r($row['msgtype']).'</pre>';
			switch ($row['msgtype']) {
				case 51:
				case 52:
					switch ($mnemonic) {
						case 'LOADSA':
						case 'ELOADSA':
						case 'WDLSA':
							if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
								$data[$row['termcode']]['loadsaApprv'] += $amtReq;
							}
							break;
						case 'LOADCA':
						case 'ELOADCA':
						case 'WDLCA':
							if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
								$data[$row['termcode']]['loadcaApprv'] += $amtReq;
							}
							break;
					}
					break;
				case 53:
					switch ($mnemonic) {
						case 'LOADSA':
						case 'ELOADSA':
						case 'WDLSA':
							if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
								$data[$row['termcode']]['loadsaRejct'] += $amtReq;
							}
							break;
						case 'LOADCA':
						case 'ELOADCA':
						case 'WDLCA':
							if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
								$data[$row['termcode']]['loadcaRejct'] += $amtReq;
							}
							break;
					}
					break;
				case 71:
					switch ($mnemonic) {
						case 'LOADSA':
						case 'ELOADSA':
						case 'WDLSA':
							if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
								$data[$row['termcode']]['loadsaRvrsl'] += $amtReq;
							}
							break;
						case 'LOADCA':
						case 'ELOADCA':
						case 'WDLCA':
							if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
								$data[$row['termcode']]['loadcaRvrsl'] += $amtReq;
							}
							break;
					}
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
		$pdf->SetTitle('Autoload Transaction Report');
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
			<h1>AUTOLOAD TRANSACTION REPORT : '.$key.'</h1>
			<h3>'. $branchName .'</h3>
			<h4>Transaction Date(s): '. $reportDate .'</h4>
			<table cellspacing="10" width="100%">
			<thead>
				<tr>
					<th width="140">LOGKEY</th>
					<th width="120">TRACE NO.</th>
					<th width="160">SEQUENCE NO.</th>
					<th width="130">TIME</th>
					<th width="130">DATE</th>
					<th width="100">LUNO</th>
					<th width="320">BANK ID / CARD #</th>
					<th width="110" align="center">BANK</th>
					<th width="250">FROM <br>ACCOUNT</th>
					<th width="60">TY</th>
					<th width="60">TC</th>
					<th width="150">TRXCODE</th>
					<th width="110" align="center">BANK</th>
					<th width="250">TO <br>ACCOUNT</th>
					<th width="70">VCD</th>
					<th width="270" align="center">REMARKS</th>
					<th width="130">TRX AMT</th>
					<th width="150">AMT AUTH</th>
				</tr>
			</thead>
			<tbody>
				'. ($d['content'] === '' ? '<tr><td></td></tr><tr><td colspan="18" align="center"><h3><font color="red">"No activity for the day"</font></h3></td></tr>' : $d['content']).'
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
					<th align="center">Apprvd LOADSA</th>
					<th align="center">Apprvd LOADCA</th>
					<th align="center">LOADSA Revrsl</th>
					<th align="center">LOADCA Revrsl</th>
					<th align="center">Rejctd LOADSA</th>
					<th align="center">Rejctd LOADCA</th>
				</tr>
			</thead>
			<tbody>
				<tr align="right">
					<td><b>TOTAL:</b></td>
					<td>'. $core->currency($d['loadsaApprv']) .'</td>
					<td>'. $core->currency($d['loadcaApprv']) .'</td>
					<td>'. $core->currency($d['loadsaRvrsl']) .'</td>
					<td>'. $core->currency($d['loadcaRvrsl']) .'</td>
					<td>'. $core->currency($d['loadsaRejct']) .'</td>
					<td>'. $core->currency($d['loadcaRejct']) .'</td>
				</tr>
			</tbody>
			</table>';
			// Print text using writeHTMLCell()
			$pdf->writeHTML($html, true, false, true, false, '');
		}
		$pdf->Output('autoloadtrxreport_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file autoloadtrxreport.php */
/* Location: ./application/reports/autoloadtrxreport.php */