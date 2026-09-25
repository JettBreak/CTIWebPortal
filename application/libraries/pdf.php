<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class pdf
{
	public function __construct()
	{
		$message = 'In-application PDF generation has been decommissioned. This report is no longer available in PDF format.';

		if (function_exists('show_error'))
		{
			show_error($message, 410, 'PDF generation retired');
		}

		throw new RuntimeException($message, 410);
	}
}
