<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LocNew extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->load->model('coresys/misc_model');
    $this->core->checkUserAllows(LOCATION_NO);
    
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
    
    $core = $this->core;
    
    $result = NULL;
    
    if (!$locTypes = $this->cache->get($this->core->getSessionID() . $this->core->getSessionID() . 'locTypes')) {     
      $result = $this->misc_model->getLocationTypes();
      $locTypes = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      $this->cache->save($this->core->getSessionID() .$this->core->getSessionID() . 'locTypes', $locTypes, CACHE_TTL);
    }
    
    $data['locationtType'] = NULL;
    
    if (count($locTypes) > 0) {
      foreach ($locTypes as $row) {
        $data['locationtType'] .= '<option value="'. $row['codeseqno'] .'">'. $row['codevalue'] .'</option>';
      }
    } else {
      $data['locationtType'] .= '<option value="">No Location Types Defined</option>';
    }
    
    $data['title'] = 'New Location';
    $data['submitBtnMsg'] = 'Create new location?';
    $data['formAction'] = 'maintenance/locnew/submit';
    $data['hiddenInput'] = NULL;
    $data['location'] = NULL;
    $data['city'] = NULL;

    if ($core->isHeadOffice()) {
      $this->load->model('coreapp/area_model');
    
      $result = $this->area_model->getAreaList();
      
      $data['areaInput'] = '<select name="regionCode" id="regionCode" style="width:262px" class="validate[required]">';
      
      if ($result->num_rows() > 0) {  
        foreach ($result->result_array() as $row) {
          $selected = $core->getRegionCode() === $row['regioncode'] ? ' selected' : NULL;
          $data['areaInput'] .= '<option value="'. $row['regioncode'] .'"'. $selected .'>'. $row['areaname'] .'</option>';
        }
      } else {
        $data['areaInput'] .= '<option value="">No Area Defined</option>';
      }
      $data['areaInput'] .= '</select>';
      
      $result->free_result();
      $result->next_result();
      
      $result = $this->area_model->getBranchListByArea($core->getRegionCode());
      
      $data['brInput'] = '<select name="brCode" id="brCode" style="width:262px" class="validate[required]">';
      
      foreach ($result->result_array() as $row) {
        //$selected = $core->getRegionCode() === $row['regioncode'] ? ' selected' : NULL;
        $data['brInput'] .= '<option value="'. $row['brcode'] .'">'. $row['brname'] .'</option>';
      }
      $data['brInput'] .= '</select>';
      
    } else {
      $data['areaInput'] = '<input type="text" id="areaName" style="width:250px" value="'. $core->getAreaName() .'" readonly/>';
      $data['brInput'] = '<input type="text" name="brCode" id="brCode" style="width:250px" value="'. $core->getBranchName() .'" readonly/>';
    }
    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/location', $data);
  }
  
  function submit()
  {
    $this->load->model('coreapp/user_model');
    $this->load->model('coreapp/card_model');

    $brseqno     = $this->core->getBranchID();
    $ipaddress   = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
      
    $row = $this
      ->user_model
      ->checkLogin(
        $this->core->getUserID(),
        $this->core->getSessionID()
      )
      ->row_array();
    
    if ($row['errno'] !== '8') { //if session valid
    
      if ($this->core->isHeadOffice()) { //if admin
        $brCode = $this->input->post('brCode', TRUE);
      } else {
        $brCode = $this->core->getBranchCode();
      }

       $locData     = str_pad($this->input->post('location', TRUE), 25, ' ', STR_PAD_RIGHT).str_pad($this->input->post('cityInput', TRUE), 13, ' ', STR_PAD_RIGHT).'PH';
    
      $result = $this->misc_model->insertLocation(
        $locData,
        $brCode,
        $this->input->post('locationtype', TRUE)
      );
      
      $row = $result->row_array();
      
      if ($row['errno'] > 0) {
        $success = FALSE;
        $message = $row['errmsg'];
      } else {
        $success = TRUE;
        $message = 'New location added successfully';

        $result->free_result();
        $result->next_result();

        $auditXML = '';
        
        $auditXML .= '<old></><new></><field>New Location</><details>Location: '.strtoupper($this->input->post('location', TRUE)).'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        
      }
    } else {
      $success = FALSE;
      $message = $row['errmsg'];
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'errorno' => $row['errno']
    ));
  }
}