<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<form id="cardEnrollmentForm" method="post">
<input type="hidden" name="cifseqno" id="cifseqno" value=""/>
<div id="cardEnrollment">
	<h1>Card Enrollment</h1>
    	<div id="content">
        	<!--Form description goes here...-->
       		<span class="hint floatRight"><span class="red">*</span> - Required Fields</span></span>
            <table width="100%" style="margin-top:0px">
                <tr>
                    <td width="120"><label for="brseqno">Branch:</label></td>
                    <td colspan="3">
                    	<select name="brseqno" id="brseqno" style="width:212px">
                            <?php echo html_entity_decode($branches); ?>
                        </select>
                    </td>
                    <td rowspan="9" width="280">
                        Allowed Transactions:
                        <div style="height:126px;" class="box" id="allowedTrx">
                            <?php echo html_entity_decode($tranAllows); ?>
                        </div>
                        <br />Channel Locking:
                        <div style="height:84px;" class="box">
                            <?php echo html_entity_decode($allows); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label for="cardNo">Card Number: <span class="red">*</span></label></td>
                    <td colspan="3"><select name="cardBIN" id="cardBIN" style="width:80px">
                            <?php echo html_entity_decode($cardBIN); ?>
                        </select
                        ><input type="text" name="cardNo" id="cardNo" style="width:120px" class="validate[required,custom[onlyNumberSp]]" placeholder="<?php echo $cardNoPlaceholder; ?>"/>
                        </td>
                </tr>
                <tr>
                    <td><label for="acctType">Card Type:</label></td>
                    <td colspan="3">
                        <select name="acctType" id="acctType" style="width:212px">
                            <?php echo html_entity_decode($cardType); ?>
                        </select>
                    </td>
                </tr>
                <?php echo html_entity_decode($productCodes); ?>
                <tr>
                    <td><label for="custName">Customer Name: <span class="red">*</span></label></td>
                    <td colspan="3">
                    	<input type="text" id="custName" class="validate[required]" style="width:277px;cursor:pointer" readonly/><!--<button id="searchBtn">Search</button>-->
                    </td>
                </tr>
                <tr>
                    <td><label for="embossName">Emboss Name:</label></td>
                    <td colspan="3"><input type="text" name="embossName" id="embossName" style="width:277px" maxlength="25"/></td>
                </tr>
                <tr>
                    <td><label for="dtInitIssue">Date Initial Issue:</label></td>
                    <td><input type="text" id="dtInitIssue" style="width:140px" value="<?php echo $dtInitIssue; ?>" readonly/></td>
                    <td colspan="2">&nbsp;</td>
                    <!--<td>Issue Count:</td>
                    <td><input type="text" style="width:30px" value="<?php //echo $issueCount; ?>" readonly/></td>-->
                </tr>
                <!--<tr>
                    <td><label for="dtActivated">Date Activated:</label></td>
                    <td><input type="text" id="dtActivated" style="width: 120px" value="<?php //echo $dtActivated; ?>" readonly/></td>
                </tr>-->
                <tr>
                    <td><label for="dtExpiry">Date Expiry:</label></td>
                    <td colspan="3"><input type="text" id="dtExpiry" style="width: 140px" value="<?php echo $dtExpiry; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="defFastCash">Default Fast Cash:</label></td>
                    <td colspan="3">
                    	<input type="text" name="fCash" id="fCash" style="width:100px" class="validate[required] currencyOnly" maxlength="9" value="0.00" <?php echo $defFastCashAttr; ?>/>
                        <select name="fType" id="fType" style="width:120px" <?php echo $defFastCashAttr; ?>>
                        	<?php echo html_entity_decode($defFastCash); ?>
                        </select>
					</td>
                </tr>
                <tr>
                    <td><label for="cardStatus">Card Status:</label></td>
                    <td colspan="3"><input type="text" id="cardStatus" style="width: 200px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
            </table>
		</div>
        <!--<div id="tabAccounts" class="tab_content nopadding">
        	<table class="dataTable">
            	<thead>
                	<tr>
                    	<th>No.</th>
                        <th>Account No.</th>
                        <th>Account Type</th>
                        <th>Authorizer Mode</th>
                        <th>Primary</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div id="tabOLLimits" class="tab_content nopadding">
        	<table class="dataTable">
            	<thead>
                	<tr>
                    	<th>Transaction</th>
                        <th>Available Cycle Amt.</th>
                        <th>Maximum Cycle Amt.</th>
                        <th>Available Counter</th>
                        <th>Max Counter</th>
                        <th>Min. Transaction Amt.</th>
                        <th>Max. Transaction Amt.</th>
                        <th>Cycle Period</th>
                        <th>Time Limit</th>
                        <th>Available No Fee Ctr.</th>
                        <th>Max. No Fee Ctr.</th>
                        <th>Available No Fee Amt.</th>
                        <th>Max. No Fee Amt.</th>
                        <th>No Fee Cycle Period</th>
                    </tr>
                </thead>
            </table> 
        </div>-->
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button value="card/enrollment/submit" id="saveBtn">Save</button
        ><button id="browseBtn">Browse</button
        >
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Reset</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var resetBtn = '#resetBtn';
	var	saveBtn = '#saveBtn';
	var browseBtn = '#browseBtn';
	var branch = '#brseqno';
	var cardBIN = '#cardBIN';
	var cardNo = '#cardNo';
	
		//searchBtn = '#searchBtn',
	var	chk = $('#allowedTrx').find(':checkbox');
	var	form = $('form');
	
	initSession('<?php echo $sessionExp; ?>');
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Enrolling Card...');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				//redirect
				//window.location.hash = 'card/verification';
				
				if (data.success) {
					var	form = $('form');
					form[0].reset();
					$(cardNo).focus();
				} else {
					$(cardNo).val('').focus();
				}
			});
		},
		scroll: false
	});
	form.validationEngine('attach');
	
	//$(DATATABLE).find('tbody tr').die('dblclick');
	
	/*oTable = $(DATATABLE).dataTable({
		'bRetrieve':true,
		'bJQueryUI': true,
		'aaSorting': [], //disable initial sorting
		'sScrollY': '100%',
		'sScrollX': '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		'sPaginationType': 'full_numbers'
	});*/
	
	//default Action
	$(TABCONTENT).hide(); //hide all content
	$(TABS).first().addClass('active').show(); //activate first tab
	$(TABCONTENT).first().show(); //show first tab content
	       			
	//onClick Event
	$(TABS).click(function() {
		if (!$(this).hasClass('active')) {
			form.validationEngine('hideAll');
			$(TABS).removeClass('active'); //remove any "active" class
			$(this).addClass('active'); //add "active" class to selected tab
			$(TABCONTENT).hide(); //hide all tab content
			
			var activeTab = $(this).find('a').attr('href'); //find the rel attribute value to identify the active tab + content
			$(activeTab).show();
			
			//show / hide buttons
			/*switch (activeTab) {
				case '#tabInfo':
					$('#modifyBtn, #newBtn, #deleteBtn').hide();
					$('#saveBtn').show();
					break;
				case '#tabAccounts':
					$('#saveBtn, #modifyBtn').hide();
					$('#newBtn, #deleteBtn').show();
					break;
				case '#tabOLLimits':
					$('#saveBtn, #newBtn, #deleteBtn').hide();
					$('#modifyBtn').show();
					break; 
			}*/
			//end
			
			$(WRAPPER).height('auto');
			//$.fn.dataTableExt.iApiIndex = 0;
			oTable.fnAdjustColumnSizing();
		}
		return false;
	});
	
	$('#acctType').change(function () {
		var selected = $(this).find(':selected');
		allows = selected.attr('defaultallows');
		var format = selected.attr('format');
		popAllows(allows);
	});
	
	$(saveBtn).click(function (e) {
		if (form.validationEngine('validate') === true) {
			messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
				form.submit();
			});
		}
		e.preventDefault();
	});
	
	var productCode = '#productCode';
	$(branch + ',' + cardBIN + ',' + productCode + ',#acctType').change(function () {		
		var selected = $('#acctType').find('option:selected');
		var format = selected.attr('format');
		
		var brChar = 'B';
		var cnt = format.split(brChar).length - 1;
		var rep = brChar.repeat(cnt);
		var brCode = $(branch).find('option:selected').attr('brcode');
		var br = '';
		var mask = '';
		if (cnt > 0) {
			br = zeroPad(cnt, brCode);
		} else {
			br = zeroPad(cnt, '');
		}
		//alert('cntr:'+cnt);
		//alert('rep:'+rep);
		mask = format.replace(rep, br);

		//alert('format:'+format);
		//alert('mask:'+mask);
		
		if ($(productCode).length > 0) {
			var prcdChar = 'P';
			cnt = format.split(prcdChar).length - 1;
			rep = prcdChar.repeat(cnt);
			var pr = zeroPad(cnt, $(productCode).val());
			mask = mask.replace(rep, pr);
		} else {
			var prcdChar = 'P';
			cnt = format.split(prcdChar).length - 1;
			//alert('cnt:'+cnt);
			rep = prcdChar.repeat(cnt);
			//alert('rep:'+rep);
			var pr = '';
			if (cnt > 0) {
				pr = zeroPad(cnt, $('#acctType').val());
			} else {
				pr = zeroPad(cnt, '');
			}
			mask = mask.replace(rep, pr);
			//lert('pr:'+pr);
			//alert('rep:'+rep);
		}
		
		var placeholder = mask.replace(/N/g, '_');
		
		$(cardNo)
			.val('')
			.mask(mask)
			.attr('placeholder', placeholder);
	});
	
	$(browseBtn).click(function () {
		window.location.hash = 'card/browse';
		return false;
	});
	
	$('#fCash').focusout(function (e) {
		this.value = formatCurrency(this.value);
	}).focusin(function(e) {
		if (this.value === '0.00') {
			this.value = '';
		}
    });
	
	$('#custName').bind('click', function () {
		$(this).attr('disabled');
		$(this).validationEngine('hidePrompt');
		searchCustomer();
		return false;
	});
	
	/*$('#cardNo').focusout(function () {
		if (this.value !== '') {
			this.value = zeroPad($(this).attr('maxlength'), this.value);
		}
	});*/
	
	$.mask.definitions = {
		'N': '[0-9]'
	};
	
	$(cardNo).mask('<?php echo $cardNoMask; ?>');
	
	
	function popAllows(allows) {
		allows = 'x' + allows;
		chk.removeAttr('checked');
		for (i=1; i <= allows.length; i++) {
			var a = allows.substring(i,i+1);
			var b = $('#bit'+i);
			
			if (a==='1') {
				b.attr('checked', 'checked');
			} else {
				b.removeAttr('checked');
			}
		}
	}
});
</script>