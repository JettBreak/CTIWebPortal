<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChangeLink extends CI_Controller {
	
	function index()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$cardNo = '3223220010000079';
		
		$result = $this->card_model->getCardInfo($cardNo);
		
		//if ($result->num_rows() > 0) {
			$row = $result->row_array();
		//}
		$acctType = $row['accttype'];
		$data['prseqno'] = $row['prseqno'];
		$data['dtEnrolled'] = $this->core->formatDate('F j, Y g:i A', $row['dtenroll']);
		$data['cardNo'] = $cardNo;
		$data['status'] = $row['statdesc'];
		$data['acctdesc'] = $row['acctdesc'];
		//$data['embossName'] = '';
		
		$prefix = $row['prefix'];
		$fName = $row['fname'];
		$mName = $row['mname'];
		$lName = $row['lname'];
		$suffix = $row['suffix'];
		
		$data['custName'] = $prefix .' '. $lName .', '. $fName .' '. $mName .' '. $suffix;
		
		$this->load->view('card/changelink', $data);
	}
}