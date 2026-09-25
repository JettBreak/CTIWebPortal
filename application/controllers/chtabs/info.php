<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		//$this->core->checkUserAllows(MONPOS_NO);
	}
	
	function index()
	{
		$this->load->model('coresys/host_model');
		$this->load->library('shortxml');
		
		$xml = $this->shortxml;
		$core = $this->core;
		$input = $this->input;
		
		$status = $input->get('status');
		
		$result = $this->host_model->getNodeList(NODE_TYPE, $status);
		
		$selected = NULL;
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
					'lastCmd' => '' ? '' : 'Unknown',
					'cmdStat' => $row['cmddesc'] ? $row['cmddesc'] : 'Unknown',
					'img' => $this->core->getHostImage($row['status'])
				);
			}
			
			if ($input->get('isFilter') !== '0') {
				$selected = $result->row_array();
				$nodeName = $selected['nodename'];
			} else {
				$nodeName = $input->get('nodeName');
			}
			
			$message = NULL;
			$success = TRUE;
		} else {
			$success = FALSE;
			$message = 'No data';
		}
		
		$result->free_result();
		$result->next_result();
		
		$details = array();
		
		echo json_encode(array(
			'success'	 => TRUE,
			'dataTables' => FALSE,
			'tableIndex' => NULL,
			'nodes'		 => $nodes,
			'details' 	 => $details,
			'selected'	 => $selected
		));
	}
	
}