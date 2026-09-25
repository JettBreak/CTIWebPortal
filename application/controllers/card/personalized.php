<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Personalized extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDORDER_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		if ($cust = $this->cache->get($this->core->getSessionID() . 'cust')) {
			$this->load->model('coreapp/card_model');
		
			$cache = $this->cache;
			$card  = $this->card_model;
			$input = $this->input;
		
			$data['cifseqno'] = $cust['cifseqno'];
			$data['custName'] = $cust['fullName'];
			$data['embossName'] = NULL;
			
			//get branches
			if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
				$this->load->model('coreapp/branch_model');
				$result = $this->branch_model->getBranchList();
			
				$branches = $result->result_array();
				
				$result->free_result();
				$result->next_result();
				$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
			}
			
			$data['branches'] = NULL;
			
			if (count($branches) > 0) {
				foreach ($branches as $row) {
					//if user branch is not allowed to monitor users from other branches
					if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
						$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
						break;
					}
					$data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
				}
			} else {
				$data['branches'] = '<option value="">No Branches Defined</option>';
			}
			//end
		
			//cache cardBIN
			if (!$cardBIN = $cache->get($this->core->getSessionID() . 'cardBIN')) {
				$this->load->model('coreapp/card_model');
				
				$result = $this->card_model->getCardBIN();
				$cardBIN = $result->result_array();
				$cache->save($this->core->getSessionID() . 'cardBIN', $cardBIN, CACHE_TTL);
				
				$result->free_result();
				$result->next_result();
			}
			
			$data['cardBIN'] = NULL;
			foreach ($cardBIN as $row)
			{
				$data['cardBIN'] .= '<option value="'. $row['codevalue'] .'">'. $row['codevalue'] .'</option>';
			}
			
			$data['productCodes'] = NULL;
			//get product code
			if ($this->core->hasProductCode()) {
				//cache product codes	
				if (!$productCodes = $cache->get($this->core->getSessionID() . 'productCodes')) {
					$this->load->model('coreapp/card_model');
					
					$result = $this->card_model->getProductCodes();
					$productCodes = $result->result_array();
					$cache->save($this->core->getSessionID() . 'productCodes', $productCodes, CACHE_TTL);
					
					$result->free_result();
					$result->next_result();
				}
				
				$options = NULL;
				if (count($productCodes) > 0) {
					foreach ($productCodes as $row)
					{
						$options .= '<option value="'. $row['codeseqno'] .'">'. strtoupper($row['codevalue']) .'</option>';
					}
				} else {
					$options = '<option value="">No Product Codes Defined</option>';
				}
				$prCodesHtml = '<tr>
					<td><label for="productCode">Product Code:</label></td>
					<td>
						<select name="productCode" id="productCode" style="width:212px" class="validate[required]">
						'. $options .'
						</select>
					</td>
				</tr>	';
				
				$data['productCodes'] = $prCodesHtml;
			}
			//end product code
			
			//cache cardType
			if (!$cardType = $cache->get($this->core->getSessionID() . 'cardTypeP')) {
				$this->load->model('coreapp/card_model');
				
				$result = $this->card_model->getCardType('Y');
				$cardType = $result->result_array();
				$cache->save($this->core->getSessionID() . 'cardTypeP', $cardType, CACHE_TTL);
			}
			
			$data['cardType'] = NULL;
			$data['acctDesc'] = NULL;
			foreach ($cardType as $row)
			{
				//hidden input "acctDesc"
				if ($data['acctDesc'] === NULL) {
					$data['acctDesc'] = $row['description'];
				}
				$val  = $row['accttype'];
				$desc = $row['description'];
				$data['cardType'] .= '<option value="'. $val .'">'. $desc .'</option>';
			}
			
			$data['title'] = 'Personalized Card Request';
			$data['label'] = 'Submit';
			$data['formAction'] = 'card/personalized/submit';
			$data['waitMsg'] = 'Sending request...';
			$data['confirmMsg'] = 'Submit card order request?';
		
			$this->load->view('card/personalized', $data);
		} else {
			$this->load->helper('url');
			redirect('customer/search/cardrequest');
		}
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('shortxml');
		
		$card		= $this->card_model;
		$session	= $this->session;
		$core		= $this->core;
		$input		= $this->input;
		
		$acctType 	= $input->post('cardType', TRUE);
		$acctDesc	= $input->post('acctDesc', TRUE);
		$cifseqno	= $input->post('custID', TRUE);
		$custName 	= $input->post('custName', TRUE);
		
		if ($core->isHeadOffice()) {
			$branchID = $input->post('branchx', TRUE);
		} else {
			$branchID = $core->getBranchID();
		}
		
		$cardEmboss = $input->post('embossName', TRUE);
		$bin 		= $input->post('cardBIN', TRUE);
		
		$xml = '<DETAIL>'. $custName . '</>'.
				'<EMBOSSNAME>'. $cardEmboss . '</>'.
				'<CUSTNO>'. $cifseqno . '</>';
		
		//for QCRB
		if ($this->core->hasProductCode()) {
			$productCode = $input->post('productCode', TRUE);
			if (isset($productCode)) {
				$xml .= '<PRCDCODE>'. $productCode .'</>';
			}
		}
		
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit  = $core->getUserID();
		$userOverride = $session->userdata('userOverride');
		$sessionID	= $core->getSessionID();
		
		$result = $card->insertCardOrder(
			$acctType,
			$acctDesc,
			$branchID,
			$branchID,
			'1',
			$bin,
			'Y',
			$custName,
			$xml,
			$ipAddress,
			$workstation,
			$userAudit,
			$userOverride,
			$sessionID
		);
		
		$session->unset_userdata('userOverride');
		
		$row = $result->row_array();
		
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Card order successful';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'errorno' => $row['errno']/*,
			'hex' => $hex,
			'xml' => $xml*/
		));
	}
}
/* End of file personalized.php */
/* Location: ./application/controllers/card/personalized.php */