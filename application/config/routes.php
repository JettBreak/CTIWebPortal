<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = 'portal';
$route['404_override'] 		 = '';

// "Override" is a built-in PHP attribute name on modern PHP versions.
// Keep the legacy URL working while dispatching to the renamed controller.
$route['override'] 			= 'useroverride';
$route['override/(:any)'] 	= 'useroverride/$1';

$route['customer/search/submit']		= 'customer/search/submit';
$route['customer/search/cache']			= 'customer/search/cache';
$route['customer/search/(:any)'] 		= 'customer/search/index/$1';
$route['customer/info/(:num)'] 			= 'customer/info/index/$1';
$route['customer/edit/submit'] 			= 'customer/edit/submit';
$route['customer/edit/(:num)'] 			= 'customer/edit/index/$1';
$route['customer/photo/(:num)/(:num)']  = 'customer/photo/index/$1/$2';
$route['card/verify/submit/(:any)'] 	= 'card/verify/submit/$1';
$route['card/verify/(:any)'] 			= 'card/verify/index/$1';

$route['maintenance/atmedit/submit'] 	= 'maintenance/atmedit/submit';
$route['maintenance/atmedit/(:num)'] 	= 'maintenance/atmedit/index/$1';
$route['maintenance/atmdup/submit'] 	= 'maintenance/atmdup/submit';
$route['maintenance/atmdup/(:num)'] 	= 'maintenance/atmdup/index/$1';

$route['maintenance/posedit/submit'] 	= 'maintenance/posedit/submit';
$route['maintenance/posedit/(:any)'] 	= 'maintenance/posedit/index/$1';
$route['maintenance/posdup/submit'] 	= 'maintenance/posdup/submit';
$route['maintenance/posdup/(:any)'] 	= 'maintenance/posdup/index/$1';

$route['maintenance/issuelist/(:num)'] 	= 'maintenance/issuelist/index/$1';
$route['maintenance/issuelognew/(:num)'] 	= 'maintenance/issuelognew/index/$1';
$route['maintenance/issuelogedit/(:num)'] 	= 'maintenance/issuelogedit/index/$1';

//$route['security/usernew/(:num)']		= 'security/usernew/index/$1';
//$route['security/usernew']				= 'security/users';
$route['security/templates/preview/(:num)']	= 'security/templates/preview/$1';

$route['maintenance/servicechargenew/(:any)']	= 'maintenance/servicechargenew/index/$1';
$route['maintenance/servicechargenew/submit'] 	= 'maintenance/servicechargenew/submit';

$route['coreware'] = 'portal/index/coreware';
$route['tysb'] = 'portal/index/tysb';
$route['koop'] = 'portal/index/koop';
$route['pbcom'] = 'portal/index/pbcom';
$route['ebank'] = 'portal/index/ebank';
$route['skyy'] = 'portal/index/skyy';
$route['qcrb'] = 'portal/index/qcrb';
$route['postal'] = 'portal/index/postal';
$route['ahb'] = 'portal/index/ahb';
$route['bm'] = 'portal/index/bm';
$route['isla'] = 'portal/index/isla';
$route['nwtf'] = 'portal/index/nwtf';
$route['ucpb'] = 'portal/index/ucpb';
$route['ucpbsavings'] = 'portal/index/ucpbsavings';
$route['dipolog'] = 'portal/index/dipolog';
$route['planbank'] = 'portal/index/planbank';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
