<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Host extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('coresys/host_model');
		$this->load->library('core');
		//$this->core->checkUserAllows(MONHOST_NO);
	}
	
	function index()
	{
		$result = $this->host_model->getCHStats();
		
		$statusList = NULL;
		foreach ($result->result_array() as $row) {
			$statusList .= '<option value="'. $row['codeseqno'] .'">'. $row['codevalue'] .'</option>';
		}
		
		$data['statusList'] = $statusList;
		
		$result->free_result();
		$result->next_result();
		
		$this->load->view('monitoring/host', $data);
	}
	
	function getHosts()
	{
		$this->load->library('shortxml');
		
		$xml = $this->shortxml;
		
		$result = $this->host_model->getNodeList(NODE_TYPE, -1);
		
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
		
		$result->free_result();
		$result->next_result();
		
		//header('Content-type: application/json');
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'nodeList' => $nodes
		));
	}
}