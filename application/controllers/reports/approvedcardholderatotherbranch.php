<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ApprovedCardHolderAtOtherBranch extends CI_Controller {	

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
		$onusCode  = $core->getONUSCODE();
		
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
		
		$result = $reports->getOurRChatOtherBranch($reportLog, $branchCode, $termType, $onusCode, $dtFrom, $dtTo); // ONUS_CODE = 'SAVE'
		$tpt = $result->result_array();
		
		//init array
		$data = array();
		if ($result->num_rows() === 0) {
			$data[' '] = array(
				'content' => '<tr><td></td></tr><tr><td colspan="15" align="center"><h3><font color="red">"No activity for the day"</font></h3></td></tr>',
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
			$data[$row['brcode']] = array(
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
			//echo '<pre>'.print_r($row).'</pre>';
			$xml->setXML($row['xml1'] . $row['xml2'] . $row['xml3'] . $row['xml4'] . $row['xml5'] . $row['xml6']);
			
			$logkey = str_pad($row['logseqno'], 8, '0', STR_PAD_LEFT);
			//$traceNo = $row['chseqno'];
			$brCode = $row['brcode'];
			$termCode = $row['termcode'];
			
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
			$remarks = '';//$row['shortdescription'];
			$rAlign = NULL;
			if (!$remarks) {
				$remarks = '-';
				$rAlign = ' align="center"';
			}
			//end
			
			$amtReq = $core->currency($row['amtreq']);
			$amtAth = $core->currency($row['amtath']);
			
			if (!array_key_exists($brCode, $data)) {
				$data[$brCode] = NULL;
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
				<td width="170">'. $logkey .'</td>
				<td width="140">'. $traceNo .'</td>
				<td width="170">'. $sequenceNo .'</td>
				<td width="170">'. $core->formatDate('H:i:s', $row['dtlog']) .'</td>
				<td width="140">'. $core->formatDate('m/d/y', $row['dtlog']) .'</td>
				<td width="130">'. $row['tpluno'] .'</td>
				<td width="170">'. substr($row['prkey1'], 0, 6) .'</td>
				<td width="280">'. substr($row['prkey1'], 6) .'</td>
				<td width="100">'. $row['msgtype'] .'</td>	
				<td width="200">'. $mnemonic .'</td>		
				<td width="280" '. $aAlign .'>'. substr($acct1, -14) .'</td>
				<td width="80">'. $acctDesc .'</td>
				<td width="150" align="center">'. $termCode .'</td>
				<td width="240" align="right">'. $amtReq .'</td>
				<td width="240" align="right">'. $amtAth .'</td>
			</tr>';
			
			$addAmt = floatval($row['amtath']);
			switch ($row['trxtype1']) {
				case 'WDL':
					//$wdl += $addAmt;
					if (!in_array($mnemonic, array('ELOADSA', 'ELOADCA')) || intval($xml->getValue('IBFTPTR')) !== IBFTPTR)  {
						$data[$brCode]['wdl'] += $addAmt;
					} else {
						$data[$brCode]['load'] += $addAmt;
					}
					break;
				case 'TRN':
					//$trn += $addAmt;
					$data[$brCode]['trn'] += $addAmt;
					break;
				case 'PAY':
					//$pay += $addAmt;
					$data[$brCode]['pay'] += $addAmt;
					break;
				case 'DEP':
					//$dep += $addAmt;
					$data[$brCode]['dep'] += $addAmt;
					break;
				case 'ADV':
					//$adv += $addAmt;
					$data[$brCode]['adv'] += $addAmt;
					break;
				case 'LOAD':
					//$load += $addAmt;
					$data[$brCode]['load'] += $addAmt;
					break;
				case 'IBFT':
					switch ($mnemonic) {
						case 'IBFTSA':
						case 'IBFTCA':
						case 'IBFTBTSA':
						case 'IBFTBTCA':
							//$ibftreq += $addAmt;
							$data[$brCode]['ibftreq'] += $addAmt;
							break;
						case 'IBFTWDSA':
						case 'IBFTWDCA':
							//$ibftwdl += $addAmt;
							$data[$brCode]['ibftwdl'] += $addAmt;
							break;
						case 'IBFTTRSA':
						case 'IBFTTRCA':
							//$ibfttrn += $addAmt;
							$data[$brCode]['ibfttrn'] += $addAmt;
							break;
					}
					//rules for ELD 8882 issue
					if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
						$data[$brCode]['load'] += $addAmt;
					}
					//end
					break;
			}
			
			$data[$brCode]['content'] .= $content;
			
			//echo '<pre>' .print_r($data). '</pre>';
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
		$pdf->SetTitle('Approved Our Cardholder at Other Branch');
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
			.tf {
				text-align: right;
			}
			</style>
			<h2>'.$instName.'</h2>
			<h1>APPROVED OUR CARDHOLDER AT OTHER BRANCH: '. $key .'</h1>
			<h3>'. $branchName .'</h3>
			<h4>Transaction Date(s): '. $reportDate .'</h4>
			<table cellspacing="10" width="100%">
			<thead>
				<tr>
					<th width="170">LOGKEY</th>
					<th width="140">TRACE NO.</th>
					<th width="170">SEQUENCE NO.</th>
					<th width="170">TIME</th>
					<th width="140">DATE</th>
					<th width="130">LUNO</th>
					<th width="170">BANK-ID</th>
					<th width="280">CARD NO.</th>
					<th width="100">TC</th>
					<th width="200">TRXCODE</th>
					<th width="280" align="center">ACCOUNT</th>
					<th width="80">TY</th>
					<th width="150" align="center">TERMINAL</th>
					<th width="240" align="center">TRX AMT</th>
					<th width="240" align="center">AMT AUTH</th>
				</tr>
				</thead>
				<tbody>
				'. $d['content'] .'
				</tbody>
				<tfoot>
				<tr>
					<td colspan="15">&nbsp;</td>
				</tr>
				</tfoot>
			</table>';
			
			$html .= '<table cellspacing="18" width="100%">
				<tr>
					<th class="tf">&nbsp;</th>
					<th class="tf">WITHDRAWAL</th>
					<th class="tf">FUND TRANSFER</th>
					<th class="tf">PAYMENTS</th>
					<th class="tf">DEPOSITS</th>
					<th class="tf">CASH ADVANCE</th>
					<th class="tf">PREPAID RELOAD</th>
					<th class="tf">IBFT REQ</th>
					<th class="tf">IBFT WDL</th>
					<th class="tf">IBFT TRN</th>
				</tr>
				<tr align="right">
					<td><b>TOTAL:</b></td>
					<td>'. $core->currency($d['wdl']) .'</td>
					<td>'. $core->currency($d['trn']) .'</td>
					<td>'. $core->currency($d['pay']) .'</td>
					<td>'. $core->currency($d['dep']) .'</td>
					<td>'. $core->currency($d['adv']) .'</td>
					<td>'. $core->currency($d['load']) .'</td>
					<td>'. $core->currency($d['ibftreq']) .'</td>
					<td>'. $core->currency($d['ibftwdl']) .'</td>
					<td>'. $core->currency($d['ibfttrn']) .'</td>
				</tr>
			</table>';
			// Print text using writeHTMLCell()
			$pdf->writeHTML($html, true, false, true, false, '');
		}
		
		$pdf->Output('approvedcardholderatotherbranch_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file approvedcardholderatotherbranch.php */
/* Location: ./application/reports/approvedcardholderatotherbranch.php */