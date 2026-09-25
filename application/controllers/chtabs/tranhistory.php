<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TranHistory extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(MONPOS_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coresys/host_model');
		$this->load->library('shortxml');
		
		$cache = $this->cache;
		$xml = $this->shortxml;
		$core = $this->core;
		$input = $this->input;
		
		$status = $input->get('status');
		
		$result = $this->host_model->getNodeList(NODE_TYPE, $status);
		
		$nodes = array();
		if ($result->num_rows() > 0) {
			
			foreach ($result->result_array() as $row) {
				$xml->setXML($row['xml1']);
				
				$nodes[] = array(
					'nodeName' => $row['nodename'],
					'nodeDesc' => $row['description'],
					'status' => $row['status'],
					'statDesc' => $row['statdesc'] ? $row['statdesc'] : $row['status'] .': Unknown',
					'sysDesc' => $xml->getValue('SYDESC') ? $xml->getValue('SYDESC') : 'Unknown',
					'prDesc' => $xml->getValue('PRDESC') ? $xml->getValue('PRDESC') : 'Unknown',
					'contact' => $xml->getValue('TELNO') ? $xml->getValue('TELNO') : 'Unknown',
					'address' => $xml->getValue('ADDR') ? $xml->getValue('ADDR') : 'Unknown',
					'lastCmd' => $row['cmddesc'] ? $row['cmddesc'] : 'Unknown',
					'cmdStat' => $row['cmddesc'] ? $row['cmddesc'] : 'Unknown',
					'img' => $this->core->getHostImage($row['status'])
				);
			}
			
			$message = NULL;
			$success = TRUE;
		} else {
			$success = FALSE;
			$message = 'No data';
		}
		
		if ($input->get('isFilter') !== '0') {
			$selected = $result->row_array();
			$nodeName = $selected['nodename'];
		} else {
			$selected = NULL;
			$nodeName = $input->get('nodeName');
		}
		
		$result->free_result();
		$result->next_result();
		
		$result = $this->host_model->getNodeTranHistory($nodeName);
		
		$details = array();
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$dtLog	  = $core->formatDate('m/d/Y h:i:s A', $row['dtlog']);
				$trxCode  = $row['trxcode'];
				
				//get trans desc
				if (!$trxList = $cache->get($this->core->getSessionID() . 'trxList')) {
					// Save into the cache for 5 minutes
					$this->load->model('coresys/atm_model');
					
					$result = $this->atm_model->getTransactionList();
					$trxList = $result->result_array();
					
					$result->free_result();
					$result->next_result();
					
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
					// Save into the cache for 5 minutes
					$this->load->model('coresys/atm_model');
					
					$result = $this->atm_model->getVoidCodeList();
					$vCodeList = $result->result_array();
					
					$result->free_result();
					$result->next_result();
					
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
				
				$mnemonic = $row['mnemonic'];
				$authname = $row['authname'];
				
				$logSeqNo = $row['logseqno'];
				$traceNo  = $row['chseqno'];
				$tpSeqNo  = $row['tpseqno'];
				$msgType  = $row['msgtype'];
				
				//get message desc
				if (!$msgTypes = $cache->get($this->core->getSessionID() . 'msgTypes')) {
					// Save into the cache for 5 minutes
					$this->load->model('coresys/atm_model');
					
					$result = $this->atm_model->getMessageTypes();
					$msgTypes = $result->result_array();
					
					$result->free_result();
					$result->next_result();
					
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
					$logSeqNo,
					$traceNo,
					$trxCode,
					$trxDesc,
					$mnemonic,
					$msgType .': '. $msgDesc,
					$authname,
					$vCode,
					$vDesc,
					$prodKey1,
					$amtReq,
					$amtAuth,
					$tpSeqNo,
					$fee1,
					$fee2
				);
			}
		}
		
		echo json_encode(array(
			'success'	 => TRUE,
			'dataTables' => TRUE,
			'tableIndex' => 0,
			'nodes'		 => $nodes,
			'details' 	 => $details,
			'selected'	 => $selected
		));
	}
	
}