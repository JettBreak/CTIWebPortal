<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BatchOrder extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDORDER_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$cache = $this->cache;
		
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
				<td><label for="productCode">Product Code: <span class="red">*</span></label></td>
				<td>
					<select name="productCode" id="productCode" style="width:200px" class="validate[required]">
					'. $options .'
					</select>
				</td>
			</tr>	';
			
			$data['productCodes'] = $prCodesHtml;
		}
		//end product code
		
		//cache cardType
		if (!$cardType = $cache->get($this->core->getSessionID() . 'cardType')) {
			$this->load->model('coreapp/card_model');
			
			$result = $this->card_model->getCardType('N');
			$cardType = $result->result_array();
			$cache->save($this->core->getSessionID() . 'cardType', $cardType, CACHE_TTL);
			
			$result->free_result();
			$result->next_result();
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
		
		$data['title'] = 'New Card Order Request';
		$data['label'] = 'Submit';
		$data['formAction'] = 'card/batchorder/submit';
		$data['reqQty'] = 1;
		$data['waitMsg'] = 'Sending request...';
		$data['confirmMsg'] = 'Submit card order request?';
		$this->output->cache(CACHE_TTL);
		$this->load->view('card/batchorder', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		
		$core = $this->core;
		$input = $this->input;
		
		$acctType = $input->post('cardType', TRUE);
		$acctDesc = $input->post('acctDesc', TRUE);
		$uBranchID = $core->getBranchID();
		
		if ($core->isHeadOffice()) {
			$branchID = $input->post('branchx', TRUE);
		} else {
			$branchID = $core->getBranchID();
		}
		
		$count = $input->post('cardCount', TRUE);
		$cardBIN = $input->post('cardBIN', TRUE);
		$xml = '';
		
		//for QCRB
		if ($this->core->hasProductCode()) {
			$productCode = $input->post('productCode', TRUE);
			if (isset($productCode)) {
				$xml .= '<PRCDCODE>'. $productCode .'</>';
			}
		}
		
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$userOverride = $this->session->userdata('userOverride');
		$sessionID = $core->getSessionID();
		
		if ($result = $this->card_model->insertCardOrder(
			str_pad($acctType,2,'0',STR_PAD_LEFT),
			$acctDesc,
			$branchID,
			$uBranchID, 
			$count,
			$cardBIN,
			'N',//is personalized?
			'',
			$xml,
			$ipAddress,
			$workstation, 
			$userAudit,
			$userOverride,
			$sessionID
		)) {
			$success = TRUE;
		} else {
			$success = FALSE;
		}
		$this->session->unset_userdata('userOverride');

		echo json_encode(array(
			'success' => $success,
			'message' => 'Card order successfully completed'
		));
	}
}
/* End of file batchorder.php */
/* Location: ./application/controllers/card/batchorder.php */