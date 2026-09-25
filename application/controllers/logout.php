<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logout extends CI_Controller {
  
  function index()
  {
    $this->load->model('coreapp/user_model');
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    
    $this->load->library('session');
    $this->load->library('core');
    $this->load->helper('file');
    
    $cache   = $this->cache;
    $user  = $this->user_model;
    $session = $this->session;
    $core  = $this->core;
    
    $userID  = $core->getUserID();
    $grpseqno = $core->getUserGroup();
    $userSession = $core->getSessionID();
    $workstation = $core->getWorkstation();
    $userAudit = $core->getUserID();
    $brseqno = $core->getBranchID();
    
    $user->userLogOut($userID, $grpseqno, $userSession);
    $session->sess_destroy();
        
    $this->db1 = $this->load->database(DB1, TRUE);
    $this->db1->cache_delete_all();
    $this->db2 = $this->load->database(DB2, TRUE);
    $this->db2->cache_delete_all();
    
    $cache->clean();  
    //clear site temp files
    $inst = isset($_SESSION['inst']) ? $_SESSION['inst'] : NULL;
    
    $data['folder'] = $inst['css'];
    $data['bankName'] = $inst['bankName'];
    $data['siteURL'] = $inst['siteURL'];

    $auditXML = '<field>Logout</><details>User logout: '.$userID.'</>';
    $this->user_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'USER','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
    
    //destroy session but retain inst info
    session_destroy();
    session_start();
    
    $_SESSION['inst'] = $inst;
    //
    
    delete_files(TEMP_PATH);
    $this->load->view('login', $data);
  }
}
/* End of file logout.php */
/* Location: ./application/controllers/logout.php */