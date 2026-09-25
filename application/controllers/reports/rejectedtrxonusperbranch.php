<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RejectedTrxOnusPerBranch extends CI_Controller {	

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(APPROVEDTRXACQPERTERM_NO);
	}
	
	function preview()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/reports_model');
		$this->load->model('coreapp/branch_model');
		$this->load->library('pdf');
		$this->load->helper('url');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$pdf 	 = $this->pdf;		
		
		$userName 	 = $core->getUserName();
		$instName 	 = $core->getInstName();
		$instAddress = $core->getAddress();
		$onusCode  	 = $core->getONUSCODE();
		//$branchName = $core->getBranchName();
		
		if ($core->canRep()) {
			$branchCode = $this->input->post('branch', TRUE);
			$branchName = $this->input->post('branchName', TRUE);
		} else {
			$branchCode = $core->getBranchCode();
			$branchName = $core->getBranchName();
		}
		
		$branches = '';
		
		//branches combobox
		if ($branches !== $this->cache->get($this->core->getSessionID() . 'branches')) {
			$result = $this->branch_model->getBranchListRep($branchCode);
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			//$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$branch = array();
		foreach ($branches as $row) {			
			//init arrays
			$index = $row['brname'].$row['brcode'];
			$branch[$index] = array(
				'brname' => $row['brname'],
				'brcode' => $row['brcode'],
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
		
		//$result->free_result();
		//$result->next_result();
		
		$totals = array(
			'wdrls' => 0,
			'wdlAmt' => 0,
			'advcs' => 0,
			'advAmt' => 0,
			'rvctr' => 0,
			'wdlRversl' => 0,
			'deps' => 0,
			'balInq' => 0,
			'ftrn' => 0,
			'ibft' => 0,
			'stmr' => 0,
			'chkr' => 0,
			'paym' => 0,
			'load' => 0,
			'reserve2' => 0
		);

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
		
		$result = $reports->getRejectedSumOnUsPerBranch($reportLog, $branchCode, $termType, 'ATM', $onusCode, $dtFrom, $dtTo);
		$tpt = $result->result_array();
		
		
		foreach ($tpt as $row) {
			//echo '<pre>' . print_r($tpt) . '<pre>';
			$brCode = $row['brcode'];
			
			$result->free_result();
			$result->next_result();
			
			$result = $this->branch_model->getBranchListRep($brCode);
			
			$brches = $result->result_array();
			
			foreach ($brches as $brchname)	
			{
				$brnme = $brchname['brname'];
			}
			
			$result->free_result();
			$result->next_result();
			
			
			$index = $brnme.$row['brcode'];
			
			
			if ( !array_key_exists($index, $branch) ) {
				continue;
			}
			
			switch ($row['trxtype1']) {
				case 'IBFT':
					$branch[$index]['ibft'] += intval($row['totctr']);
					break;
				case 'INQ':
					$branch[$index]['balInq'] += intval($row['totctr']);
					break;
				case 'WDL':
					$branch[$index]['wdrls'] += intval($row['totctr']);
					$branch[$index]['wdlAmt'] += floatval($row['totamt']);
					break;
				case 'REV':
					$branch[$index]['rvctr'] += intval($row['totctr']);
					$branch[$index]['wdlRversl'] += floatval($row['totamt']);
					break;
				case 'TRN':
					$branch[$index]['ftrn'] += intval($row['totctr']);
					break;
				case 'ADV':
					$branch[$index]['advcs'] += intval($row['totctr']);
					$branch[$index]['advAmt'] += floatval($row['totamt']);
					break;
				case 'DEP':
					$branch[$index]['deps'] += intval($row['totctr']);
					break;
				case 'STAT':
					$branch[$index]['stmr'] += intval($row['totctr']);
					break;
				case 'CKBK':
					$branch[$index]['chkr'] += intval($row['totctr']);
					break;
				case 'PAY':
					$branch[$index]['paym'] += intval($row['totctr']);
					break;
				case 'LOAD':
					$branch[$index]['load'] += intval($row['totctr']);
					break;
				default:
					break;
			}
			
			$branch[$index]['reserve2'] += 0;
		}
		
		ksort($branch);
		
		$data = NULL;
		foreach ($branch as $code => $r) {
			
			$brchcode = $r['brcode'];
			
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
				'<td align="left" width="300">'.$brchcode . '-'. $r['brname'] .'</td>'.//branch
				'<td>'. number_format($wdrls) .'</td>'.//wdrls
				'<td align="right">'. $core->currency($wdlAmt) .'</td>'.//wdl amount
				'<td>'. number_format($advcs) .'</td>'.//advcs
				'<td align="right">'. $core->currency($advAmt) .'</td>'.//adv amount
				'<td>'. number_format($rvctr) .'</td>'.//rvctr
				'<td align="right">'. $core->currency($wdlRversl) .'</td>'.//wdl rversl
				'<td>'. number_format($deps) .'</td>'.//deps
				'<td>'. number_format($balInq) .'</td>'.//balinq
				'<td>'. number_format($ftrn) .'</td>'.//ftrn
				'<td>'. number_format($ibft) .'</td>'.//ibft
				'<td>'. number_format($stmr) .'</td>'.//stmr
				'<td>'. number_format($chkr) .'</td>'.//chkr
				'<td>'. number_format($paym) .'</td>'.//paym
				'<td>'. number_format($load) .'</td>'.//reserve1 '<td align="right">'. $core->currency($reserve2) .'</td>'.//reserve2
			'</tr>';
			
			//count totals
			$totals['wdrls'] += $wdrls;
			$totals['wdlAmt'] += $wdlAmt;
			$totals['advcs'] += $advcs;
			$totals['advAmt'] += $advAmt;
			$totals['rvctr'] += $rvctr;
			$totals['wdlRversl'] += $wdlRversl;
			$totals['deps'] += $deps;
			$totals['balInq'] += $balInq;
			$totals['ftrn'] += $ftrn;
			$totals['ibft'] += $ibft;
			$totals['stmr'] += $stmr;
			$totals['chkr'] += $chkr;
			$totals['paym'] += $paym;
			$totals['load'] += $load;
			$totals['reserve2'] += $reserve2;
		}
		
		$result->free_result();
		$result->next_result();
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('Rejected Transactions, Onus Per Branch Summary');
		$pdf->SetSubject('');
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
		$pdf->AddPage('L', 'LEGAL');
		
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
		<h1>REJECTED TRANSACTIONS, ONUS PER BRANCH SUMMARY</h1>
		<h3>'. $branchName .'</h3>
		<h4>Transaction Date(s): '. $reportDate .'</h4>
		<table cellspacing="18" width="100%">
			<thead>
				<tr>
					<th width="300">Branch</th>
					<th>WDL</th>
					<th>WDL Amount</th>
					<th>ADV CASH</th>
					<th>ADV Amount</th>
					<th>RCTR</th>
					<th>WDL REVERSAL</th>
					<th>DEPOSITS</th>
					<th>BAL INQ</th>
					<th>FUND TRN</th>
					<th>IBFT</th>
					<th>STMR</th>
					<th>CHKB REQ</th>
					<th>PAYMENTS</th>
					<th>LOAD</th>
				</tr>
			</thead>
			<tbody>
			'. $data .'
			</tbody>
			<tfoot>
				<tr>
					<td><b>TOTAL:</b></td>
					<td>'. number_format($totals['wdrls']) .'</td>
					<td align="right">'. $core->currency($totals['wdlAmt']) .'</td>
					<td>'. number_format($totals['advcs']) .'</td>
					<td align="right">'. $core->currency($totals['advAmt']) .'</td>
					<td>'. number_format($totals['rvctr']) .'</td>
					<td align="right">'. $core->currency($totals['wdlRversl']) . '</td>
					<td>'. number_format($totals['deps']) .'</td>
					<td>'. number_format($totals['balInq']) .'</td>
					<td>'. number_format($totals['ftrn']) .'</td>
					<td>'. number_format($totals['ibft']) .'</td>
					<td>'. number_format($totals['stmr']) .'</td>
					<td>'. number_format($totals['chkr']) .'</td>
					<td>'. number_format($totals['paym']) .'</td>
					<td>'. number_format($totals['load']) .'</td>
				</tr>
			</tfoot>
		</table>';
		// Print text using writeHTMLCell()
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->Output('rejectedtrxonusperbranch_' . $dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file rejectedtrxonusperbranch_.php */
/* Location: ./application/reports/rejectedtrxonusperbranch_.php */