<form id="mobileInfoForm" method="post">
<div id="mobileInfo">
	<h1>Mobile Information <span class="floatRight">[<?php echo $cellNo; ?>]</span></h1>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabCardLink">Card Link</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content">            
            <table>
            	<tr>
                	<td width="120"><label for="branch">Branch:</label></td>
                    <td><input type="text" id="branch" name="branch" style="width: 150px;" /></td>
                    <td rowspan="7">&nbsp;</td>
                    <td rowspan="7" style="vertical-align: top !important;">
                    	<fieldset>
                        	<legend>Allowed Transactions:</legend>
                        <ul>
                    		<li><input type="checkbox" name="allowedTransactions" />Mobile SA Inquiry</li>
                        	<li><input type="checkbox" name="allowedTransactions" />Mobile CA Inquiry</li>
                        </ul>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                	<td><label for="cellBIN2">Mobile:</label></td>
                    <td>
                    	<select id="cellBIN1" name="cellBIN1" style="width: 70px;">
                        	<?php echo $cellBIN; ?>
                        </select>
                    	<input type="text" id="cellBIN2" name="cellBIN2" style="width: 76px;" class="numbersOnly" maxlength="7" />
					</td>
                </tr>
                <tr>
                	<td><label for="dtCreated">Date Created:</label></td>
                    <td><input type="text" id="dtCreated" name="dtCreated" style="width: 150px;" readonly /></td>
                </tr>
                <tr>
                	<td><label for="dtLastActivity">Date Last Activity:</label></td>
                    <td><input type="text" id="dtLastActivity" name="dtLastActivity" style="width: 150px;" readonly /></td>
                </tr>
                <tr>
                	<td><label for="lastTransaction">Last Transaction:</label></td>
                    <td><input type="text" id="lastTransaction" name="lastTransaction" style="width: 150px;" readonly /></td>
                </tr>
                <tr>
                	<td><label for="mobileStatus">Mobile Status:</label></td>
                    <td>
                    	<select id="mobileStatus" name="mobileStatus" style="width: 162px;">
                        	<option>Inactive</option>
                            <option>Active</option>
                            <option>Blocked</option>
                            <option>Closed</option>
                        </select>
                    </td>
                </tr>
            </table> 
        </div>
        <div id="tabCardLink" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Card No.</th>
                        <th>Customer Name</th>
                        <th>Date Enrolled</th>
                        <th>Date Last Activity</th>
                        <th>Card Status</th>
                    </tr>
                </thead>
            </table>
        </div>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="saveBtn">Save</button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn">Reset</button
        ><button id="refreshBtn" class="hidden">Refresh</button
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
			$.fn.dataTableExt.iApiIndex = 0;
			oTable.fnAdjustColumnSizing();
			
			if ($activeTab === '#tabCardLink') {
				$($resetBtn).hide();
				$($refreshBtn).show();
			} else {
				$($resetBtn).show();
				$($refreshBtn).hide();
			}
		}
		return false;
	});
	
	$($resetBtn).click(function() {

	});
});
</script>