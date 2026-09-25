<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zones extends CI_Controller {
	
    function index()
	{	
		$this->load->view('zones');
	}
	
	function getData()
	{
		$this->load->model('coreapp/customer_model');
		
		$customer = $this->customer_model;
		$input	  = $this->input;
		
		$city 	  = $input->post('zoneCity', TRUE);
		$province = $input->post('zoneProv', TRUE);
		$zipcode  = $input->post('zoneZip', TRUE);
		
		$result = $customer->getZones($city, $province, $zipcode);
		
		if ($result->num_rows) {		
			$zipCodes = NULL;
			foreach ($result->result_array() as $row) {
				$data[] = array($row['zipname'], $row['areacode'], $row['zipcode']);
			}
		
			$success = TRUE;
			$message = $data;
		} else {
			$success = FALSE;
			$message = 'No record found';
		}
			
		echo json_encode(array(
			'success' => $success,
			'result'  => $message
		));
	}
}
/* End of file zipcodes.php */
/* Location: ./application/controllers/zipcodes.php */