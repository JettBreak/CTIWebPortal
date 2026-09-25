<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Progress2 extends CI_Controller {
	
	function index() {
		$this->load->view('progress2');
	}
	
	function screenstatus()
	{
		// pad to force the browser to starting parsing/executing
		echo str_pad('<html><body>', 4096);
		
		for ($i = 0; $i <= 100; $i ++) {
			echo str_pad('<script type="text/javascript">parent.updateStatus('. $i .');</script>'."\n", 1024);
			flush();
			usleep(25000);
		}
		
		
		
		
		/*while(1) {
			//status string
			$status = 1;
	 
			// how many have been processed
			$c = 1;
	 
			// how many results
			$rc = 1;
	 
			// total
			$t = 1;
	 
			echo str_pad('<script type="text/javascript">parent.updateStatus(1);</script>'."\n", 1024);
			flush();
	 
			if(($status === false) || ($status === 'canceled') || ($status === 'complete')) {
				break;
			}
	 
			usleep(25000);
		}*/
 
		echo '</body></html>';
	}
}