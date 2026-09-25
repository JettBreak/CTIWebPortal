<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(USERENTRY_NO);
    
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
    $this->load->model('coreapp/user_model');
    $this->load->library('shortxml');
    $this->cache->delete($this->core->getSessionID() .'userInfo');
    
    $xml = $this->shortxml;
    
    if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
      $this->load->model('coreapp/branch_model');
      $result = $this->branch_model->getBranchList();
    
      $branches = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      $this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
    }
    
    $data['branches'] = NULL;
    foreach ($branches as $row) {
      $data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
    }
    
    $result = $this->user_model->getWebUserGroupList();
    
    $data['usergroups'] = NULL;
    $details = array();
    if ($result->num_rows() > 0) {
      foreach ($result->result_array() as $row) {
        $xml->setXML($row['xml1']);
        
        //$attr = substr($xml->getValue('SECUOPTION'), 1, 1) === '1' ? ' xuser' : NULL;
        
        $data['usergroups'] .= '<option value="'. $row['grpseqno'] .'">'. $row['description'] .'</option>';   
      }
    }
    
    $result->free_result();
    $result->next_result();
    
    $userGroup = $this->core->getUserGroup();
    
    $data['initgrpseqno'] = 0;
    //end
    
    //set initbrseqno and ui toolbar
    
    if ($this->core->canUser()) {
      $initbrseqno = 0;
      $data['showToolbarFilters'] = TRUE;
    } else {
      $initbrseqno = $this->core->getBranchID();
      $data['showToolbarFilters'] = FALSE;
    }
    
    $data['initbrseqno'] = $initbrseqno;
    //end

    $this->load->model('coreapp/card_model');

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>User List</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);


    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('security/users', $data);
  } 

  //cache user info
  function cache()
  {
    $_SESSION['user'] = $_POST;
    
    echo json_encode(array(
      'success' => TRUE
    ));
  }
  
  function getData()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    
    $grpseqno = $this->input->post('grpseqno', TRUE);
    
    if ($this->input->post('cache', TRUE) === '0') {
      $this->cache->delete($this->core->getSessionID() .'users'.$grpseqno);
    }
    
    if (!$details = $this->cache->get($this->core->getSessionID() . 'users'.$grpseqno)) {
      $this->load->model('coreapp/user_model');
      $this->load->library('shortxml');
      
      $core = $this->core;
      $xml = $this->shortxml;
      
      if ($core->canUser()) {
        $brseqno = $this->input->post('brseqno', TRUE);
      } else {
        $brseqno = $core->getBranchID();
      }
      
      $result = $this->user_model->getWebUserList($grpseqno, $brseqno);
      
      $details = array();
      if ($result->num_rows() > 0) {
        foreach ($result->result_array() as $row) {
          $xml->setXML($row['xml1'] . $row['grpxml']);
          
          $userID = $row['userid'];
          
          if ($userID !== $core->getUserID()) {
          
            $userName = $row['username'];
            $branch = $row['brname'];
            $grpdesc = $row['grpdesc'];
            $status = $row['statdesc'];
            $dtLastAccess = $row['dtlastlogin'] ? $core->formatDate('m/d/Y h:i:s A', $row['dtlastlogin']) : 'Never';
            $dtLastPwdChg = $row['dtlastpwdchg'] ? $core->formatDate('m/d/Y h:i:s A', $row['dtlastpwdchg']) : 'Never';
            $dtCreated = $xml->getValue('DATECREATED');
            $dtCreated = $core->formatDate('m/d/Y h:i:s A', $dtCreated);
            $grpseqno = $row['grpseqno'];
            $emailAddr = $xml->getValue('EMAIL');
            $position = $xml->getValue('POSITION');
            //$xml1 = $row['xml1'];
            $department = $xml->getValue('DEPARTMENT');
            $tmseqno = $row['tmseqno'];
            $allows = $row['allows'];
            $brseqno = $row['brseqno'];
            $enableDisable = substr($xml->getValue('SECUOPTION'), 1, 1) === '1' ? 1 : 0;
            
            $details[] = array(
              $userID,
              $userName,
              $branch,
              $grpdesc,
              $status,
              $dtLastAccess,
              $dtLastPwdChg,
              $dtCreated,
              $grpseqno,
              $position,
              '',//$xml1, //reserved for debugging
              $department,
              $tmseqno,
              $allows,
              $brseqno,
              $emailAddr,
              $enableDisable
            );
          }
        }
      }
      
      $this->cache->save($this->core->getSessionID() .'users'.$grpseqno, $details, CACHE_TTL);
    }
    
    echo json_encode(array(
      'success' => TRUE,
      'details' => $details
    ));
  }
  
  function submit()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));    
    $this->load->model('coreapp/user_model');      
    $this->load->model('coreapp/branch_model');  
    $this->load->model('coreapp/dept_model');  
    $this->load->library('coreconverters');
    
    $core = $this->core;
    $input = $this->input;
    
    $tmseqno = $input->post('tmseqno', TRUE);
    $grpseqno = $input->post('grpseqno', TRUE);
    $userID = $input->post('userID', TRUE);
    $brseqno = $input->post('branch', TRUE);
    $userName = ucwords(strtolower($input->post('userName', TRUE)));
    
    $allows = $this->input->post('allows');
    
    if ($allows) {
      $max = max($allows);
      $bin = '';
      for ($i = 1; $i <= $max; $i++) {  
        if (in_array($i, $allows)) {
          $bin .= '1';
        } else {
          $bin .= '0';
        }
      }
    } else {
      $bin = '0';
    }
    
    $hex = $this->coreconverters->asciiBinToHex($bin);
    
    $position = $input->post('position', TRUE);
    $department = $input->post('department', TRUE);
    $email = $input->post('emailAddr', TRUE);
    $workstation = $core->getWorkstation();
    $userAudit = $core->getUserID();
    $sessionID = $core->getSessionID();

    $menu = $this->core->getNavMenu();
    $newallows = $bin;
    
    $menuList = '';
    
    if ($input->post('update', TRUE)) {
      if ($info = $_SESSION['user']) {
        $dtCreated = $info['dtCreated'];
      } else {
        echo json_encode(array(
          'success' => FALSE,
          'message' => 'Cache has expired please try again'
        ));
        exit();
      }

      $allows    = $this->coreconverters->asciiHexToBin($info['allows']);
      
      $xml1 = '<DATECREATED>'. $dtCreated .'</>'.
        '<POSITION>'. $position .'</>'.
        '<DEPARTMENT>'. $department .'</>'.
        '<EMAIL>'. $email .'</>';
      $result = $this->user_model->getcurrentuserinfo($userID);

      $info = $result->row_array();

      $result->free_result();
      $result->next_result();
      
      $result = $this->user_model->updateWebUser($tmseqno, $userID, $brseqno, $userName, $hex, $xml1, $workstation, $userAudit, $sessionID);

      $item = $info;
      $auditXML = '';
      if ($item['tmseqno'] != $tmseqno) {

        $stat = $this->user_model->getUserTemplateInfo($item['tmseqno']);
        $oldtm = $stat->row_array();

        $stat->free_result();
        $stat->next_result();

        $stat = $this->user_model->getUserTemplateInfo($tmseqno);
        $newtm = $stat->row_array();

        $stat->free_result();
        $stat->next_result();

        $auditXML .= '<old>'.$oldtm['description'].'</><new>'.$newtm['description'].'</><field>Template</><details>Update user: '.$userID.'</>';
        $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $info;
      $auditXML = '';
      if ($item['brseqno'] != $brseqno) {

        $stat = $this->branch_model->getBranchDetails($brseqno);
        $desc = $stat->row_array();

        $stat->free_result();
        $stat->next_result();

        $auditXML .= '<old>'.$item['brname'].'</><new>'.$desc['brname'].'</><field>Branch</><details>Update user: '.$userID.'</>';
        $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $info;
      $auditXML = '';
      if ($item['department'] != $department) {

        $stat = $this->dept_model->getDepartmentInfo($item['department']);
        $olddept = $stat->row_array();

        $stat->free_result();
        $stat->next_result();

        $stat = $this->dept_model->getDepartmentInfo($department);
        $newdept = $stat->row_array();

        $stat->free_result();
        $stat->next_result();

        $auditXML .= '<old>'.$olddept['codevalue'].'</><new>'.$newdept['codevalue'].'</><field>Department</><details>Update user: '.$userID.'</>';
        $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $info;
      $auditXML = '';
      if ($item['position'] != $position) {
        $auditXML .= '<old>'.$item['position'].'</><new>'.$position.'</><field>Position</><details>Update user: '.$userID.'</>';
        $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $info;
      $auditXML = '';
      if ($item['username'] != $userName) {
        $auditXML .= '<old>'.$item['username'].'</><new>'.$userName.'</><field>Username</><details>Update user: '.$userID.'</>';
        $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $message = 'User entry updated successfully';

      foreach ($menu as $pos => $sub) {
        $checkedold = substr($allows, $pos - 1, 1) === '1' ? 'Checked' : 'Unchecked';
        $checkednew = substr($newallows, $pos - 1, 1) === '1' ? 'Checked' : 'Unchecked';

        if ($checkedold != $checkednew) {
          $auditXML = '<old>'.$checkedold.'</><new>'.$checkednew.'</><field>Access Template</><details>Update '.$userID. ' : '.$sub['name'].'</>';
          $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        }
        //$menuList = $sub['name'];
        
        foreach ($sub['subMenu'] as $posx => $submenu) {
          if ( in_array($posx, array(PDFGEN_NO)) ) {
            continue;
          }

          $checkedoldx = substr($allows, $posx - 1, 1) === '1' ? 'Checked' : 'Unchecked';     
          $checkednewx = substr($newallows, $posx - 1, 1) === '1' ? 'Checked' : 'Unchecked';  
          
          if ($checkedoldx != $checkednewx) {
            $auditXML = '<old>'.$checkedoldx.'</><new>'.$checkednewx.'</><field>Access Template</><details>Update '.$userID. ' : '.$submenu['name'].'</>';
            $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
          }

          //$data['menuList'] .= '<li><input type="checkbox" id="'.$posx.'" name="allows[]" value="'.$posx.'" class="'.$pos.'sub"'.$checked.'/>'. $submenu['name'] .'</li>';
        }
      }

    } else {

      $groupname = $input->post('groupname', TRUE);

      date_default_timezone_set('Etc/UTC');

      $auditXML = '';

      $dtCreated = date('m/d/Y h:i:s A');
      $userPW = $core->encrypt($userID, sha1($userID)); //$randpass
      
      $xml1 = '<DATECREATED>'. $dtCreated .'</>'.
        '<POSITION>'. $position .'</>'.
        '<DEPARTMENT>'. $department .'</>'.
      '<EMAIL>'. $email .'</>';
        
      $xml2 = '<ISWEB>Y</>'.
        '<WEBPWD>'. $userPW .'</>'.
        '<WEBSTAT>1</>';
    
      $result = $this->user_model->insertWebUser($tmseqno, $grpseqno, $userID, $brseqno, $userName, $hex, $xml1, $xml2, $workstation, $userAudit, $sessionID);
      $message = "New user successfully created.<br> System generated password has been emailed to the user.";

      foreach ($menu as $pos => $sub) {
        $checkednew = substr($newallows, $pos - 1, 1) === '1' ? 'Checked' : 'Unchecked';

        if ($checkednew == 'Checked') {
          $auditXML = '<old></><new></><field>Access Template</><details>User: '.$userID. ' - Set template  : '.$sub['name'].'</>';
          $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        }
        //$menuList = $sub['name'];
        
        foreach ($sub['subMenu'] as $posx => $submenu) {
          if ( in_array($posx, array(PDFGEN_NO)) ) {
            continue;
          }

          $checkednewx = substr($newallows, $posx - 1, 1) === '1' ? 'Checked' : 'Unchecked';  
          
          if ($checkednewx == 'Checked') {
            $auditXML = '<old></><new></><field>Access Template</><details>User: '.$userID. ' - Set template  : '.$submenu['name'].'</>';
            $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
          }
        }
      }
    }
    
    if ($result->num_rows() > 0) {
      $row = $result->row_array();
      if ($row['errno'] > 0) {
        $success = FALSE;
        $message = $row['errmsg'];
      } else {
        unset($_SESSION['user']);
        $success = TRUE;
      }
    }
    
    $result->free_result();
    $result->next_result();
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'hex' => $hex,
      'allows' => $bin,
      'sessionID' => $sessionID
    ));
  }
  
  function delete()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/user_model');
    unset($_SESSION['user']);
    
    $userID = $this->input->post('userID', TRUE);
    $brseqno = $this->input->post('brseqno', TRUE);
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
    $workstation = $this->core->getWorkstation();
    
    $this->user_model->deleteWebUser($userID, $userAudit, $sessionID);

    $auditXML = '';

    $auditXML .= '<old></><new></><field>Delete Web User</><details>Delete user: '.$userID.'</>';
    $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
    
    echo json_encode(array(
      'removed' => TRUE,
      'userID' => $userID
    ));
  }
  
  function enable()
  {
    $this->load->model('coreapp/user_model');
    
    $userID = $this->input->post('userID', TRUE);
    $brseqno = $this->input->post('brseqno', TRUE);
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
    $workstation = $this->core->getWorkstation();
    
    $this->user_model->enableWebUser($userID, $userAudit, $sessionID);
    
    $auditXML = '';

    $auditXML .= '<old></><new></><field>Enable Web User</><details>Enable user: '.$userID.'</>';
    $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
    /*
    $to = "christian.sevilleno@yahoo.com";
    $subject = "My subject";
    $txt = "Hello world!";
    $headers = "From: christian@corewaretech.com" . "\r\n" .
    "CC: ithcode_cs@yahoo.com";

    mail($to,$subject,$txt,$headers);*/
    
    echo json_encode(array(
      'enabled' => TRUE,
      'userID' => $userID,
      'newStat' => 'Active'
    ));
  }
  
  function disable()
  {
    $this->load->model('coreapp/user_model');
    
    $userID = $this->input->post('userID', TRUE);
    $brseqno = $this->input->post('brseqno', TRUE);
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
    $workstation = $this->core->getWorkstation();
    
    $this->user_model->disableWebUser($userID, $userAudit, $sessionID);
    
    $auditXML = '';

    $auditXML .= '<old></><new></><field>Disable Web User</><details>Disable user: '.$userID.'</>';
    $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
    
    echo json_encode(array(
      'disabled' => TRUE,
      'userID' => $userID,
      'newStat' => 'Disabled'
    ));
  }
  
  function resetSettings()
  {
    $this->load->model('coreapp/user_model');
    
    $grpseqno = $this->input->post('grpseqno', TRUE);
    $userID = $this->input->post('userID', TRUE);
    $brseqno = $this->input->post('brseqno', TRUE);
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
    $workstation = $this->core->getWorkstation();
    
    $this->user_model->resetWebUserSettings($grpseqno, $userID, $userAudit, $sessionID);
    
    $auditXML = '';

    $auditXML .= '<old></><new></><field>Reset Web User</><details>Reset user settings: '.$userID.'</>';
    $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
    
    
    echo json_encode(array(
      'sessionid' => $sessionID,
      'grpseqno' => $grpseqno,
      'userAudit' => $userAudit,
      'resetSettings' => TRUE,
      'userID' => $userID,
      'newStat' => 'User Reset'
    ));
  }
  
  function resetPwd()
  {
    $this->load->model('coreapp/user_model');
    $this->load->library('shortxml');
    
    $xml = $this->shortxml;
    $userID = $this->input->post('userID', TRUE);
    $grpseqno = $this->input->post('grpseqno', TRUE);
    $username = $this->input->post('name', TRUE);
    $email = $this->input->post('emailAddr', TRUE);
    $stats = $this->input->post('stats', TRUE);
    $brseqno = $this->core->getBranchID();
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $this->core->getUserID();
    $sessionID = $this->core->getSessionID();
    $userPW = $this->core->encrypt($userID, sha1($userID));

    $result = $this->user_model->getUserPassCycleCount($userID, $grpseqno);

    if ($result) {
        $row = $result->row_array();
        $result->free_result();
        $result->next_result();
    } else {
        $row = null;
    }

    $userPW = $this->core->encrypt($userID, sha1($userID)); 

    $userXML = '';
    $passCycle = 0;

    if (!empty($row)) {
        $xml->setXML($row['grpXML']);
        $passCycle = (int)$xml->getValue('PWCYCLE'); 
        
        $xml->setXML($row['userXML']);
    }

    $success = true;
    $pwArr = [];

    for ($i = 1; $i < $passCycle; $i++) {
        $pwSet = 'PWSET' . $i;
        $pwArr[] = $xml->getValue($pwSet);
    }

    if ($stats != 7) {
        if (count($pwArr) === $passCycle) { 
            array_pop($pwArr);
        }
        
        array_unshift($pwArr, $xml->getValue('WEBPWD'));
    }

    $pwXML = '';
    for ($i = 1; $i < $passCycle; $i++) {
        $tagName = 'PWSET' . $i;
        
        if ($i > count($pwArr)) {
            $pwXML .= '<' . $tagName . '></' . $tagName . '>';
        } else {
            $idx = $i - 1;
            $pwXML .= '<' . $tagName . '>' . $pwArr[$idx] . '</' . $tagName . '>';
        }
    }
    
    $result = $this->user_model->setUserPass($userID, $userPW, NULL, TRUE, '', '', 0, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID, $grpseqno, $pwXML);

    $auditXML = "";
    $auditXML .= '<old></><new></><field>Reset Web User</><details>Reset user password : '.$userID.'</>';
    $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
    $success = TRUE;
    $message = 'Password Reset';
    
    echo json_encode(array(
      'resetPwd' => $success,
      'userID' => $userID,
      'newStat' => $message
    ));
  }
}
