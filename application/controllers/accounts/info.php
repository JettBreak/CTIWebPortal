<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ACCNTINFO_NO);
	}
	
	function index()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$card 	 = $this->card_model;
		$core 	 = $this->core;
		
		$result = $card->getAccountBIN();
		$row 	= $result->row_array();
		$num 	= $row['NUM'];
		
		$branchID		   = $core->getBranchID();
		$data['accountNo'] = $num . '00'. $branchID;
		
		$this->output->cache(CACHE_TTL);
		$this->load->view('accounts/info', $data);
	}
	
	function verify()
	{	
		$this->load->model('coreapp/card_model');
		$card  = $this->card_model;
		$input = $this->input;
		
		$accntNo = $input->post('accntNo1', TRUE) . $input->post('accntNo2', TRUE);
		
		$result = $card->getAccountInfoByKey($accntNo);
		
		if ($result->num_rows() > 0) {
			$row = $result->row_array();
			
			$prseqno	= $row['prseqno'];
			$accntDesc	= $row['acctdesc'];
			$authMode 	= $row['authmode'];
			$accntStats	= $row['statdesc'];
			$accntOwn 	= ($row['cifseqno'] ? $row['lastname'] .', '. $row['firstname'] .' '. $row['middlename'] : NULL);
			
			$result->free_result();
			$result->next_result();
			
			$result = $card->getAcctCardLink($prseqno);
			
			$cardLink = NULL;
			foreach ($result->result_array() as $row) {
				//add zeros to cifseqno
				$len = 8 - strlen($row['cifseqno']);
				$cifseqno = NULL;
				for ($i = 1; $i <= $len; $i++) {
					$cifseqno .= '0'; 
				}
				$cifseqno .= $row['cifseqno'];
				//end
				
				$cardLink[] = array(
					$row['prkey'],
					$cifseqno,
					'',
					$row['description']
				);
			}
					
			echo json_encode(array(
				'verified' => TRUE, 
				'prseqno'	 => $prseqno,
				'accntDesc'	 => $accntDesc,
				'authMode' 	 => $authMode,
				'accntStats' => $accntStats,
				'accntOwn' 	 => $accntOwn,
				'cardLink'	 => $cardLink
			));
		} else {
			echo json_encode(array(
				'verified' => FALSE
			));
		}
	}
}
/* End of file info.php */
/* Location: ./application/contollers/accounts/info.php */