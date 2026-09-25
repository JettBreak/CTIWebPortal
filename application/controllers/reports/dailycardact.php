<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DailyCardAct extends CI_Controller {	

	function index()
	{
		$this->load->library('core');
		$this->core->checkUserAllows(DAILYCARDACT_NO);
		
		if ($this->input->get('norecord', TRUE) === 'true') {
			$data['showMsg'] = '1';
		} else {
			$data['showMsg'] = '0';
		}
		$data['date'] = date('m/d/Y');
		$this->load->view('reports/dailycardact', $data);
	}
	
	function preview()
	{
		$this->load->model('coresys/reports_model');
		$this->load->library('core');
		$this->load->library('pdf');
		$this->load->helper('url');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$input	 = $this->input;
		$pdf 	 = $this->pdf;		
		
		$userName = $core->getUserName();
		$instName = $core->getInstName();
		$branchName = $core->getBranchName();
		
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
		
		$branchCode = $core->getBranchCode();
		$cardHolder = $input->post('cardHolderOpts', TRUE);
		$reportLog = $input->post('reports', TRUE);
		
		$result = $reports->getTransactionLog($reportLog, $branchCode, $dtFrom, $dtTo, $cardHolder);	
		
		
		if ($result->num_rows() === 0) {
			$this->load->helper('url');
			redirect('/pdfviewer/norecord');
			exit();
		}

		$data = NULL;
		foreach ($result->result_array() as $row) {
			$data .= '<tr>
				<td width="300">'. $core->formatDate('m/d/Y H:i:s', $row['dtlog']) .'</td>
				<td width="150" align="center">'. $row['logseqno'] .'</td>
				<td width="150" align="center">'. $row['chseqno'] .'</td>
				<td width="100" align="center">'. $row['bankcode'] .'</td>
				<td width="400">'. $row['prkey1'] .'</td>
				<td width="500">'. $row['description'] .'</td>
				<td width="300" align="right">'. $core->currency($row['amtath']) .'</td>
				<td width="150" align="right">'. $row['fee1'] .'</td>
				<td width="300">'. $row['acct1'] .'</td>
				<td width="300">'. $row['acct2'] .'</td>
			</tr>';
		}
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('Daily Card Activity Report');
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
			font-weight: bold;
			text-align: center;
		}
		</style>
		<h2>'.$instName.'</h2>
		<h1>DAILY CARD ACTIVITY REPORT</h1>
		<h3>'.$branchName.'</h3>
		<h4>'. $reportDate .'</h4>
		<table cellspacing="18" width="100%">
			<thead>
				<tr>
					<th width="300">TRANS. DATE/TIME</th>
					<th width="150">FUSION SEQ NO.</th>
					<th width="150">AUTH SEQ NO.</th>
					<th width="100">BANK</th>
					<th width="400">CUSTOMER CARD</th>
					<th width="500">TRANSACTION</th>
					<th width="300">TRANSACTION AMOUNT</th>
					<th width="150">FEE</th>
					<th width="300">SOURCE ACCOUNT</th>
					<th width="300">DESTINATION ACCOUNT</th>
				</tr>
			</thead>
			<tbody>'. $data .'<tbody>
		</table>';
		
		// Print text using writeHTMLCell()
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->Output('dailycardact_' .$dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file dailycardact.php */
/* Location: ./application/reports/dailycardact.php */