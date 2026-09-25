<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TMNew extends CI_Controller {
	
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
		$this->load->model('coreapp/user_model');
		$this->load->library('coreconverters');
		
		$menu = $this->core->getNavMenu();
		
		$grpseqno = $this->core->getUserGroup();
		
		$data['menuList'] = '<ul id="navMenu">';
		foreach ($menu as $pos => $sub) {
			
			$data['menuList'] .= '<li><input type="checkbox" id="'. $pos .'" name="allows[]" value="'. $pos .'" class="category" checked/><span class="category">'. $sub['name'] .'</span><ul>';
			
			foreach ($sub['subMenu'] as $posx => $submenu) {	
				if ( in_array($posx, array(PDFGEN_NO)) ) {
					continue;
				}
				$data['menuList'] .= '<li><input type="checkbox" id="'. $posx .'" name="allows[]" value="'. $posx .'" class="'. $pos .'sub" checked/><span>'. $submenu['name'] .'</span></li>';
			}
			
			$data['menuList'] .= '</ul></li>';
		}
		$data['menuList'] .= '</ul>';
		
		//user groups
		
		$result = $this->user_model->getWebUserGroupList($grpseqno);
		$userGroup = $result->result_array();
		
		$data['userGroup'] = NULL;
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row)
			{				
				$data['userGroup'] .= '<option value="'. $row['grpseqno'] .'">'. $row['description'] .'</option>';
			}
		} else {
			$data['userGroup'] = '<option value="">No user Groups Defined</option>';
		}
		//end
		
		$data['title'] = 'New User Template';
		$data['waitMsg'] = 'Creating User Template...';
		$data['tmseqno'] = NULL;
		$data['controlsAttr'] = NULL;
		$data['visibility'] = NULL;
		$data['templateName'] = NULL;
		$data['formAction'] = 'security/tmnew/submit';
		
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
			
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('security/templatex', $data);
	}
	
	function submit()
	{
		//sleep(4);
		$this->load->model('coreapp/user_model');
		$this->load->library('coreconverters');
		
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
		
		$result = $this->user_model->insertUserTemplate($grpseqno, $desc, $hex, $userAudit, '', $workstation, $sessionID);
		
		$row = $result->row_array();
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'New user template created successfully';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message/*,
			'bin' => $bin,
			'hex' => $hex,
			'max' => $max,
			'allows' => $allows*/
			/*'$grpseqno' => $grpseqno,
			'$desc' => $desc,
			'$hex' => $hex,
			'$userAudit' => $userAudit,
			'$workstation' => $workstation,
			'$sessionID' => $sessionID*/
		));
	}
}