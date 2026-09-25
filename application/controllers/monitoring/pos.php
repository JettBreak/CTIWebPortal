<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POS extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(MONPOS_NO);
    
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
    $this->load->model('coreapp/area_model');
    $this->load->model('coresys/pos_model');
    $this->load->library('shortxml');
    
    $cache = $this->cache;
    $pos  = $this->pos_model;
    $core = $this->core;
    $xml = $this->shortxml;
    
    $result = $pos->getTerminalLocations('POS');
    
    $locArr = $result->result_array();
    
    $result->free_result();
    $result->next_result();
    
    //if no terminals defined
    
    //echo $locArr;
    
    $brcodesWithPOS = array();
    foreach ($locArr as $row) {
      $brcodesWithPOS[] = $row['brcode'];
    }
    
    $brcodes = implode(',', $brcodesWithPOS);
    
    $result = $this->area_model->getTerminalAreas($brcodes);
    
    if ($result === FALSE) {
      echo " NO POS TERMINAL DEFINED ";
      $this->load->helper('url');
      //redirect('welcome');
      exit();
    }
    
    $areaArr = $result->result_array();
    
    $result->free_result();
    $result->next_result();
    
    //ATM info to Information Tab and Header    
    if ($core->canMon()) {
      $branchCode = 0;
      $locCode = 0;
      //branches combobox
      if (!$brArr = $this->cache->get($this->core->getSessionID() . 'branches')) {
        $this->load->model('coreapp/branch_model');
        $result = $this->branch_model->getBranchList();
      
        $brArr = $result->result_array();
        
        $result->free_result();
        $result->next_result();
        $this->cache->save($this->core->getSessionID() .'branches', $brArr, CACHE_TTL);
      }
      
      $option = NULL;
      //$areaFirst  = current(array_keys($areaArr));
      $areaLast   = end(array_keys($areaArr));
      //$brFirst  = current(array_keys($brArr));
      $brLast   = end(array_keys($brArr));
      //$locFirst   = current(array_keys($locArr));
      $locLast  = end(array_keys($locArr));
      
      /*foreach ($areaArr as $key => $area) {
        $aLine = '|';
        if ($areaLast === $key) {
          $aLine = NULL;
        }
            
        $option .= '<option value="'. $area['brcode'] .'">|--- '. $area['areaname'] .'</option>';
        
        foreach ($brArr as $key => $br) {
          
          if ($area['regioncode'] === $br['regioncode']) {
            $bLine = '|';
            if ($brLast === $key) {
              $bLine = NULL;
            }
            
            $option .= '<option value="'. $br['brcode'] .'">'. $aLine .'&nbsp;&nbsp;&nbsp;&nbsp;'. $bLine .'----- '. $br['brname'] .'</option>';
            
            foreach ($locArr as $key => $loc) {
              
              if ($br['brcode'] === $loc['brcode']) {
                $lLine = '|';
                if ($locLast === $key) {
                  $lLine = NULL;
                }
              
                $option .= '<option value="'. $loc['brcode'] .'">'. $aLine .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. $lLine .'-----'. $loc['location'] .'</option>';
              }
            }
          }
        }
      }*/
      
      foreach ($areaArr as $key => $area) {           
        $option .= '<option value="'. $area['brcode'] .'" loccode="0">&nbsp;&nbsp;&#9679; '. $area['areaname'] .'</option>';
        
        foreach ($brArr as $key => $br) {
          
          if ($area['regioncode'] === $br['regioncode']) {
            
            $option .= '<option value="'. $br['brcode'] .'" loccode="0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9679; '. $br['brname'] .'</option>';
            
            foreach ($locArr as $key => $loc) {
              
              if ($br['brcode'] === $loc['brcode']) {             
                $option .= '<option value="'. $loc['brcode'] .'" loccode="'. $loc['loccode'] .'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9679; '. $loc['location'] .'</option>';
              }
            }
          }
        }
      }
      
      $data['branches'] = '<td width="230"><label for="location">Location:</label>
        <select name="location" id="location" style="width:160px">
          <option value="0" loccode="0">All</option>
          '. $option .'
        </select></td>';
    } else {
      $branchCode = $core->getBranchCode();
      $locCode = 0;
      $data['branches'] = NULL;
    }
    
    $result = $pos->getPOSList($branchCode, 0, '-1');
      
    if ($result->num_rows() === 0) {
      $this->load->helper('url');
      redirect('welcome');
      exit();
    }
    
    $row = $result->row_array();  
      
    $xml->setXML($row['xml']);
            
    $data['infoCode']     = $row['termcode'];
    $data['infoLuno']     = $row['luno'];
    $data['infoID']       = $row['termid'];
    $data['infoStats']    = ($row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown');
    $data['infoDesc']     = $row['description'];
    $data['infoLocation'] = ucwords(strtolower($row['location']));
    $data['infoArea']     = $core->getAreaName();
    $data['infoBranch']   = $core->getBranchName();

    $instid = $xml->getValue('INSTID') ? $xml->getValue('INSTID') : NULL;
    $outlet = $xml->getValue('OUTLET') ? $xml->getValue('OUTLET') : NULL;

    //Partner Institution
    IF ($instid != NULL) {
      if ($this->core->hasPOSCashOut()) {
        $inst = $this->pos_model->getInstitutionInfo($instid);
        $data['instname'] = isset($inst['instname']) ? $inst['instname'] : 'N/A';                
      } else  {
        $data['instname'] = 'N/A';
      }
    } else {
      $data['instname'] = 'N/A';
    }
    //end

    //Outlet
    IF ($outlet != NULL) {
      if ($this->core->hasPOSCashOut()) {
        $outl = $this->pos_model->getOutletInfo($outlet);
        $data['outletname'] = isset($outl['outletname']) ? $outl['outletname'] : 'N/A';                
      } else  {
        $data['outletname'] = 'N/A';
      }
    } else {
      $data['outletname'] = 'N/A';
    }
    //end
    
    //ATM list 
    $data['posList'] = $core->showPOSList($result);
    
    //cache POS status list
    if (!$statusList = $cache->get($this->core->getSessionID() . 'posStatusList')) {
      $result->free_result();
      $result->next_result();

      $statusList = $pos->getPOSStatus()->result_array();
      $cache->save($this->core->getSessionID() . 'posStatusList', $statusList, CACHE_TTL);
    }
    
    $data['statusList'] = NULL;
      
    foreach ($statusList as $row)
    {
      $val  = $row['codeseqno'];
      $desc = $row['codevalue'];
      $data['statusList'] .= '<option value="'. $val .'">'. $desc .'</option>\n';
    }

    $this->load->view('monitoring/pos', $data);
  }
  
  function status()
  {
    $this->load->model('coresys/pos_model');
    
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
    
    $result  = $this->pos_model->getPOSList($branchCode, $locCode, $status);  
    $posList = $core->showPOSList($result);
    
    echo json_encode(array(
      'success' => ($posList === NULL ? FALSE : TRUE),
      'pos'     => $core->compressOutput($posList)
    ));
  }
}
/* End of file pos.php */
/* Location: ./application/contollers/monitoring/pos.php */