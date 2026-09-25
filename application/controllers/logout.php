<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logout extends CI_Controller {
  function index()
  {
    $this->load->library('session');
    $this->load->library('core');

    $session = $this->session;
    $core = $this->core;
    $wasLoggedIn = $core->isLoggedIn();

    // Keep the portal branding available after clearing an expired session.
    $inst = (isset($_SESSION['inst']) && is_array($_SESSION['inst']))
      ? $_SESSION['inst']
      : array();
    $data = array(
      'folder' => isset($inst['css']) ? $inst['css'] : 'ucpbsavings' . DIRECTORY_SEPARATOR,
      'bankName' => isset($inst['bankName']) ? $inst['bankName'] : 'UCPB SAVINGS ',
      'siteURL' => isset($inst['siteURL']) ? $inst['siteURL'] : 'http://www.ucpb.com/'
    );

    if ($wasLoggedIn) {
      $this->load->model('coreapp/user_model');
      $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
      $this->load->helper('file');

      $userID = $core->getUserID();
      $grpseqno = $core->getUserGroup();
      $userSession = $core->getSessionID();
      $workstation = $core->getWorkstation();
      $brseqno = $core->getBranchID();

      $this->user_model->userLogOut($userID, $grpseqno, $userSession);

      $this->db1 = $this->load->database(DB1, TRUE);
      $this->db1->cache_delete_all();
      $this->db2 = $this->load->database(DB2, TRUE);
      $this->db2->cache_delete_all();
      $this->cache->clean();

      $userAudit = $userID;
      $auditXML = '<field>Logout</><details>User logout: '.$userID.'</>';
      $this->user_model->insertAuditLogclixx(
        41, '990317', $brseqno, 0, 0, 'USER', '', $userAudit, '', '', '', '', '', '', '',
        $userAudit, '', 'WEB', 'WEB', $workstation, $auditXML
      );

      // Only an authenticated logout should clear shared temporary files.
      delete_files(TEMP_PATH);
    }

    // Clear both CI's session cookie and PHP's session data before rendering.
    // No user-data getters or output occur before these header operations.
    $session->sess_destroy();
    if (session_status() === PHP_SESSION_ACTIVE) {
      $_SESSION = array();
      session_destroy();
    }
    session_start();
    $_SESSION['inst'] = $inst;

    $this->load->view('login', $data);
  }
}
/* End of file logout.php */
/* Location: ./application/controllers/logout.php */
