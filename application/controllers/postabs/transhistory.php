<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TransHistory extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONPOS_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/pos_model');
		
		$cache = $this->cache;
		$pos   = $this->pos_model;
		$core  = $this->core;
		$input = $this->input;
		
		if ($core->canMon()) {
			$branchCode = $input->get('brcode');
			$locCode = $input->get('loccode');
		} else {
			$branchCode = $core->getBranchCode();
			$locCode = 0;
		}
		$status = $input->get('status');
		
		$result  = $pos->getPOSList($branchCode, $locCode, $status);
		
		if ($input->get('isFilter') !== '0') {
			$selected = $result->row_array();
			$terminalCode = $selected['termcode'];
		} else {
			$selected = NULL;
			$terminalCode = $input->get('poscode');
		}
		
		$posList = $core->showPOSList($result);
		
		$result->free_result();
		$result->next_result();
		
		$result = $pos->getPOSTransactionHistory($terminalCode);
	
		$details = array();//do not set to NULL. Must be an empty array
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$dtLog	  = $core->formatDate('F j, Y h:i:s A', $row['dtlog']);
				$trxCode  = $row['trxcode'];
				
				//get trans desc
				if (!$trxList = $cache->get($this->core->getSessionID() . 'trxList')) {
					$result->free_result();
					$result->next_result();
					// Save into the cache for 5 minutes
					$trxList = $pos->getTransactionList()->result_array();
					
					array_push($trxList, array(
						'trxcode' => NULL, 
						'description' => 'Unknown')
					); 
					
					$cache->save($this->core->getSessionID() . 'trxList', $trxList, CACHE_TTL);
				}
				
				$trxDesc = NULL;
				foreach ($trxList as $trxList)
				{
					if ($trxCode > 0) {
						$trxDesc = 'Unknown';
						if (in_array($trxCode, $trxList)) {
							$trxDesc = ucwords(strtolower($trxList['description']));
							
							$mustCaps = array('Sa ', 'Ca ', 'Atm ');
							$replacement = array('SA ', 'CA ', 'ATM ');
							
							$trxDesc = str_replace($mustCaps, $replacement, $trxDesc);
							break;
						}
					}
				}
				//end
				
				$vCode = $row['sysvcode'];
				
				//get void desc
				if (!$vCodeList = $cache->get($this->core->getSessionID() . 'vCodeList')) {
					$result->free_result();
					$result->next_result();
					// Save into the cache for 5 minutes
					$vCodeList = $pos->getVoidCodeList()->result_array();
					
					array_push($vCodeList, array(
						'sysvcode' => NULL, 
						'shortdescription' => 'Unknown')
					);
					
					$cache->save($this->core->getSessionID() . 'vCodeList', $vCodeList, CACHE_TTL);
				}
				
				$vDesc = NULL;
				foreach ($vCodeList as $vCodeList)
				{
					if ($vCode > 0) {
						$vDesc = 'Unknown';
						if (in_array($vCode, $vCodeList)) {
							$vDesc = $vCodeList['shortdescription'];
							break;
						}
					}
				}
				//end
				
				$logSeqNo = $row['logseqno'];
				$traceNo  = $row['chseqno'];
				$tpSeqNo  = $row['tpseqno'];
				$msgType  = $row['msgtype'];
				
				//get message desc
				if (!$msgTypes = $cache->get($this->core->getSessionID() . 'msgTypes')) {
					$result->free_result();
					$result->next_result();
					// Save into the cache for 5 minutes
					$msgTypes = $pos->getMessageTypes()->result_array();
					
					array_push($msgTypes, array(
						'codeseqno' => NULL, 
						'codevalue' => 'Unknown')
					);
					
					$cache->save($this->core->getSessionID() . 'msgTypes', $msgTypes, CACHE_TTL);
				}
				
				$msgDesc = NULL;
				foreach ($msgTypes as $msgTypes) {
					if ($msgType > 0) {
						$msgDesc = 'Unknown';
						if (in_array($msgType, $msgTypes)) {
							$msgDesc = $msgTypes['codevalue'];
							break;
						}
					}
				}
				//end
				
				$amtReq	  = $core->currency($row['amtreq']);
				$amtAuth  = $core->currency($row['amtath']);
				$fee1	  = $core->currency($row['fee1']);
				$fee2	  = $core->currency($row['fee2']);
				$prodKey1 = $row['prkey1'];
				$prodKey2 = $row['prkey2'];
				
				$details[] = array(
					$dtLog,
					$trxCode,
					$trxDesc,
					$vCode,
					$vDesc,
					$logSeqNo,
					$traceNo,
					$tpSeqNo,
					$msgType .': '. $msgDesc,
					$amtReq,
					$amtAuth,
					$fee1,
					$fee2,
					$prodKey1,
					$prodKey2
				);
			}
		}
		
		echo json_encode(array(
			'success' 	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 0,
			'pos' 		 => $core->compressOutput($posList),
			'details' 	 => $details,
			'selected'	 => $selected
		));
	}
}
/* End of file transhistory.php */
/* Location: ./application/contollers/postabs/transhistory.php */