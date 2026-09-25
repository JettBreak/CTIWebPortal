<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RequestCustomer extends CI_Controller {
  
  private $fileVersion = '1.10.00';
  
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
    
    $this->load->library('version');

    $file = basename(__DIR__) . '/' . basename(__FILE__);

    $verified = $this->version->validate($file, $this->fileVersion);

    if (!$verified) {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'Module is out of date. Please contact software administrator.'
      ));
      exit();
    }
  }
  
  function index($id = NULL)
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/customer_model');
    $this->load->library('shortxml');   
    $this->load->helper('url');

    //
    $cache    = $this->cache;
    $xml    = $this->shortxml;
    $xmldata  = $this->shortxml;
    $core   = $this->core;
    $customer = $this->customer_model;  
    $input    = $this->input;
    
    $userID   = $core->getUserID();
    $sessionID  = $core->getSessionID();

    $dataxml = array();
    $xmlstring = '';

    if ($id === NULL) { //if no param set then fetch from cache
      if ($cust = $cache->get($this->core->getSessionID() . 'cust')) {
        $id = $cust['cifseqno'];
        $xmldata->setXML($cust['xmlstring']);
        $xmlstring = $cust['xmlstring'];

      } else {
        redirect('customer/search2');
        exit();
      }
    }
    
    $result   = $customer->searchCustomer($id, '', '', '', $userID, $sessionID);
    
    $row = $result->row_array();

    //echo print_r($dataxml);
    
    if ($result->num_rows() > 0 || $id) { 
    
      $data['cifseqno']   = $id;//'0001';//
      $data['blobPic']  = //$row['blobpic'];
      
      $myPrefix     = ucfirst(strtolower($xmldata->getVALUE('TITLE')));
      $data['lastName']   = utf8_decode($xmldata->getVALUE('LNAME'));//$row['lastname'];
      $data['firstName']  = utf8_decode($xmldata->getVALUE('FNAME'));//$row['firstname'];
      $data['middleName'] = utf8_decode($xmldata->getVALUE('MNAME'));//$row['middlename'];
      $data['suffix']   = '';//$row['suffix'];
      
      //xml1 ------------------------------------------------------------------------------------------ xml1
      //$xml->setXML($row['xml1']);
      
      $data['email']    = '';//$xmlstring;//$xmldata->getValue('EMAIL');
      $eNotify      = 'N';//$xml->getValue('ENOT');
      
      //e-mail notification
      if ($eNotify === 'Y') {
        $data['eNotify'] = 'checked';
      } elseif ($data['email'] != NULL) {
        $data['eNotify'] = NULL;
      } else {
        $data['eNotify'] = 'disabled';
      }
      //end xml1 -------------------------------------------------------------------------------------- end xml1
      
      //xml2 ------------------------------------------------------------------------------------------ xml2
      //$xml->setXML($row['xml2']);

      $prefixes   = $core->getNamePrefixes();
      
      $data['bDay']     = '';//$xml->getValue('BIRTHDAY') !== '' ? $xml->getValue('BIRTHDAY') : date('m/d/Y');
      $data['bPlace']   = '';//$xml->getValue('BIRTHPLACE');
      $data['sss']    = '';//$xml->getValue('SSS');
      $data['tin']    = '';//$xml->getValue('TIN');
      $data['occupation'] = '';//$xml->getValue('OCCUPATION');
      $data['race']     = '';//$xml->getValue('RACE');
      $myGender     = !in_array($xmldata->getVALUE('TITLE'), $prefixes['Male']) ? 'Female' : 'Male';
      $myCivilStat    = '';//$xml->getValue('CIVIL');
      $data['nationality'] = '';//$xml->getValue('NATIONALITY');
      //end xml2 -------------------------------------------------------------------------------------- end xml2
      
      //xml3 ------------------------------------------------------------------------------------------ xml3
      //$xml->setXML($row['xml3']);
      
      $hPhone       = '';//$xml->getValue('HPHONE');
      $data['hFax']     = '';//$xml->getValue('HFAX');
      $data['hOther']   = '';//$xml->getValue('HOTHER');
      $hNotify      = 'N';//$xml->getValue('HNOT');
      
      if ($hNotify === 'Y') {
        $data['hNotify'] = 'checked';
      } elseif ($hPhone !== '') {
        $data['hNotify'] = NULL;
      } else {
        $data['hNotify'] = 'disabled';
      }
      
      //$data['hAreaCode']  = $core->getAreaCode($hPhone);
      $data['hPhone']   = $hPhone;//$core->getPhoneNumber($hPhone);
      
      $bPhone       = '';//$xml->getValue('BPHONE');
      $data['bFax']     = '';//$xml->getValue('BFAX');
      $data['bOther']   = '';//$xml->getValue('BOTHER');
      $bNotify      = 'N';//$xml->getValue('BNOT');
      
      if ($bNotify === 'Y') {
        $data['bNotify'] = 'checked';
      } elseif ($bPhone !== '') {
        $data['bNotify'] = NULL;
      } else {
        $data['bNotify'] = 'disabled';
      }
      
      $oPhone       = '';//$xml->getValue('OPHONE');
      $data['oFax']     = '';//$xml->getValue('OFAX');
      $oNotify      = 'N';//$xml->getValue('ONOT');
      
      if ($oNotify === 'Y') {
        $data['oNotify'] = 'checked';
      } elseif ($oPhone !== '') {
        $data['oNotify'] = '';
      } else {
        $data['oNotify'] = 'disabled';
      }
      $data['oAreaCode']  = $core->getAreaCode($oPhone);
      $data['oPhone']   = $oPhone;//$core->getPhoneNumber($oPhone);
      
      $mPhone       = '';//$xml->getValue('MPHONE');
      $mNotify      = 'N';//$xml->getValue('MNOT');
      
      if ($mNotify === 'Y') {
        $data['mNotify'] = 'checked';
      } elseif ($mPhone !== '') {
        $data['mNotify'] = NULL;
      } else {
        $data['mNotify'] = 'disabled';
      }
      $mobile       = $core->getPhoneNumber($mPhone);
      $data['mPhone']   = '';//$mobile['num'];
      //end xml3 -------------------------------------------------------------------------------------- end xml3
      
      //contact details
      $myCountry = 'Philippines';//set default country
      $myAddrType = 'Home';//set default address type
      
      $data['address1'] = NULL;
      $data['address2'] = NULL;
      $data['city']     = NULL;
      $data['province'] = NULL;
      $data['prvnisReq'] = $core->isISOCustomer() ? '' : 'validate[required]';
      $data['prvnreqSign'] = $core->isISOCustomer() ? '' : '*';
      $data['zipCode']  = NULL;
      
      if (/*$row['xml4']*/ '' !== '') {
        //$xml->setXML($row['xml4']);
        if (/*$xml->getValue('HADDRESS')*/ '1' !== '') {
          $data['address1']   = '';//$xml->getValue('HADDRESS');
          $data['addr1isReq'] = $core->isISOCustomer() ? '' : 'validate[required]';
          $data['addr1reqSign'] = $core->isISOCustomer() ? '' : '*';
          $data['address2']   = '';//$xml->getValue('HADDRESS2');
          $data['city']   = '';//$xml->getValue('HCITY');
          $data['cityisReq'] = $core->isISOCustomer() ? '' : 'validate[required]';
          $data['cityreqSign'] = $core->isISOCustomer() ? '' : '*';
          $data['province']   = '';//$xml->getValue('HPROV');
          $data['zipCode']  = '';//$xml->getValue('HZIPCODE');
          $data['zipCisReq'] = $core->isISOCustomer() ? '' : 'validate[required,custom[onlyNumberSp]] numbersOnly';
          $data['zipCreqSign'] = $core->isISOCustomer() ? '' : '*';
          $myCountry      = $myCountry;//$xml->getValue('HCOUNTRY');
          $myAddrType     = 'Home';
        }
      } elseif (/*$row['xml5']*/ '1' !== '') {
        //$xml->setXML($row['xml5']);
        if (/*$xml->getValue('BADDRESS')*/ '1' !== '') {
          $data['address1']   = '';//$xml->getValue('BADDRESS');
          $data['addr1isReq'] = $core->isISOCustomer() ? '' : 'validate[required]';//$xml->getValue('BADDRESS');
          $data['addr1reqSign'] = $core->isISOCustomer() ? '' : '*';
          $data['address2']   = '';//$xml->getValue('BADDRESS2');
          $data['city']   = '';//$xml->getValue('BCITY');
          $data['cityisReq'] = $core->isISOCustomer() ? '' : 'validate[required]';
          $data['cityreqSign'] = $core->isISOCustomer() ? '' : '*';
          $data['province']   = '';//$xml->getValue('BPROV');
          $data['zipCode']  = '';//$xml->getValue('BZIPCODE');
          $data['zipCisReq'] = $core->isISOCustomer() ? '' : 'validate[required,custom[onlyNumberSp]] numbersOnly';
          $data['zipCreqSign'] = $core->isISOCustomer() ? '' : '*';
          $myCountry      = $myCountry;//$xml->getValue('BCOUNTRY');
          $myAddrType     = 'Office';
        }
      }
      
      $errNo    = '';//$row['errno'];
      $errMsg   = '';//$row['errmsg'];
      
      
      $gender   = $core->getGender();
      $status   = $core->getCivilStats();
      $countries  = $core->getCountries();
      $addrTypes  = $core->getAddressTypes();
      
      if (!$cellBIN = $cache->get($this->core->getSessionID() . 'cellBIN')) {
        $result->free_result();
        $result->next_result();
  
        $cellBIN = $customer->getCellBIN()->result_array();
        $cache->save($this->core->getSessionID() . 'cellBIN', $cellBIN, CACHE_TTL);
      }
      
      //image source
      if ($data['blobPic'] == 0) {
        $data['srcImg'] = 'images/nullphoto.jpg';
        $data['upMsg']  = 'Upload Photo';
      } else {
        $data['srcImg'] = 'customer/photo/'. $data['cifseqno'] .'/'. $data['blobPic'] .'?'. time();
        $data['upMsg']  = 'Change Photo';
      }//end
      
      //prefixes combobox
      $data['prefixes'] = NULL;
      foreach ($prefixes[$myGender] as $prefix) {
        $selected = ($myPrefix === $prefix ? ' selected="selected"' : NULL);
        $data['prefixes'] .= '<option'. $selected .'>'. $prefix .'</option>';
      }
      $data['prefix'] = $myPrefix; //default prefix
      
      //gender radio buttons
      $data['gender'] = NULL;
      foreach ($gender as $sex) {
        $checked = ($sex === $myGender ? ' checked' : NULL);
        $data['gender'] .= '<input type="radio" name="custGender" value="'. $sex .'"'. $checked .' />'. $sex;
      }
      //civil status combobox
      $data['civilStats'] = NULL;
      foreach ($status as $stats) {
        $selected = ($stats === $myCivilStat ? ' selected' : NULL);
        $data['civilStats'] .= '<option value="'. $stats .'"'. $selected .'>'. $stats .'</option>';
      }
      //countries combobox
      $data['countries'] = NULL;
      foreach ($countries->children() as $country) {
        $selected = ($country == $myCountry ? ' selected' : NULL);
        $data['countries'] .= '<option value="'. $country .'"'. $selected .'>'. $country .'</option>';
      }
      
      //address types
      $data['addrTypes'] = NULL;
      foreach ($addrTypes as $type) {
        $selected = ($type === $myAddrType ? ' selected' : NULL);
        $data['addrTypes'] .= '<option value="'. $type .'"'. $selected .'>'. $type .'</option>';
      }
      
      //cellBIN combobox      
      $data['cellBIN'] = NULL;        
      foreach ($cellBIN as $row) {
        $bin = $row['codevalue'];
        //$selected = ($bin === $mobile['bin'] ? 'selected' : NULL);
        //$data['cellBIN'] .= '<option value="'. $bin .'" '. $selected .'>'. $bin .'</option>';
        
      }

      $data['cellBIN'] = '';
      
      $data['sessionExp'] = $this->core->getSessionExp();
      $this->load->view('customer/requestCustomer', $data);
    } else {
      //$this->load->view('customer/search2');
      echo json_encode(array(
        'auth' => FALSE,
        'message' => '"CIF number not found."'
      ));
    }
  }
  
  function submit()
  {
    $this->load->model('coreapp/customer_model');
    $this->load->model('coreapp/card_model');
    
    $customer = $this->customer_model;
    $core   = $this->core;
    $session  = $this->session;
    $input    = $this->input;
    //cifgrpseqno
    //custkey
    //ciftype
    $prefix = strtoupper($input->post('custPrefix', TRUE));
    $fName  = strtoupper($input->post('custFirstName', TRUE));
    $mName  = strtoupper($input->post('custMiddleName', TRUE));
    $lName  = strtoupper($input->post('custLastName', TRUE));
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
    
    $bDay      = $input->post('custBDate', TRUE);
    $bPlace    = $input->post('custBPlace', TRUE);
    $sss     = $input->post('custSSS', TRUE);
    $tin     = $input->post('custTIN', TRUE);
    $occupation  = $input->post('custOccupation', TRUE);
    $gender    = $input->post('custGender', TRUE);
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
    $hAreaCode  = NULL;//$input->post('custHomePhone', TRUE);
    $hPhone   = $input->post('custHomePhone', TRUE);
    if (isset($_POST['custHomeNotify'])) {
      $hNotify = $input->post('custHomeNotify', TRUE);
    } else {
      $hNotify = 'N';
    }
    
    $oAreaCode  = $input->post('custAreacodeOffice', TRUE);
    $oPhone   = $input->post('custOfficePhone', TRUE);
    if (isset($_POST['custOfficeNotify'])) {
      $oNotify = $input->post('custOfficeNotify', TRUE);
    } else {
      $oNotify = 'N';
    }
    
    $mAreaCode  = $input->post('custMobileBIN', TRUE);
    $mPhone   = $input->post('custMobile', TRUE);
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
    
    $custkey   = $input->post('custCifseqno', TRUE);
    $country   = $input->post('custCountry', TRUE);
    $addressType = $input->post('custAddressType', TRUE);
    $street1   = $input->post('custStreet1', TRUE);
    $street2   = $input->post('custStreet2', TRUE);
    $city      = $input->post('custCity', TRUE);
    $zipCode   = $input->post('custZipCode', TRUE);
    $province    = $input->post('custProvince', TRUE);
    
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
    
    $userAudit   = $core->getUserID();
    $ipAddress   = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $brseqno   = $core->getBranchID();
    $sessionID   = $core->getSessionID();
    //$sessionid    = 'asfsa536325189';
    
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

    $result = $customer->insertCustomerApproval(
      2,      //cifgrpseqno
      $custkey,     //custkey
      'INDV',   //ciftype
      $prefix,
      $fName,
      $mName,
      $lName,
      $suffix,
      1,      //uniqtype
      '',     //uniqval
      $userAudit,
      '',     //override
      $ipAddress,
      $workstation, //wkstn
      $xml1,
      $xml2,
      $xml3,
      $xml['4'],
      $xml['5'],
      $xml['6'],
      $xml['7'],
      $xml['8'],
      $blobPic,
      0,      //blobsgn
      $brseqno,
      $sessionID
    );
    
    $row  = $result->row_array(); 
    $errNo  = $row['errno'];
    $errMsg = $row['errmsg'];
    
    if ($errNo != 0) {
      $success = FALSE;
    } else {
      $success = TRUE;

/*      $result->free_result();
      $result->next_result();

      $auditXML = '';

      $auditXML .= '<old></><new></><field>Customer Request</><details></>';
      $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CUST','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
 */
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
      'file_name'   => 'temp',
      'upload_path'   => $path,
      'allowed_types' => VALID_IMG_FORMATS,
      'max_size'    => MAX_IMG_SIZE,
      'max_width'   => MAX_IMG_WIDTH,
      'max_heigt'   => MAX_IMG_HEIGHT,
      'overwrite'   => TRUE
    );
    
    $this->load->library('upload', $config);
    
    $data = NULL;
    $error = NULL;
    if ($this->upload->do_upload($img)) {
      $uploaded = TRUE;
      $data = $this->upload->data();
      
      $config = array(
        'source_image'  => $data['full_path'],
        'width'     => 100,
        'height'    => 100
      );
      
      $this->load->library('image_lib', $config);
      $this->image_lib->resize();
    } else {
      $uploaded = FALSE;
      $error    = $this->upload->display_errors('', '');
    }
    
    echo json_encode(array(
      'uploaded'  => $uploaded,
      'file_name' => $data['file_name'],
      'message' => $error
    ));
  }
}
/* End of file edit.php */
/* Location: ./application/views/customer/edit.php */