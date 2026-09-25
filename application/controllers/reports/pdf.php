<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AllIBFTTrx extends CI_Controller {	

	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(OTHERCARDHOLDERATOURTERM_NO);
	}
	
	function preview()
	{
		
		
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('All IBFT Transaction');
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
			<h1>ALL IBFT TRANSACTION: '.$key.'</h1>
			<h3>'. $branchName .'</h3>
			<h4>Transaction Date(s): '. $reportDate .'</h4>
			<table cellspacing="10" width="100%">
				<tr>
					<th width="140">LOGKEY</th>
					<th width="120">TRACE NO.</th>
					<th width="170">SEQUENCE NO.</th>
					<th width="120">TIME</th>
					<th width="120">DATE</th>
					<th width="100">LUNO</th>
					<th width="280">BANK ID / CARD #</th>
					<th width="120" align="center">ISSUER</th>
					<th width="250">FROM <br>ACCOUNT</th>
					<th width="60">TY</th>
					<th width="60">TC</th>
					<th width="160">TRXCODE</th>
					<th width="100">BANK</th>
					<th width="240">TO <br>ACCOUNT</th>
					<th width="70">VCD</th>
					<th width="290" align="center">REMARKS</th>
					<th width="130">TRX AMT</th>
					<th width="150">AMT AUTH</th>
				</tr>
				'. $d['content'] .'
				<tr>
					<td colspan="14">&nbsp;</td>
				</tr>
			</table>';
			
			$html .= '<table cellspacing="18" width="100%">
				<tr>
					<th align="center">&nbsp;</th>
					<th align="center">Apprvd IBFTWDL</th>
					<th align="center">Apprvd IBFTTRN</th>
					<th align="center">IBFTWDL Revrsl</th>
					<th align="center">IBFTTRN Revrsl</th>
					<th align="center">Rejctd IBFTWDL</th>
					<th align="center">Rejctd IBFTTRN</th>
				</tr>
				<tr align="right">
					<td><b>TOTAL:</b></td>
					<td>'. $core->currency($d['ibftwdlApprv']) .'</td>
					<td>'. $core->currency($d['ibfttrnApprv']) .'</td>
					<td>'. $core->currency($d['ibftwdlRvrsl']) .'</td>
					<td>'. $core->currency($d['ibfttrnRvrsl']) .'</td>
					<td>'. $core->currency($d['ibftwdlRejct']) .'</td>
					<td>'. $core->currency($d['ibfttrnRejct']) .'</td>
				</tr>
			</table>';
			// Print text using writeHTMLCell()
			$pdf->writeHTML($html, true, false, true, false, '');
		}
		$pdf->Output('allibfttrx_' . $dateSaveFormat . '.pdf', 'I');
	}
}
/* End of file allibfttrx.php */
/* Location: ./application/reports/allibfttrx.php */