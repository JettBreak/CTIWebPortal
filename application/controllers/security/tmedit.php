<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TMEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(USERTEMP_NO);
		
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
		
		if (!$template = $this->cache->get($this->core->getSessionID() . 'template')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$this->load->model('coreapp/user_model');
		$this->load->library('coreconverters');
		
		$menu = $this->core->getNavMenu();
		
		$data['menuList'] = '<ul id="navMenu">';
		
		foreach ($menu as $pos => $sub) {
			$checked = substr($template['allows'], $pos - 1, 1) === '1' ? ' checked' : NULL;
			
			$data['menuList'] .= '<li><input type="checkbox" id="'.$pos.'" name="allows[]" value="'.$pos.'" class="category" '. $checked .'/><span class="category">'. $sub['name'] .'</span><ul>';
			foreach ($sub['subMenu'] as $posx => $submenu) {
				if ( in_array($posx, array(PDFGEN_NO)) ) {
					continue;
				}
				$checked = substr($template['allows'], $posx - 1, 1) === '1' ? ' checked' : NULL;
				
				
				$data['menuList'] .= '<li><input type="checkbox" id="'.$posx.'" name="allows[]" value="'.$posx.'" class="'.$pos.'sub" '. $checked .'/>'. $submenu['name'] .'</li>';
			}
			$data['menuList'] .= '</ul></li>';
		}
		$data['menuList'] .= '</ul>';
		
		//user groups		
		$grpseqno = $this->core->getUserGroup();
		
		$result = $this->user_model->getWebUserGroupList($grpseqno);
		$userGroup = $result->result_array();
		
		$data['userGroup'] = NULL;
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row)
			{
				$grpseqno = $row['grpseqno'];
				$desc = $row['description'];
				$selected = $grpseqno === $template['grpseqno'] ? ' selected' : NULL;
				
				$data['userGroup'] .= '<option value="'.$grpseqno.'"'.$selected.'>'.$desc.'</option>';
			}
		} else {
			$data['userGroup'] = '<option value="">No user Groups Defined</option>';
		}
		//end
		
		//set forbidden modules for branch user
		/*$forbidden = $this->core->getForbiddenModules();
		$lastItem = end($forbidden);
		
		$data['forbidden'] = NULL;
		foreach ($forbidden as $module) {
			$data['forbidden'] .= '#'. $module;
			if ($lastItem !== $module) {
				$data['forbidden'] .= ',';
			}
		}*/
		//end
		
		$data['title'] = 'Edit User Template';
		$data['waitMsg'] = 'Updating User Template...';
		$data['tmseqno'] = $template['ID'];
		
		if ($template['predefined'] === 'Y') {
			$data['controlsAttr'] = ' disabled';
			$data['visibility'] = ' hidden';
		} else {
			$data['controlsAttr'] = NULL;
			$data['visibility'] = NULL;
		}
		
		$data['templateName'] = $template['description'];
		$data['formAction'] = 'security/tmedit/submit';
		
		if ($template['predefined'] === 'Y') {
			$data['script'] = "$('input').click(function () {
				return false;
			});";
		} else {
			$data['script'] = "//check subcategory
			$(treeView + ' input:checkbox.category').change(function() {
				var chk = '.'+this.id+'sub:not(:disabled)';
				
				if ($(this).is(':checked') === true) {
					$(chk).attr('checked', true);
				} else {
					$(chk).removeAttr('checked');
				}
			});
			
			//check parent
			var subcat = 'input:checkbox:not(.category)';
			$(subcat).change(function() {
				
				var y = $(this).attr('class');
				var x = y.replace('sub', '');
				var z = $('#'+ x);
				z.attr('checked', true);
				
				//uncheck parent
				if ($('.'+ y +':checked').length == 0) {
					
					z.removeAttr('checked');
				}
			});";
		}
		
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('security/templatex', $data);
	}
	
	function submit()
	{
		//sleep(4);
		$this->load->model('coreapp/user_model');
		$this->load->library('coreconverters');
		
		$tmseqno = $this->input->post('tmseqno', TRUE);
		$grpseqno = $this->input->post('grpseqno', TRUE);
		$desc = $this->input->post('newTemplateName', TRUE);
		$userAudit = $this->core->getUserID();
		$change = $this->input->post('change', TRUE);
		$sessionID = $this->core->getSessionID();
		
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
		$workstation = $this->core->getWorkstation();
		
		$result = $this->user_model->updateUserTemplate($tmseqno, $grpseqno, $desc, $hex, $userAudit, '', $workstation, $change, $sessionID);	
		
		$row = $result->row_array();	
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'User template updated successfully';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message/*,
			'bin' => $bin,
			'hex' => $hex,
			'max' => $max,
			'allows' => $allows*/
		));
	}
}