<form id="cardTypeChangeForm" method="post">
<input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
<div id="cardTypeChange">
	<h1>Card Type Change <span class="floatRight">[<?php echo $cardNo; ?>]</span></h1>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabCardLink">Limits</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content">
        	<table width="100%">
            	<tr>
                	<td width="100"><label for="dtEnrolled">Date Enrolled:</label></td>
                    <td><input type="text" id="dtEnrolled" style="width: 200px" value="<?php echo $dtEnrolled; ?>" readonly/></td>
                    <td rowspan="8" width="220">
                    Allows:<br />
                    <div style="width:100%;height:204px;overflow:auto;border:1px solid #333">
                    	<?php echo html_entity_decode($allows); ?>
                    </div>
                    </td>
                </tr>
                <tr>
                	<td><label for="custCard">Customer Card:</label></td>
                    <td><input type="text" id="custCard" style="width: 200px" value="<?php echo $cardNo; ?>" readonly/></td>
                </tr>
                <tr>
                	<td><label for="cardStatus">Card Status:</label></td>
                    <td><input type="text" id="cardStatus" style="width: 200px" value="<?php echo $status; ?>" readonly/></td>
                </tr>
                <tr>
                	<td><label for="cardType">Card Type:</label></td>
                    <td><input type="text" id="cardType" style="width: 200px" value="<?php echo $acctdesc; ?>" readonly/></td>
                </tr>
                <tr>
                	<td><label for="embossName">Emboss Name:</label></td>
                    <td><input type="text" id="embossName" style="width: 200px" value="" readonly/></td>
                </tr>
                <tr>
                	<td><label for="custName">Customer Name:</label></td>
                    <td><input type="text" id="custName" style="width: 200px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                	<td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                	<td><label for="newCardType"><strong>New Card Type:</strong></label></td>
                    <td>
                    	<select name="newCardType" id="newCardType" style="width:212px">
                        	<?php echo html_entity_decode($cardType); ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div id="tabCardLink" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Available Cycle Amt.</th>
                        <th>Maximum Cycle Amt.</th>
                        <th>Available Ctr.</th>
                        <th>Max Counter</th>
                        <th>Min Transaction Amt.</th>
                        <th>Max Transaction Amt.</th>
                        <th>Cycle Period</th>
                        <th>Time Limit</th>
                        <th>Avail. No Fee Ctr.</th>
                        <th>Max No Fee Ctr.</th>
                        <th>Avail. No Fee Amt.</th>
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
    	<button id="saveBtn" value="card/changetype/submit">Save</button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Reset</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>
<style>
.dataTables_scrollBody {
	height: 150px !important;
	max-height: 150px !important;
}
</style>

<script>
$(function() {
	var $saveBtn 	= '#saveBtn',
		$resetBtn 	= '#resetBtn',
		$refreshBtn = '#refreshBtn',
		$form 		= $('form');
	
	$form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Sending request...');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			if (data.success === true) {
				window.location.hash = 'card/search';
			}
			messageBox(data.message);
		},
		scroll: false
	});
	$form.validationEngine('attach');
	
	$(DATATABLE).find('tbody tr').die('dblclick');
	
	oTable = $(DATATABLE).dataTable({
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
			$.fn.dataTableExt.iApiIndex = 0;
			oTable.fnAdjustColumnSizing();
		}
		return false;
	});
	
	$($saveBtn).click(function(e) {
		messageBox('Continue card type change?', 'Confirm', 'confirm', function() {
			$form.submit();
		});
		e.preventDefault();
	});
	
	$($resetBtn).click(function() {

	});
});
</script>