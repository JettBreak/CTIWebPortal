<?php

/*
 * Core FS Fusion PORTAL
 *
 * Coreware Technologies, Inc.
 * http://www.corewaretech.com
 * 
 * Copyright (c) 2011 CorewareTech
 * 
 * CHANGE LOG
 * 
 * DATE				VER   			REMARKS
 * 03/10/2011		1.00.00			initial
 * 11/21/2011		1.00.01			
 */
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $pageTitle; ?></title>
<link rel="shortcut icon" href="images/core.ico">
<style>
@import "css/<?php echo $folder; ?>style.css";
@import "css/<?php echo $folder; ?>form.css";
@import "css/ui-lightness/jquery-ui.custom.css";
@import "css/config.css";
@import "css/datatable.css";
@import "css/validationEngine.jquery.css";
@import "css/jquery.contextMenu.css";
<?php echo $menucss; ?>
input[type="checkbox"], input[type="radio"] {
	position: relative;
	top: 2px;
}
#tellerMenu {
	position: absolute;
	top: 100px;
	z-index: 10000;
}
#tellerMenu .subMenu {
	border: 1px #111 solid;
	background: #333;
	font-size: 20px;
	padding: 10px;
}
</style>
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="js/jquery-1.7.1.min.js"></script>
<script src="js/jquery-ui.custom.min.js"></script>
</head>
<body>
<?php echo html_entity_decode($navMenu); ?>
<header>
	<div class="wrapper">
    	<table width="100%" id="xInfo">
    		<?php echo html_entity_decode($header); ?>
        </table>
    </div>
</header>
<section>
    <div id="wrapper">
        <div id="form">
        	<h1>LOADING</h1>
            <div id="content">
            	<noscript>Please enable javascript</noscript>
            </div>
        </div>
    </div>
    <div id="instLogo">
    	<img src="images/<?php echo $folder; ?>logox.png" />
    </div>
</section>
<footer>
    <div class="wrapper"><?php echo html_entity_decode($pageFooter); ?></div>
</footer>
</body>
</html>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/jquery.dataTables.plugins.js"></script>
<script src="js/jquery.validationEngine.js"></script>
<script src="js/jquery.validationEngine-en.js"></script>
<script src="js/constants.js"></script>
<script src="js/core.js"></script>
<script src="js/modal.js"></script>
<script src="js/jquery-dynamic_page.js"></script>
<script src="js/tree.js"></script>
<script src="js/jquery.contextMenu.js"></script>
<script src="js/jquery.treeview.js"></script>
<script src="js/coreencrypt.js"></script>
<script src="js/coreencrypt.js"></script>
<script src="js/jquery.maskedinput-1.3.min.js"></script>
<script>
$(document).ready(function () {
	autoAdjustSection();
	
    var session = '<?php echo $sessionID; ?>';
	//var isTeller = '<?php //echo $isTeller; ?>';
	
    if (session) {
        if (window.location.hash == '') {
            window.location.hash = 'welcome';
        }
		
		<?php echo $js; ?>
		
		$.getScript('js/menu.js');
		
		/*if (isTeller) {
			$('#tellerMenu').fadeIn();
		}*/
		
		initSession('<?php echo $sessionExp; ?>');
    } else {
        if ((window.location.hash !== '#forgotpw') && (window.location.hash !== '#setpw')) {
            window.location.hash = 'login'
        }
    }
	
	//auto refresh
	//setTimeout('window.location.reload();', 16 * 60 * 1000);
});
</script>
