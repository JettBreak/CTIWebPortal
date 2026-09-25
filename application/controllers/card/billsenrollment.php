<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BillsEnrollment extends CI_Controller {
	
	function index()
	{		
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('core');
		$this->load->library('shortxml');
		
		$card 	 = $this->card_model;
		$session = $this->session;
		$core	 = $this->core;
		$xml 	 = $this->shortxml;
		
		$info = $session->userdata('cardInfo');
		
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardBIN']	= $info['cardBIN'];
		$data['custName'] 	= $info['custName'];
		$data['cardStatus'] = $info['cardStatus'];
		$data['cardType'] 	= $info['cardType'];
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->getCardBillsLink($data['prseqno'], $userAudit, $sessionID);
		
		$data['billsLink']	= NULL;
		$parentx 			= NULL;
		foreach ($result->result_array() as $row) {
			$xml->setXML($row['xml1']);
			
			$inst 		= $row['parent'];
			$ptr	 	= $row['bpayptr'];
			$subsNo	 	= $row['subscriberno'];
			$subsName 	= $row['subscribername'];
			$parent 	= $row['parentseqno'];
			
			if ($parent != $parentx) {
				$parentx 	= $parent;
				$data['billsLink'] .= '
					<tr id="'. $parent .'">
						<td>'. $inst.'</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				';
			}
				
			$data['billsLink'] .= '
				<tr idref="'. $parent .'">
					<td>'. $inst.'</td>
					<td>'. $ptr .'</td>
					<td>'. $subsNo .'</td>
					<td>'. $subsName .'</td>
					<td>'. $parent .'</td>
				</tr>
			';
		}
		
		$this->load->view('card/billsenrollment', $data);
	}
}
/* End of file billsenrollment.php */
/* Location: ./application/controllers/card/billsenrollment.php */