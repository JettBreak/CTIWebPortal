<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ISS extends CI_Controller {	

	function index()
	{
		if ($this->input->get('norecord', TRUE) === 'true') {
			$data['showMsg'] = '1';
		} else {
			$data['showMsg'] = '0';
		}
		
		$data['title'] = 'ISSUER PER BRANCH SUMMARY';
		$data['date'] = date('m/d/Y');
		$data['formAction'] = 'reports/iss/preview';
		$this->load->view('reports/approvedtrx', $data);
	}
	
	function preview()
	{
		$this->load->model('coresys/reports_model');
		$this->load->library('core');
		$this->load->library('pdf');
		$this->load->helper('url');
		
		$reports = $this->reports_model;
		$core 	 = $this->core;
		$pdf 	 = $this->pdf;		
		
		//$core->checkUserAllows();
		
		$userName = $core->getUserName();
		$instName = $core->getInstName();
		$branchName = $core->getBranchName();
		
		$branchCode = $core->getBranchCode();
		$transDate  = $core->formatDate('Y-m-d', $this->input->post('trxDate', TRUE));
		
		$userAudit  = $core->getUserID();
		$sessionID  = $core->getSessionID();
		
		$result = $reports->getSumIssuerPerBranch($branchCode, $transDate);
		
		if ($result->num_rows() === 0) {
			redirect('/pdfviewer/norecord');
			exit();
		}
		/*$result = array();
		
		for ($i = 0; $i <= 100; $i ++) {
			$rand = array("IBFT","INQ","WDL","REV","TRN","ADV","DEP","STAT","CKBK","PAY");
			
			$result[] = array(
				'trxtype1' => $rand[rand(0, count($rand)-1)],
				'brcode' => '001',
				'totamt' => rand(1, 100),
				'totctr' => rand(1, 100)
			);
		}*/
		
		$currentDate = $core->formatDate('F j, Y', $this->input->post('trxDate', TRUE));
		
		$data = NULL;
		
		foreach ($result as $row) {
			
			$wdrls = 0;
			$wdlAmt = 0;
			$advcs = 0;
			$advAmt = 0;
			$rvctr = 0;
			$wdlRversl = 0;
			$deps = 0;
			$balInq = 0;
			$ftrn = 0;
			$ibft = 0;
			$stmr = 0;
			$chkr = 0;
			$paym = 0;
			$reserve1 = '0.00';
			$reserve2 = '0.00';
			
			switch ($row['trxtype1']) {
				case 'IBFT':
					$ibft = $row['totctr'];
					break;
				case 'INQ':
					$balInq = $row['totctr'];
					break;
				case 'WDL':
					$wdrls = $row['totctr'];
					$wdlAmt = $row['totamt'];
					break;
				case 'REV':
					$rvctr = $row['totctr'];
					$wdlRversl = $row['totamt'];
					break;
				case 'TRN':
					$ftrn = $row['totctr'];
					break;
				case 'ADV':
					$advcs = $row['totctr'];
					$advAmt = $row['totamt'];
					break;
				case 'DEP':
					$deps = $row['totctr'];
					break;
				case 'STAT':
					$stmr = $row['totctr'];
					break;
				case 'CKBK':
					$chkr = $row['totctr'];
					break;
				case 'PAY':
					$paym = $row['totctr'];
					break;
				default:
					break;
			}
			
			$data .= '<tr>'.
				'<td>'. $row['brcode'] .'</td>'.//branch code
				'<td>'. $wdrls .'</td>'.//wdrls
				'<td>'. $wdlAmt .'</td>'.//wdl amount
				'<td>'. $advcs .'</td>'.//advcs
				'<td>'. $advAmt .'</td>'.//adv amount
				'<td>'. $rvctr .'</td>'.//rvctr
				'<td>'. $wdlRversl .'</td>'.//wdl rversl
				'<td>'. $deps .'</td>'.//deps
				'<td>'. $balInq .'</td>'.//balinq
				'<td>'. $ftrn .'</td>'.//ftrn
				'<td>'. $ibft .'</td>'.//ibft
				'<td>'. $stmr .'</td>'.//stmr
				'<td>'. $chkr .'</td>'.//chkr
				'<td>'. $paym .'</td>'.//paym
			'</tr>';
		}
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($userName);
		$pdf->SetTitle('Approved Transactions');
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
		h1, h2, h3, h4, h5 {
			text-align: center;
		}
		th {
			text-transform: uppercase;
			font-size: 7pt;
			font-weight: bold;
		}
		</style>
		<h2>'.$instName.'</h2>
		<h1>APPROVED TRANSACTION, ISSUER PER BRANCH SUMMARY</h1>
		<h3>'. $branchName .'</h3>
		<h4>'. $currentDate .'</h4>
		<table cellspacing="18" width="100%">
			<tr>
				<th>Branch</th>
				<th>WDRWLS</th>
				<th>WDL Amount</th>
				<th>ADVCS</th>
				<th>ADV Amount</th>
				<th>RVCTR</th>
				<th>WDL RVERSL</th>
				<th>DEPS</th>
				<th>BALINQ</th>
				<th>FNDTRN</th>
				<th>IBFT</th>
				<th>STMREQ</th>
				<th>CHKREQ</th>
				<th>PAYS</th>
			</tr>
			'. $data .'
		</table>';
		// Print text using writeHTMLCell()
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->Output('preview.pdf', 'I');
	}
}
/* End of file iss.php */
/* Location: ./application/reports/iss.php */