<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATMAvailability extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ATMAVAILABILITY_NO);
	}
	
	function preview()
	{
		$this->load->model('coresys/reports_model');
		$this->load->library('pdf');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$pdf 	 = $this->pdf;
		
		$userName = $core->getUserName();
		$instName = $core->getInstName();
		//$branchName = $core->getBranchName();
		
		if ($core->canRep()) {
			$branchCode = $this->input->post('branch', TRUE);
			$branchName = $this->input->post('branchName', TRUE);
		} else {
			$branchCode = $core->getBranchCode();
			$branchName = $core->getBranchName();
		}
		
		$userAudit  = $core->getUserID();
		$sessionID  = $core->getSessionID();
		
		$dtFrom = $this->input->post('dtFrom', TRUE);
		$dtFrom = $core->formatDate('Y-m-d', $dtFrom);
		$dtTo = $this->input->post('dtTo', TRUE);
		$dtTo = $core->formatDate('Y-m-d', $dtTo);
		
		$result = $reports->getATMListForReports($branchCode);
		
		$data = NULL;

		$terminal = array();
		
		//init array for indexing
		foreach ($result->result_array() as $row) {
			$terminal[$row['termcode']] = array(
				1 => array( //hardware types
					1 => 0, //dispenser
					2 => 0, //cardreader
					3 => 0, //monitor
					4 => 0, //cpu/hdd
					5 => 0, //key/PIN pad
					6 => 0  //others
				),
				2 => 0, //power failure
				3 => 0, //network issue
				4 => 0, //others
				'exempted' => 0,
				'location' => $row['location'],
				'dtprod' => $row['dtprod']
			);
		}
		
		$result->free_result();
		$result->next_result();
			
		if ($dtFrom !== $dtTo) {
			//if same month, outputs: January 1 - 10, 2011
			if ($core->formatDate('Y-m', $dtFrom) === $core->formatDate('Y-m', $dtTo)) {
				$currentDate = $core->formatDate('F j', $dtFrom) .' - '. $core->formatDate('j, Y', $dtTo);
			} else {
				$currentDate = 'From '. $core->formatDate('F j, Y', $dtFrom) .' to '. $core->formatDate('F j, Y', $dtTo);
			}
			
			//compute number of days
			$dt1 = date_create($dtFrom);
			$dt2 = date_create($dtTo);
			$interval = date_diff($dt1, $dt2);
			
			$days = intval($interval->format('%a')) + 1;
			$totalMinutes = 24 * 60 * $days; //period
			$dateSaveFormat = $core->formatDate('mdY', $dtFrom) .'-'. $core->formatDate('mdY', $dtTo);
		} else {
			$currentDate = $core->formatDate('F j, Y', $dtFrom);
			$totalMinutes = 24 * 60; //1 day
			$dateSaveFormat = $core->formatDate('mdY', $dtFrom);
		}
		
		$result = $reports->getATMAvailabilityList($dtFrom, $dtTo, $branchCode);
		
		
		foreach ($result->result_array() as $row) {
			$issueType = intval($row['issuetype']);
			$hardwareType = intval($row['hardwaretype']);
			$downtime = intval($row['downtime']);
			$exempted = intval($row['exdowntime']);
			
			if ($issueType === 1) {
				$terminal[$row['termcode']][1][$hardwareType] += $downtime;
			} else {
				$terminal[$row['termcode']][$issueType] += $downtime;
			}
			$terminal[$row['termcode']]['exempted'] += $exempted;
		}
		
		$totalAvailability = array();
		foreach ($terminal as $termcode => $i) {
			$x = array(
				'dispenser' => $i[1][1],
				'cardReader' => $i[1][2],
				'monitor' => $i[1][3],
				'cpuHDD' => $i[1][4],
				'keyPINPad' => $i[1][5],
				'others1' => $i[1][6],
				'powerF' => $i[2],
				'network' => $i[3],
				'others2' => $i[4]
			);
			
			$deduction = 0;
			
			//if production date > from date
			if ($i['dtprod'] > $dtFrom) {
				//compute number of days
				$dt1 = date_create($i['dtprod']);
				$dt2 = date_create($dtFrom);
				$interval = date_diff($dt1, $dt2);
				
				$days = intval($interval->format('%a'));
				$deduction = 24 * 60 * $days; //period
			}
			
			$totalDowntime = array_sum($x);
			$total = $totalDowntime - $i['exempted'];
			$total = $totalMinutes - $deduction - $total;
			$overall = round(100 - ($totalDowntime * 100) / $total, 2);
			
			$data .= '<tr>'.
				'<td width="300">'. $termcode .'</td>'.
				'<td>'.  $i['location'] .'</td>'.
				'<td align="center">'. $core->formatDate('m/d/Y', $i['dtprod']) .'</td>'.
				'<td align="center">'. number_format($total) .'</td>'.
				'<td align="center">'. number_format($x['dispenser']) .'</td>'.
				'<td align="center">'. number_format($x['cardReader']) .'</td>'.
				'<td align="center">'. number_format($x['monitor']) .'</td>'.
				'<td align="center">'. number_format($x['cpuHDD']) .'</td>'.
				'<td align="center">'. number_format($x['keyPINPad']) .'</td>'.
				'<td align="center">'. number_format($x['others1']) .'</td>'.
				'<td align="center">'. number_format($x['powerF']) .'</td>'.
				'<td align="center">'. number_format($x['network']) .'</td>'.
				'<td align="center">'. number_format($x['others2']) .'</td>'.
				'<td align="center">'. number_format($totalDowntime) .'</td>'.
				'<td align="center">'. $i['exempted'] .'</td>'.
				'<td align="center">'. $overall .'%</td>'.
			'</tr>';
			
			$totalAvailability[] = $overall;
		}
		
		$data .= '<tr><td colspan="16">&nbsp;</td></tr>';
		
		$totavail = 0;
		if (array_sum($totalAvailability) !== 0) {
			$totavail = round(array_sum($totalAvailability) / count($totalAvailability), 2);
		}
		
		//totals
		$tableFooter = '<tr>'.
			'<td align="right" colspan="15"><b>TOTAL</b></td>'.
			'<td align="center">'. array_sum($totalAvailability) .'%</td>'.
		'</tr>'.
		'<tr>'.
			'<td align="right" colspan="15"><b>NUMBER OF ATMS</b></td>'.
			'<td align="center">'. number_format(count($terminal)) .'</td>'.
		'</tr>'.
		'<tr>'.
			'<td align="right" colspan="15"><b>AVERAGE ATM AVAILABILITY</b></td>'.
			'<td align="center">'. $totavail .'%</td>'.
		'</tr>';
		//end
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('ATM Availability Report');
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
		$pdf->AddPage('L', 'Legal');
		
		// Set some content to print
		$html = '<style>
		th {
			font-weight: bold;
			text-align: center;
			vertical-align: middle;
			
		}
		</style>
		<h2>'.$instName.'</h2>
		<h1>ATM AVAILABILITY REPORT</h1>
		<h3>'. $branchName .'</h3>
		<h4>'. $currentDate .'</h4>
		<table cellspacing="18" width="100%">
			<thead>
				<tr>
					<th width="300" rowspan="2">TERMINAL CODE</th>
					<th rowspan="2">LOCATION</th>
					<th rowspan="2">PRODUCTION DATE</th>
					<th rowspan="2">TOTAL NUMBER OF MINUTES</th>
					<th colspan="6">HARDWARE PROBLEM</th>
					<th rowspan="2">POWER FAILURE</th>
					<th rowspan="2">NETWORK ISSUE</th>
					<th rowspan="2">OTHERS</th>
					<th rowspan="2">TOTAL DOWNTIME</th>
					<th rowspan="2">EXEMPTED DOWNTIME</th>
					<th rowspan="2">% OVERALL ATM AVAILABILITY</th>
				</tr>
				<tr>
					<th>DISPENSER</th>
					<th>CARD READER</th>
					<th>MONITOR</th>
					<th>CPU/HDD</th>
					<th>KEY/PIN PAD</th>
					<th>OTHERS</th>
				</tr>
			</thead>
			<tbody>'. $data .'<tbody>
			<tfoot>'. $tableFooter .'</tfoot>
		</table>';
		// Print text using writeHTMLCell()
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->Output('atmavailability_' .$dateSaveFormat . '.pdf', 'I');
	}
}