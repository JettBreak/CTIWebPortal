<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Terminal extends CI_Controller {
    
    private $fileVersion = '1.10.00';

    function __construct()
    {
        parent::__construct();
        $this->load->library('core');
        $this->core->checkUserAllows(MONATM_NO);
        
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
    
    function index()
    {
    $success = TRUE;
    $message = '';
    $data = array();
    $errors = array();
    
    $view = 'monitoring/terminal';

    try {
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->load->model('coreapp/area_model');
        $this->load->model('coresys/atm_model');
        $this->load->model('coresys/pos_model');
        $this->load->library('shortxml');
        
        $cache = $this->cache;
        $atm = $this->atm_model;
        $core = $this->core;
        $xml = $this->shortxml;
        
        $result = $atm->getTerminalLocations('ATM');
        
        $locArr = $result->result_array();
        
        $result->free_result();
        $result->next_result();
        
        $brcodesWithATM = array();
        foreach ($locArr as $row) {
            $brcodesWithATM[] = "'". $row['brcode'] ."'";
        }
        
        $brcodes = implode(',', $brcodesWithATM);
        
        $result = $this->area_model->getTerminalAreas($brcodes);
        
        if ($result === FALSE) {
            echo " NO ATM TERMINAL DEFINED ";
            $this->load->helper('url');
            exit();
        }
        
        $areaArr = $result->result_array();

        $result->free_result();
        $result->next_result();
        
        //ATM info to Information Tab and Header        
        if ($core->canMon()) {
            $branchCode = 0;
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
            $brLast     = end(array_keys($brArr));
            //$locFirst     = current(array_keys($locArr));
            $locLast    = end(array_keys($locArr));
            
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
                    
                    if ($area['regioncode'] === $br['regioncode'] && in_array("'". $br['brcode'] ."'", $brcodesWithATM)) {
                        
                        $option .= '<option value="'. $br['brcode'] .'" loccode="0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9679; '. $br['brname'] .'</option>';
                        
                        foreach ($locArr as $key => $loc) {
                            
                            if ($br['brcode'] === $loc['brcode']) {                         
                                $option .= '<option value="'. $loc['brcode'] .'" loccode="'. $loc['loccode'] .'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9679; '. substr($loc['location'],0 , 25) .'</option>';
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
            $data['branches'] = NULL;
        }
        
        $result = $atm->getATMList($branchCode, 0, '-1');
        
        //if no terminals defined
        if ($result->num_rows() === 0) {
            $this->load->helper('url');
            redirect('welcome');
            exit();
        }
        
        $row    = $result->row_array();
        
        $xml->setXML($row['xml']);
        
        $luno = $row['luno'];
        $data['infoTerminalID']    = $row['termid'];
        $data['infoTerminalCode']  = $row['termcode'];
        $data['infoTerminalLuno']  = $luno;
        $data['infoTerminalStats'] = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';
        $data['infoTerminalDesc']  = $row['description'];
        $data['infoInstallType']   = $row['insttype'];
        $data['infoTerminalLoc']   = substr(ucwords(strtolower($row['location'])), 0 ,25 );
        //$data['infoContactNo']       = $xml->getValue('CONTACT') ? $xml->getValue('CONTACT') : 'None';
        $data['infoProgCode']      = $row['progcode'];
        $data['infoProgLang']      = $row['proglang'];
        $instid = $xml->getValue('INSTID') ? $xml->getValue('INSTID') : NULL;
        $outlet = $xml->getValue('OUTLET') ? $xml->getValue('OUTLET') : NULL;
        $data['isEMV']  = $xml->getValue('EMVENABLED') ? $xml->getValue('EMVENABLED') : NULL;

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

        $brCode = $row['brcode'];

        //ATM list 
        $data['atmList'] = $core->showATMList($result);
        //end

        $result->free_result();
        $result->next_result();

        $result = $atm->getATMLastCommandStatus($luno);
        $row    = $result->row_array();

        if ($result->num_rows() > 0) {
            $data['infoLastCommand']   = $row['description'] ? str_replace('Atm', 'ATM', ucwords(strtolower($row['description']))) : 'Unknown';
            $data['infoCommandStatus'] = $row['statdesc'];
        } else {
            $data['infoLastCommand']   = 'Unknown';
            $data['infoCommandStatus'] = 'Unknown';
        }
        
        
        //get area name
        $res = $this->area_model->getAreaByBranch(strval($brCode));
        $rw  = $res->row_array();
        
        $areaName = ucwords(strtolower($rw['areaname']));
        $data['infoAreaName'] = $areaName;//$core->getAreaName();
        
        $res->free_result();
        $res->next_result();
        //end
        
        if ($core->canMon()) {
            if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
                $this->load->model('coreapp/branch_model');
                $res = $this->branch_model->getBranchList();
            
                $branches = $res->result_array();
                
                $res->free_result();
                $res->next_result();
                $this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
            }
            
            $branchName = NULL;
            foreach ($branches as $row) {
                if ($brCode == $row['brcode']) {
                    $branchName = ucwords(strtolower($row['brname']));
                    break;
                }
            }
        } else {
            $branchName = $core->getBranchName();
        }
        
        $result->_data_seek();
        
        $data['infoBranchName']    = $branchName;
        
        
        
        $result->free_result();
        $result->next_result();
            
        //cache ATM status list
        if (!$statusList = $cache->get($this->core->getSessionID() . 'statusList')) {
            $result = $atm->getATMStatus();
            $statusList = $result->result_array();
            
            $result->free_result();
            $result->next_result();
            $cache->save($this->core->getSessionID() . 'statusList', $statusList, CACHE_TTL);
        }
        
        $data['statusList'] = NULL;
            
        foreach ($statusList as $row)
        {
            $val  = $row['codeseqno'];
            $desc = $row['codevalue'];
            $data['statusList'] .= '<option value="'. $val .'">'. $desc .'</option>\n';
        }

    } catch (Exception $e) {
        $errors[] = $e->GetMessage();
    }
    
    $this->load->model('coreapp/card_model');

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();
   
    if (count($errors) > 0) {
       $msgtype = 43;
       $errordetails = '- '.implode(', ', $errors); 
    } else {
       $msgtype = 41;
       $errordetails = ''; 
    }

    $auditXML .= '<old></><new></><field>ATM Monitoring</><details>Open Module '.$errordetails.'</>';
    $this->card_model->insertAuditLogclixx($msgtype,'990317',$brseqno,0,0,'ATM','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    
    if (count($errors) > 0) {
        $data['errorMessage'] = implode(', ', $errors);
        $this->load->view('error', $data);
    } else {
        $this->load->view($view, $data);
    }
  }
  
  // --------------------------------------------------------------------

  /**
   * Get ATM List
   *
   * @access  public
   */
  function get()
  {
    $this->load->model('coresys/atm_model');
    
    $core  = $this->core;
    $input = $this->input;
            
    if ($core->canMon()) {
        $brCode = $input->get('brcode', TRUE);
        $locCode = $input->get('loccode', TRUE);
    } else {
        $brCode = $core->getBranchCode();
        $locCode = 0;
    }
    $status = $input->get('status');
    
    $result  = $this->atm_model->getATMList($brCode, $locCode, $status);    
    $atmList = $core->showATMList($result);
    
    echo json_encode(array(
        'success' => $atmList ? TRUE : FALSE,
        'atm'     => $core->compressOutput($atmList)
    ));
  }
}
/* End of file atm.php */
/* Location: ./application/contollers/monitoring/atm.php */