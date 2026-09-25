<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EditBatchOrder extends CI_Controller {
	
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
			$selected = ($cardOrder['cardBIN'] === $row['codevalue'] ? ' selected' : NULL);
			$data['cardBIN'] .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
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
			$selected = ($cardOrder['cardType'] === $desc ? ' selected' : NULL);
			$data['cardType'] .= '<option value="'. $val .'"'. $selected .'>'. $desc .'</option>';
		}
		
		$data['title'] = 'Edit Card Order Request';
		$data['label'] = 'Save';
		$data['formAction'] = 'card/editbatchorder/submit';
		$data['reqQty'] = $cardOrder['reqQty'];
		$data['waitMsg'] = 'Updating request...';
		$data['confirmMsg'] = 'Update card order request?';
		//$this->output->cache(CACHE_TTL);
		$this->load->view('card/batchorder', $data);
	}
	
	function submit()
	{		
		if ($cardOrder = $_SESSION['cardOrder']) {
			$orderNo = $cardOrder['orderNo'];
			$isPersonalized = $cardOrder['isPersonalized'];
		} else {
			echo json_encode(array(
				'success' => FALSE,
				'message' => 'Cache has expired'	
			));
			exit();
		}
		
		$this->load->model('coreapp/card_model');
		
		$card  = $this->card_model;	
		$core  = $this->core;
		$input = $this->input;
		
		$accType   = $input->post('cardType', TRUE);
		$uBranchID = $core->getBranchID();
		
		if ($core->isHeadOffice()) {
			$branchID = $input->post('branchx', TRUE);
		} else {
			$branchID = $core->getBranchID();
		}
		
		$bin = $input->post('cardBIN', TRUE);
		
		if ($isPersonalized === 'Y') {
			$custName = trim(substr($cardOrder['customer'], strpos($cardOrder['customer'], ':') + 1));
			$embossName = $input->post('embossName', TRUE);
			$cifseqno = substr($cardOrder['customer'], 0, strpos($cardOrder['customer'], ':'));
			
			$count = 1;
			$xml = '<DETAIL>'. $custName . '</>'.
				'<EMBOSSNAME>'. $embossName . '</>'.
				'<CUSTNO>'. $cifseqno . '</>';
		} else {
			$count = $input->post('cardCount', TRUE);
			$xml = '';
			
			//for QCRB product code
			if ($this->core->hasProductCode()) {
				$productCode = $input->post('productCode', TRUE);
				if (isset($productCode)) {
					$xml .= '<PRCDCODE>'. $productCode .'</>';
				}
			}
		}
		
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result = $card->updateCardOrder(
			$orderNo,
			$accType,
			$branchID,
			$uBranchID, 
			$count,
			$bin,
			$isPersonalized,
			$xml,
			$ipAddress,
			$workstation,
			$userAudit,
			$sessionID
		);
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Card order successfully changed'
		));
	}
}
/* End of file editbatchorder.php */
/* Location: ./application/controllers/card/editbatchorder.php */