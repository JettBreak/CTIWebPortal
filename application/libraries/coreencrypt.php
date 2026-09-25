<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CoreEncrypt extends CI_Controller {
	
	function encrypt($str)
	{
		return sha1('coreware:'. $str);
	}
}