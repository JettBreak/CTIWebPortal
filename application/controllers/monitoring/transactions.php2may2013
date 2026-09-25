<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transactions extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONTRANS_NO);
	}
	
    function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		//get void codes
		if (!$vCodes = $this->cache->get($this->core->getSessionID() . 'vCodes')) {
			$this->load->model('coresys/atm_model');
			$result = $this->atm_model->getVoidCodes();
			
			$vCodes = NULL;
			foreach ($result->result_array() as $row) {
				$vCodes[] = intval($row['codevalue']);
			}
			
			$this->cache->save($this->core->getSessionID() .'vCodes', $vCodes, CACHE_TTL);
		}
				
		$vCodes = json_encode($vCodes);
		$data['vCodes'] = $vCodes;
		$data['date'] = date('m/d/Y');
		$this->load->view('monitoring/transactions', $data);
	}
	
	function getData()
	{
		$this->load->model('coresys/atm_model');
		$this->load->library('shortxml');
		
		$atm   = $this->atm_model;
		$xml   = $this->shortxml;
		$core  = $this->core;
		$input = $this->input;
		
		$finswitch = $core->getFINSWITCH();
		
		$cmd 	 = $input->post('cmd', true);
		$dtLog	 = $core->formatDate('Y-m-d', $input->post('dtlog', TRUE));
		$limit 	 = $input->post('limit', true);
		$details = array();
		$cnt	 = 0;
		
		$result = $atm->getTransactionLog($cmd, $dtLog, $limit);
		$cnt = intval($result['numrows']);
		$resultArr = $result['result'];
		
		if ($cnt > 0) {					
			foreach ($resultArr as $row) {
				$time 		= $core->formatDate('H:i:s', $row['dtlog']);
				$logkey 	= $row['logseqno'];
				$transCode 	= $row['trxcode'];
				$msgType	= $row['msgtype'];
				$chname		= $row['chname'];
				
				// if ACQ is true
				if (in_array($row['termtype'], array('ATM','SAF')) && in_array($chname, array($finswitch, 'SAF')) ) {
					
					$traceNo = str_pad($row['chseqno'], 6, '0', STR_PAD_LEFT);
					$sequenceNo = str_pad($row['tpseqno'], 6, '0', STR_PAD_LEFT);
					
				} else {
					
					$traceNo = str_pad($row['tpseqno'], 6, '0', STR_PAD_LEFT);
					$sequenceNo = str_pad($row['chseqno'], 6, '0', STR_PAD_LEFT);
				}
				
				$mnemonic	= $row['mnemonic'];
				$msgDesc	= $row['msgdesc'];
				$msgType	= $row['msgtype'] .': '. $msgDesc;
				$terminal	= $row['termtype'];
				$auth		= $row['authname'];
				$vCode		= $row['sysvcode'] !== '0' ? $row['sysvcode'] : NULL;
				$vDesc		= $row['shortdescription'];
				$prodkey1	= $row['prkey1'];
				$amtreq		= $row['amtreq'];
				$amtauth	= $row['amtath'];
				$termid		= $row['termcode'];

				$details[] = array(
					$time,
					$logkey,
					$traceNo,
					$sequenceNo,
					$transCode,
					$mnemonic,
					$msgType,
					$terminal,
					$auth,
					$vCode,
					$vDesc,
					$prodkey1,
					$amtreq,
					$amtauth,
					$termid,
					$chname
				);
			}
				
			$success = TRUE;
		} else {
			$success = FALSE;
		}
		
		echo json_encode(array(
			'success' => $success,
			'details' => $details,
			'count'   => $cnt
		));
	}
}