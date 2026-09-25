<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ApprovedTrxAcqPerTerm extends CI_Controller {	

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(APPROVEDTRXACQPERTERM_NO);
	}
	
	function preview()
	{
		$this->load->model('coresys/reports_model');
		$this->load->library('pdf');
		$this->load->helper('url');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$pdf 	 = $this->pdf;		
		
		$userName = $core->getUserName();
		$instName = $core->getInstName();
		$finswitch = $core->getFINSWITCH();
		//$branchName = $core->getBranchName();
		
		if ($core->canRep()) {
			$branchCode = $this->input->post('branch', TRUE);
			$branchName = $this->input->post('branchName', TRUE);
		} else {
			$branchCode = $core->getBranchCode();
			$branchName = $core->getBranchName();
		}
		
		//$userAudit  = $core->getUserID();
		//$sessionID  = $core->getSessionID();
		
		$result = $reports->getATMListForReports($branchCode);
		
		$terminal = array();
		foreach ($result->result_array() as $row) {
			$termCode = $row['termcode'];
			
			//init arrays
			$terminal[$termCode] = array(
				'ibft' => 0,
				'balInq' => 0,
				'wdrls' => 0,
				'wdlAmt' => 0,
				'rvctr' => 0,
				'wdlRversl' => 0,
				'ftrn' => 0,
				'advcs' => 0,
				'advAmt' => 0,
				'deps' => 0,
				'stmr' => 0,
				'chkr' => 0,
				'paym' => 0,
				'load' => 0,
				'reserve2' => 0
			);
			//end
		}
		
		$result->free_result();
		$result->next_result();
		
		$wdrlsTotal = 0;
		$wdlAmtTotal = 0;
		$advcsTotal = 0;
		$advAmtTotal = 0;
		$rvctrTotal = 0;
		$wdlRverslTotal = 0;
		$depsTotal = 0;
		$balInqTotal = 0;
		$ftrnTotal = 0;
		$ibftTotal = 0;
		$stmrTotal = 0;
		$chkrTotal = 0;
		$paymTotal = 0;
		$loadTotal = 0;
		$reserve2Total = 0;
		
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
		
		$result = $reports->getSumAcquirerPerTerminal($reportLog, $branchCode, $finswitch, $dtFrom, $dtTo); 
		//
		
		
		foreach ($result->result_array() as $row) {
			$termCode = $row['termcode'];
			
			
			if ( !array_key_exists($termCode, $terminal) ) {
				continue;
			}
			
			switch ($row['trxtype1']) {
				case 'IBFT':
					$terminal[$termCode]['ibft'] += intval($row['totctr']);
					break;
				case 'INQ':
					$terminal[$termCode]['balInq'] += intval($row['totctr']);
					break;
				case 'WDL':
					$terminal[$termCode]['wdrls'] += intval($row['totctr']);
					$terminal[$termCode]['wdlAmt'] += floatval($row['totamt']);
					break;
				case 'REV':
					$terminal[$termCode]['rvctr'] += intval($row['totctr']);
					$terminal[$termCode]['wdlRversl'] += floatval($row['totamt']);
					break;
				case 'TRN':
					$terminal[$termCode]['ftrn'] += intval($row['totctr']);
					break;
				case 'ADV':
					$terminal[$termCode]['advcs'] += intval($row['totctr']);
					$terminal[$termCode]['advAmt'] += floatval($row['totamt']);
					break;
				case 'DEP':
					$terminal[$termCode]['deps'] += intval($row['totctr']);
					break;
				case 'STAT':
					$terminal[$termCode]['stmr'] += intval($row['totctr']);
					break;
				case 'CKBK':
					$terminal[$termCode]['chkr'] += intval($row['totctr']);
					break;
				case 'PAY':
					$terminal[$termCode]['paym'] += intval($row['totctr']);
					break;
				case 'LOAD':
					$terminal[$termCode]['load'] += intval($row['totctr']);
					break;
				default:
					break;
			}
			
			$terminal[$termCode]['reserve2'] += 0;
		}
		
		$data = NULL;
		foreach ($terminal as $code => $r) {
			$wdrls = $r['wdrls'];
			$wdlAmt = $r['wdlAmt'];
			$advcs = $r['advcs'];
			$advAmt = $r['advAmt'];
			$rvctr = $r['rvctr'];
			$wdlRversl = $r['wdlRversl'];
			$deps = $r['deps'];
			$balInq = $r['balInq'];
			$ftrn = $r['ftrn'];
			$ibft = $r['ibft'];
			$stmr = $r['stmr'];
			$chkr = $r['chkr'];
			$paym = $r['paym'];
			$load = $r['load'];
			$reserve2 = $r['reserve2'];
			
			$data .= '<tr>'.
				'<td>'. $code .'</td>'.//terminal
				'<td>'. $wdrls .'</td>'.//wdrls
				'<td align="right">'. $core->currency($wdlAmt) .'</td>'.//wdl amount
				'<td>'. $advcs .'</td>'.//advcs
				'<td align="right">'. $core->currency($advAmt) .'</td>'.//adv amount
				'<td>'. $rvctr .'</td>'.//rvctr
				'<td align="right">'. $core->currency($wdlRversl) .'</td>'.//wdl rversl
				'<td>'. $deps .'</td>'.//deps
				'<td>'. $balInq .'</td>'.//balinq
				'<td>'. $ftrn .'</td>'.//ftrn
				'<td>'. $ibft .'</td>'.//ibft
				'<td>'. $stmr .'</td>'.//stmr
				'<td>'. $chkr .'</td>'.//chkr
				'<td>'. $paym .'</td>'.//paym
				'<td>'. $load .'</td>'.//reserve1 '<td align="right">'. $core->currency($reserve2) .'</td>'.//reserve2
			'</tr>';
			
			//count totals
			$wdrlsTotal += $wdrls;
			$wdlAmtTotal += $wdlAmt;
			$advcsTotal += $advcs;
			$advAmtTotal += $advAmt;
			$rvctrTotal += $rvctr;
			$wdlRverslTotal += $wdlRversl;
			$depsTotal += $deps;
			$balInqTotal += $balInq;
			$ftrnTotal += $ftrn;
			$ibftTotal += $ibft;
			$stmrTotal += $stmr;
			$chkrTotal += $chkr;
			$paymTotal += $paym;
			$loadTotal += $load;
			$reserve2Total += $reserve2;
		}
		
		/*if ($result->num_rows() === 0) {
			$this->load->helper('url');
			redirect('/pdfviewer/norecord');
			exit();
		}*/
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('Approved Transactions, ACQ Per Terminal');
		$pdf->SetSubject('Card');
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
		$pdf->AddPage('L', 'Letter');
		
		// Set some content to print
		$html = '<style>
		th {
			text-transform: uppercase;
			font-weight: bold;
			text-align: center;
		}
		td {
			text-align: center;
		}
		</style>
		<h2>'.$instName.'</h2>
		<h1>APPROVED TRANSACTIONS, ACQUIRER PER TERMINAL SUMMARY</h1>
		<h3>'. $branchName .'</h3>
		<h4>'. $reportDate .'</h4>
		<table cellspacing="18" width="100%">
			<thead>
				<tr>
					<th>Terminal</th>
					<th>WDRWLS</th>
					<th>WDL Amount</th>
					<th>ADVCS</th>
					<th>ADV Amount</th>
					<th>RVCTR</th>
					<th>WDL RVERSL</th>
					<th>DEPS</th>
					<th>BALINQ</th>
					<th>FTRN</th>
					<th>IBFT</th>
					<th>STMR</th>
					<th>CHKR</th>
					<th>PAYM</th>
					<th>LOAD</th>
				</tr>
			</thead>
			<tbody>
			'. $data .'
			</tbody>
			<tfoot>
				<tr>
					<td><b>TOTAL:</b></td>
					<td>'. $wdrlsTotal .'</td>
					<td align="right">'. $core->currency($wdlAmtTotal) .'</td>
					<td>'. $advcsTotal .'</td>
					<td align="right">'. $core->currency($advAmtTotal) .'</td>
					<td>'. $rvctrTotal .'</td>
					<td align="right">'. $core->currency($wdlRverslTotal) . '</td>
					<td>'. $depsTotal .'</td>
					<td>'. $balInqTotal .'</td>
					<td>'. $ftrnTotal .'</td>
					<td>'. $ibftTotal .'</td>
					<td>'. $stmrTotal .'</td>
					<td>'. $chkrTotal .'</td>
					<td>'. $paymTotal .'</td>
					<td>'. $loadTotal .'</td>
				</tr>
			</tfoot>
		</table>';
		// Print text using writeHTMLCell()
		
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->Output('approvedtrxacqperterm_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file approvedtrxacqperterm.php */
/* Location: ./application/reports/approvedtrxacqperterm.php */