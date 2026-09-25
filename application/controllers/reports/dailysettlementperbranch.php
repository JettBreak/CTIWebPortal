<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DailySettlementPerBranch extends CI_Controller {	

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
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$pdf 	 = $this->pdf;		
		
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
		
		$result = $reports->getSettlementListPerBranch($reportLog, $branchCode, $termType, $finswitch, $dtFrom, $dtTo);
		$tpt = $result->result_array();
		
		$data = array();
		if ($result->num_rows() === 0) {
			$data[' '][' '] = array(
					'content' => 'norecord',
					'description' => '',
					'cntrIss' => 0,
					'cntrAcq' => 0,
					'FeeIss' => 0,
					'FeeAcq' => 0,
					'AmtAthIss' => 0,
					'AmtAthAcq' => 0);
					
			$totals[' '] = array(
				'TotalAmtIss' => 0,
				'TotalAmtAcq' => 0,
				'TotalNumIss' => 0,
				'TotalNumAcq' => 0,
				'TotalFeeIss' => 0,
				'TotalFeeAcq' => 0,
				'TotalNet' => 0);
		}
		
		if (!$acctTypes = $this->cache->get($this->core->getSessionID() . 'acctTypes')) {
			$result->free_result();
			$result->next_result();
			
			$this->load->model('coreapp/card_model');	
			$result = $this->card_model->getAccountType();
			$acctTypes = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'acctTypes', $acctTypes, CACHE_TTL);
		}
		$contentall = '';
		
		foreach ($tpt as $row) {
			//echo '<pre>' .print_r($row). '</pre>';
			$data[$row['isscode']][$row['brcode']] = array(
					'content' => '',
					'description' => '',
					'cntrIss' => 0,
					'cntrAcq' => 0,
					'FeeIss' => 0,
					'FeeAcq' => 0,
					'AmtAthIss' => 0,
					'AmtAthAcq' => 0);

			$totals[$row['isscode']] = array(
				'TotalAmtIss' => 0,
				'TotalAmtAcq' => 0,
				'TotalNumIss' => 0,
				'TotalNumAcq' => 0,
				'TotalFeeIss' => 0,
				'TotalFeeAcq' => 0,
				'TotalNet' => 0);
			}
			
		foreach ($tpt as $row) {
			$issCode = $row['isscode'];
			$brCode = $row['brcode'];
			$amtAth = $row['amtath'];
			$fee = $row['fee1'];			
			$data[$issCode][$brCode]['description'] = $brCode;
			
			//echo '<pre>' .print_r($row). '</pre>';
			if ($row['termtype'] === 'ATM') {
				$data[$issCode][$brCode]['AmtAthAcq'] += $amtAth;
				$data[$issCode][$brCode]['FeeAcq'] += $fee;
				$data[$issCode][$brCode]['cntrAcq']++;
				$totals[$issCode]['TotalAmtAcq'] += $amtAth;
				$totals[$issCode]['TotalNumAcq'] ++;
				$totals[$issCode]['TotalFeeAcq'] += $fee;
			} else {
				$data[$issCode][$brCode]['AmtAthIss'] += $amtAth;
				$data[$issCode][$brCode]['FeeIss'] += $fee;
				$data[$issCode][$brCode]['cntrIss']++;
				$totals[$issCode]['TotalAmtIss'] += $amtAth;
				$totals[$issCode]['TotalNumIss'] ++;
				$totals[$issCode]['TotalFeeIss'] += $fee;
			}
			//echo $amtAth.'<br>';
		}
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('Daily Settlement Per Branch');
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
		
		foreach ($data as $key1 => $IC) {
			
			foreach ($IC as $key2 => $d) {
					
				if ($d['content'] === 'norecord') {
					$contentall = '<tr><td></td></tr><tr><td colspan="8" align="center"><h3><font color="red">"No activity for the day"</font></h3></td></tr>';
				} else {
			
					$net = $d['cntrIss'] + $d['cntrAcq'];
					$content = '<tr>
									<td width="450">'.$d['description'].'</td>
									<td width="380" align="right">'.$core->currency($d['AmtAthIss']).'</td>
									<td width="380" align="right">'.$d['cntrIss'].'</td>
									<td width="200" align="right">'.$core->currency($d['FeeIss']).'</td>
									<td width="380" align="right">'.$core->currency($d['AmtAthAcq']).'</td>
									<td width="380" align="right">'.$d['cntrAcq'].'</td>
									<td width="200" align="right">'.$core->currency($d['FeeAcq']).'</td>
									<td width="300" align="right">'.$net.'</td>
								</tr>';
					$contentall .= $content;
					$totals[$key1]['TotalNet'] += $net;
				}
			}
		
			//foreach ($data as $key => $d) {
			$pdf->AddPage('L', 'Letter');
			
			// Set some content to print
			$html = '<style>
			
			th {
				text-transform: uppercase;
				font-weight: bold;
				text-align: center;
			}
			</style>
			<h2>'.$instName.'</h2>
			<h1>DAILY SETTLEMENT PER BRANCH: '.$key1.'</h1>
			<h3>'. $branchName .'</h3>
			<h4>Transaction Date(s): '. $reportDate .'</h4>
			<table cellspacing="10" width="100%">
			<thead>
				<tr>
					<th width="400" align="left">BRANCH CODE</th>
					<th width="380" align="left">TOTAL TRANSACTION (AS ISSUER) TXN AMOUNT</th>
					<th width="380" align="left">TOTAL TRANSACTION (AS ISSUER) NUMBER</th>
					<th width="200" align="center">TRN FEES</th>
					<th width="380" align="left">TOTAL TRANSACTION (AS ACQUIRER) TXN AMOUNT</th>
					<th width="380" align="left">TOTAL TRANSACTION (AS ACQUIRER) NUMBER</th>
					<th width="200" align="center">TRN FEES</th>
					<th width="300" align="center">DAILY NET SETTLEMENT</th>
				</tr>
			</thead>
			<tbody>
				'.$contentall.'
				<tr>
					<td colspan="8">&nbsp;</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td align="right">TOTAL :</td>
					<td align="right">'.$core->currency($totals[$key1]['TotalAmtIss']).'</td>
					<td align="right">'.$totals[$key1]['TotalNumIss'].'</td>
					<td align="right">'.$core->currency($totals[$key1]['TotalFeeIss']).'</td>
					<td align="right">'.$core->currency($totals[$key1]['TotalAmtAcq']).'</td>
					<td align="right">'.$totals[$key1]['TotalNumAcq'].'</td>
					<td align="right">'.$core->currency($totals[$key1]['TotalFeeAcq']).'</td>
					<td align="right">'.$totals[$key1]['TotalNet'].'</td>
				</tr>
			</tfoot>
			</table>';
			// Print text using writeHTMLCell()
			$pdf->writeHTML($html, true, false, true, false, '');
			$contentall = '';
		}
		
		$pdf->Output('dailysettlementperbranch_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file dailysettlementperbranch.php */
/* Location: ./application/reports/dailysettlementperbranch.php */