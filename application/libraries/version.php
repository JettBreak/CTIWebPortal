<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Version Controller Class
 *
 * @author		system
 * @lastedit	system 2
 * @lastupdate	July 12, 2013
 */
class Version
{
	private $files;
	private $error;

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		$this->files = array(
			array(
				'filename' => 'monitoring/terminal.php',
				'version' => '1.10.00'
			),
			//atm tabs
			array(
				'filename' => 'atmtabs/info.php',
				'version' => '1.10.00'
			),
			//card enrollment
			array(
				'filename' => 'card/enrollment.php',
				'version' => '1.10.00'
			),
			//models
			array(
				'filename' => 'coresys/atm_model.php',
				'version' => '1.10.00'
			),
			//reports
			array(
				'filename' => 'reports/barts.php',
				'version' => '1.10.00'
			),
			//reports
			array(
				'filename' => 'coresys/reports_model.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'card/accountadd.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'accounts/newentry.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'accounts/accountrequest.php',
				'version' => '1.10.00'
			),
			//account
			array(
				'filename' => 'accounts/accountget.php',
				'version' => '1.10.00'
			),
			//account
			array(
				'filename' => 'accounts/accountinfo.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'accounts/changestatus.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'accounts/accountresultlist.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'card/issuance.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'customer/requestCustomer.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'customer/search2.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'customer/editapproval.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'customer/verification.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'card/batchupload.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'accounts/batchupload.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'card/cardissuanceapproval.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'customer/batchupload.php',
				'version' => '1.10.00'
			),
			//card
			array(
				'filename' => 'card/verify.php',
				'version' => '1.10.00'
			),
			//instapay
			array(
				'filename' => 'instapay/request.php',
				'version' => '1.10.00'
			),
			//instapay
			array(
				'filename' => 'instapay/verify.php',
				'version' => '1.10.00'
			),
			//instapay
			array(
				'filename' => 'instapay/changeuserpwd.php',
				'version' => '1.10.00'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Validator
	 *
	 */
	function validate($fileName, $version)
	{
		$success = FALSE;
		foreach ($this->files as $file) {
			if ($fileName === $file['filename'] && $version === $file['version']) {
				$success = TRUE;
				break;
			}
		}

		return $success;
	}
}