<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<form id="cardEnrollmentForm" method="post">
<div id="cardEnrollment">
	<h1>Card Enrollment</h1>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabAccounts">Accounts</a></li>
        <li><a href="#tabOLLimits">On-Line Limits [XXXXONLN]</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content"> 
            <table width="100%">
                <tr>
                    <td width="140"><label for="branch">Branch:</label></td>
                    <td><input type="text" name="branch" id="branch" style="width: 200px" value="<?php echo $branch; ?>" readonly/></td>
                    <td rowspan="10" width="280">
                        Allowed Transactions:
                        <div style="height:126px;" class="box">
                            <?php echo html_entity_decode($allows); ?>
                        </div>
                        <br />Channel Locking:
                        <div style="height:84px;" class="box">
                            <?php echo html_entity_decode($allows); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label for="cardNo">Card No.:</label></td>
                    <td><input type="text" name="cardBIN" id="cardBIN" style="width: 70px" value="<?php echo $cardNo; ?>" readonly/>-<input type="text" name="cardNo" id="cardNo" style="width: 114px" class="validate[custom[onlyNumberSp]] numbersOnly" maxlength="7"/></td>
                </tr>
                <tr>
                    <td><label for="cardType">Card Type:</label></td>
                    <td>
                        <select name="cardType" id="cardType" style="width:212px">
                            <?php echo html_entity_decode($cardType); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="custName">Customer Name:</label></td>
                    <td><input type="text" name="custName" id="custName" style="width: 200px" readonly/></td>
                </tr>
                <tr>
                    <td><label for="embossName">Emboss Name:</label></td>
                    <td><input type="text" name="embossName" id="embossName" style="width: 200px" maxlength="20"/></td>
                </tr>
                <tr>
                    <td><label for="dtInitIssue">Date Initial Issue:</label></td>
                    <td><input type="text" name="dtInitIssue" id="dtInitIssue" style="width: 120px" value="<?php echo $dtInitIssue; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="dtActivated">Date Activated:</label></td>
                    <td><input type="text" name="dtActivated" id="dtActivated" style="width: 120px" value="<?php echo $dtActivated; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="dtExpiry">Date Expiry:</label></td>
                    <td><input type="text" name="dtExpiry" id="dtExpiry" style="width: 120px" value="<?php echo $dtExpiry; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="defFastCash">Def. Fast Cash:</label></td>
                    <td><input type="text" name="defFastCash" id="defFastCash" style="width: 200px" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cardStatus">Card Status:</label></td>
                    <td><input type="text" name="cardStatus" id="cardStatus" style="width: 200px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
            </table>
		</div>
        <div id="tabAccounts" class="tab_content nopadding">
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
                        <th>Max No Fee Ctr.</th>
                        <th>Available No Fee Amt.</th>
                        <th>Max No Fee Amt.</th>
                        <th>No Fee Cycle Period</th>
                    </tr>
                </thead>
            </table> 
        </div>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button value="card/search">Search</button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Reset</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var $resetBtn = '#resetBtn',
		$form 	  = $('form');
	$form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Sending search transaction...');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			if (data.success === true) {
				//redirect
				$(MSGBOX).dialog('close');
				window.location.hash = data['page'];
			} else {
				$form[0].reset();
				$('#cardBIN2').focus();
				messageBox(data.message);
			}
		},
		scroll: false
	});
	$form.validationEngine('attach');
	
	$(DATATABLE).find('tbody tr').die('dblclick');
	
	oTable = $(DATATABLE).dataTable({
		'bRetrieve':true,
		'bJQueryUI': true,
		'aaSorting': [], //disable initial sorting
		'sScrollY': '100%',
		'sScrollX': '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		'sPaginationType': 'full_numbers'
	});
	
	//default Action
	$(TABCONTENT).hide(); //hide all content
	$(TABS).first().addClass('active').show(); //activate first tab
	$(TABCONTENT).first().show(); //show first tab content
	       			
	//onClick Event
	$(TABS).click(function() {
		if (!$(this).hasClass('active')) {
			$form.validationEngine('hideAll');
			$(TABS).removeClass('active'); //remove any "active" class
			$(this).addClass('active'); //add "active" class to selected tab
			$(TABCONTENT).hide(); //hide all tab content
			
			var $activeTab = $(this).find('a').attr('href'); //find the rel attribute value to identify the active tab + content
			$($activeTab).show();
			
			$(WRAPPER).height('auto');
			//$.fn.dataTableExt.iApiIndex = 0;
			oTable.fnAdjustColumnSizing();
		}
		return false;
	});
	
	$($resetBtn).click(function() {

	});
});
</script>