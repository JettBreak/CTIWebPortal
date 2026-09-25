<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Enroll extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CUSTNEW_NO);
		
		$this->load->model('coreapp/user_model');

		$result = $this->user_model->checkLogin($this->core->getUserID(), $this->core->getSessionID());

		$row = $result->row_array();

		if (intval($row['errno']) > 0) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Invalid Login Session. Please relogin'
			));
			exit();
		}
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		$cache = $this->cache;
		$core  = $this->core;
		
		$prefixes  = $core->getNamePrefixes();
		$gender	   = $core->getGender();
		$status    = $core->getCivilStats();
		$countries = $core->getCountries();
		$addrTypes = $core->getAddressTypes();
		
		if (!$cellBIN = $cache->get($this->core->getSessionID() . 'cellBIN')) {
			$this->load->model('coreapp/customer_model');
			// Save into the cache for 5 minutes
			$cellBIN = $this->customer_model->getCellBIN()->result_array();
			$cache->save($this->core->getSessionID() . 'cellBIN', $cellBIN, CACHE_TTL);
		}
		
		$data['prefixes'] 	= NULL;
		$data['gender']		= NULL;
		$data['civilStats']	= NULL;
		$data['countries']	= NULL;
		$data['cellBIN']	= NULL;
		$data['addrTypes']  = NULL;
		
		//get name prefixes
		foreach ($prefixes as $pre) {
			//$data['prefixes'] .= '<option value="'. $pre .'">'. $pre .'</option>';
		}
		//get gender list
		foreach ($gender as $sex) {
			$selected = ('Male' == $sex ? 'checked' : '');
			$data['gender'] .= '<input type="radio" name="custGender" value="'. $sex .'" '. $selected .' />'. $sex;
		}
		//get civil status list
		foreach ($status as $stats) {
			$data['civilStats'] .= '<option value="'. $stats .'">'. $stats .'</option>';
		}
        //get country list
		foreach ($countries->children() as $country) {
			$selected = ($country == 'Philippines' ? ' selected' : NULL);
			$data['countries'] .= '<option value="'. $country .'"'. $selected .'>'. $country .'</option>';
		}
		
		foreach ($addrTypes as $type) {
			$data['addrTypes'] .= '<option value="'. $type .'">'. $type .'</option>';
		}
		
		//get cell BIN           
		foreach ($cellBIN as $row) {
			//$data['cellBIN'] .= '<option value="'. $row['codevalue'] .'">'. $row['codevalue'] .'</option>';
		}
		$data['cellBIN'] = '';
		
		// Batch Upload
		$data['uploadBtn'] = NULL;
		if($core->hasBatchCardUpload()) {
			$data['uploadBtn'] = NULL;//'<button id="uploadBtn">Batch Upload</button>';
		}
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->output->cache(CACHE_TTL);
		$this->load->view('customer/enroll', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/customer_model');
		
		$customer = $this->customer_model;
		$core	  = $this->core;
		$session  = $this->session;
		$input	  = $this->input;
		//cifgrpseqno
		//custkey
		//ciftype
		$prefix = strtoupper($input->post('custPrefix', TRUE));
		$fName 	= strtoupper($input->post('custFirstName', TRUE));
		$mName 	= strtoupper($input->post('custMiddleName', TRUE));
		$lName 	= strtoupper($input->post('custLastName', TRUE));
		$suffix = strtoupper($input->post('custSuffix', TRUE));
		//uniqtype
		//uniqval
		//useraudit
		//override
		//wkstn
		
		$xml = array(
			'4' => '',
			'5' => '',
			'6' => '',
			'7' => '',
			'8' => ''
		);
			
		//xml1
		$email = $input->post('custEmail', TRUE);
		if (isset($_POST['custEmailNotify'])) {
			$eNotify = $input->post('custEmailNotify', TRUE);
		} else {
			$eNotify = 'N';
		}
		
		$xml1 = '<EMAIL>'. $email .'</>';
		$xml1 .= '<ENOT>'. $eNotify .'</>';
		//end xml1
		
		//xml2
		
		$bDay 		 = $input->post('custBDate', TRUE);
		$bPlace 	 = $input->post('custBPlace', TRUE);
		$sss 		 = $input->post('custSSS', TRUE);
		$tin 		 = $input->post('custTIN', TRUE);
		$occupation  = $input->post('custOccupation', TRUE);
		$gender		 = $input->post('custGender', TRUE);
		$civilStatus = $input->post('custCivilStatus', TRUE);
		$nationality = $input->post('custNationality', TRUE);
		
		$xml2 = '<BIRTHDAY>'. $bDay .'</>';
		$xml2 .= '<BIRTHPLACE>'. $bPlace .'</>';
		$xml2 .= '<SSS>'. $sss .'</>';
		$xml2 .= '<TIN>'. $tin .'</>';
		$xml2 .= '<OCCUPATION>'. $occupation .'</>';
		$xml2 .= '<RACE></>';
		$xml2 .= '<SEX>'. $gender .'</>';
		$xml2 .= '<CIVIL>'. $civilStatus .'</>';
		$xml2 .= '<NATIONALITY>'. $nationality .'</>';
		//end xml2
		
		//xml3
		$hAreaCode 	= $input->post('custAreaCodeHome', TRUE);
		$hPhone 	= $input->post('custHomePhone', TRUE);
		if (isset($_POST['custHomeNotify'])) {
			$hNotify = $input->post('custHomeNotify', TRUE);
		} else {
			$hNotify = 'N';
		}
		
		$oAreaCode 	= $input->post('custAreacodeOffice', TRUE);
		$oPhone 	= $input->post('custOfficePhone', TRUE);
		if (isset($_POST['custOfficeNotify'])) {
			$oNotify = $input->post('custOfficeNotify', TRUE);
		} else {
			$oNotify = 'N';
		}
		
		$mAreaCode 	= $input->post('custAreaCodeMobile', TRUE);
		$mPhone 	= $input->post('custMobile', TRUE);
		if (isset($_POST['custMobileNotify'])) {
			$mNotify = $input->post('custMobileNotify', TRUE);
		} else {
			$mNotify = 'N';
		}
		
		if ($mPhone === '') {
			$mPhone = NULL;
		} else {
			$mPhone = $mAreaCode . $mPhone;
		}
		
		$xml3 = '<HPHONE>'. $hAreaCode . $hPhone .'</>';
		$xml3 .= '<OPHONE>'. $oAreaCode . $oPhone .'</>';
		$xml3 .= '<MPHONE>'. $mPhone .'</>';
		$xml3 .= '<HNOT>'. $hNotify .'</>';
		$xml3 .= '<ONOT>'. $oNotify .'</>';
		$xml3 .= '<MNOT>'. $mNotify .'</>';
		//end xml3
		
		$country 	 = $input->post('custCountry', TRUE);
		$addressType = $input->post('custAddressType', TRUE);
		$street1 	 = $input->post('custStreet1', TRUE);
		$street2 	 = $input->post('custStreet2', TRUE);
		$city 		 = $input->post('custCity', TRUE);
		$zipCode 	 = $input->post('custZipCode', TRUE);
		$province 	 = $input->post('custProvince', TRUE);
		
		switch ($addressType) {
			case 'Home':
				$xmlPrefix = 'H';
				$xmlNum = '4';
				break;
			case 'Office':
				$xmlPrefix = 'B';
				$xmlNum = '5';
				break;
			/*case 'Office':
				$xmlPrefix = 'O';
				$xmlNum = 6;
				break;
			case 'Corporate':
				$xmlPrefix = 'C';
				$xmlNum = 7;
				break;*/
		}
		
		$xml[$xmlNum] = '<'. $xmlPrefix .'ADDRESS>'. $street1 .'</>';
		$xml[$xmlNum] .= '<'. $xmlPrefix .'ADDRESS2>'. $street2 .'</>';
		$xml[$xmlNum] .= '<'. $xmlPrefix .'CITY>'. $city .'</>';
		$xml[$xmlNum] .= '<'. $xmlPrefix .'PROV>'. $province .'</>';
		$xml[$xmlNum] .= '<'. $xmlPrefix .'ZIPCODE>'. $zipCode .'</>';
		$xml[$xmlNum] .= '<'. $xmlPrefix .'COUNTRY>'. $country .'</>';
		$xml[$xmlNum] .= '<'. $xmlPrefix .'MAIL></>';
		
		/*if ($addresstype == 'home') {
			$xml4 = '<HADDRESS>'. $street1 .'</>';
			$xml4 .= '<HADDRESS2>'. $street2 .'</>';
			$xml4 .= '<HCITY></>';
			$xml4 .= '<HPROV>'. $province .'</>';
			$xml4 .= '<HZIPCODE>'. $zipcode .'</>';
			$xml4 .= '<HCOUNTRY>'. $country .'</>';
			$xml4 .= '<HMAIL></>';
		} else {
			$xml4 = '';
		}*/
		//xml4
		//end xml4
		
		//xml5
		//xml6
		//xml7
		//xml8
		//blobpic
		//blobsgn
		
		$userAudit 	 = $core->getUserID();
		$ipAddress	 = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$brseqno	 = $core->getBranchID();
		$sessionID	 = $core->getSessionID();
		//$sessionid		= 'asfsa536325189';
		
		if (is_uploaded_file($_FILES['custImage']['tmp_name'])) {
			//insert blob data (customer picture)
			$desc = 'CUSTOMER PICTURE - '. $lName .', '. $fName .' '. $mName;
			$blobData = file_get_contents($_FILES['custImage']['tmp_name']);
			
			$cifseqno = $customer->getNextCustomerID();
			$blobPic  = $customer->getNextBlobPic();
			$result   = $customer->insertCustPic($cifseqno, $desc, $blobData, $userAudit, $sessionID);
		
			$result->free_result();
			$result->next_result();
		} else {
			$blobPic = 0;
		}

		$result = $customer->insertCustomer(
			2,			//cifgrpseqno
			'',			//custkey
			'INDV',		//ciftype
			$prefix,
			$fName,
			$mName,
			$lName,
			$suffix,
			1,			//uniqtype
			'',			//uniqval
			$userAudit,
			'',			//override
			$ipAddress,
			$workstation,	//wkstn
			$xml1,
			$xml2,
			$xml3,
			$xml['4'],
			$xml['5'],
			$xml['6'],
			$xml['7'],
			$xml['8'],
			$blobPic,
			0,			//blobsgn
			$brseqno,
			$sessionID
		);
		
		$row 	= $result->row_array();	
		$errNo 	= $row['errno'];
		$errMsg	= $row['errmsg'];
		
		if ($errNo != 0) {
			$success = FALSE;
		} else {
			$success = TRUE;
		}
		
		echo json_encode(array(
			'success' => $success,
			'errorno' => $errNo,
			'message' => $errMsg
		));
	}
	
	function upload()
	{	
		$path = realpath(APPPATH .'../uploads');
		$img  = 'custImage';
		
		$config = array(
			'file_name'		=> 'temp',
			'upload_path' 	=> $path,
			'allowed_types' => VALID_IMG_FORMATS,
			'max_size'		=> MAX_IMG_SIZE,
			'max_width'		=> MAX_IMG_WIDTH,
			'max_heigt'		=> MAX_IMG_HEIGHT,
			'overwrite'		=> TRUE
		);
		
		$this->load->library('upload', $config);
		
		$data  = NULL;
		$error = NULL;
		if ($this->upload->do_upload($img)) {
			$uploaded = TRUE;
			$data = $this->upload->data();
			
			$config = array(
				'source_image' => $data['full_path'],
				'width'		   => 100,
				'height'	   => 100
			);
			
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
		} else {
			$uploaded = FALSE;
			$error 	  = $this->upload->display_errors('', '');
		}
		
		echo json_encode(array(
			'uploaded'	=> $uploaded,
			'file_name'	=> $data['file_name'],
			'message'	=> $error
		));
	}
}
/* End of file enroll.php */
/* Location: ./application/contollers/customer/enroll.php */