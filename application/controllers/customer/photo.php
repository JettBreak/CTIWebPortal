<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Photo extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CUSTMGMT_NO);
	}
	
    function index($id, $blobPic)
	{
		$this->load->model('coreapp/customer_model');
		
		$customer	= $this->customer_model;
		$core		= $this->core;
		$input 		= $this->input;
		
		//$id 		= $input->get('id', TRUE);
		//$blobpic 	= $input->get('blobpic', TRUE);
		
		$userAudit	= $core->getUserID();
		$sessionID	= $core->getSessionID();
		
		$result 	= $customer->getCustomerPicture($id, $blobPic, $userAudit, $sessionID);
		$row 		= $result->row_array();
		
		$result->free_result();
		
		header('Content-type: image/jpeg');
		echo $row['blobdata'];
	}
}
/* End of file photo.php */
/* Location: ./application/controllers/customer/photo.php */