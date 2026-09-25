<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EditPersonalized extends CI_Controller {
	
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
		
		if ($cardOrder = $_SESSION['cardOrder']) {
		} else {
			$this->load->helper('url');
			redirect('card/orderrequest');
			exit();
		}
		
		$this->load->model('coreapp/card_model');
	
		$cache = $this->cache;
		$card  = $this->card_model;
		$input = $this->input;
	
		$data['cifseqno'] = substr($cardOrder['customer'], 0, strpos($cardOrder['customer'], ':'));
		$data['custName'] = trim(substr($cardOrder['customer'], strpos($cardOrder['customer'], ':') + 1));
		$data['embossName'] = $cardOrder['embossName'];
		
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
				
				$selected = $cardOrder['brseqno'] === $row['brseqno'] ? ' selected' : NULL;
				
				//if user branch is not allowed to monitor users from other branches
				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				$data['branches'] .= '<option value="'. $row['brseqno'] .'"'. $selected .'>'. $row['brname'] .'</option>';
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
			$selected = $cardOrder['cardBIN'] == $row['codevalue'] ? ' selected' : NULL;
			$data['cardBIN'] .= '<option'. $selected .'>'. $row['codevalue'] .'</option>';
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
					$selected = $cardOrder['prcdcode'] === $row['codeseqno'] ? ' selected' : NULL;
					$options .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. strtoupper($row['codevalue']) .'</option>';
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
			
			$selected = $cardOrder['cardType'] === $desc ? ' selected' : NULL;
			$data['cardType'] .= '<option value="'. $val .'"'. $selected .'>'. $desc .'</option>';
		}
		
		$data['title'] = 'Edit Personalized Card Request';
		$data['label'] = 'Save';
		$data['formAction'] = 'card/editbatchorder/submit';
		$data['waitMsg'] = 'Updating transaction...';
		$data['confirmMsg'] = 'Update card order request?';
			
		$this->load->view('card/personalized', $data);
	}
}