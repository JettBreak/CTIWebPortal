<form id="cardSearchForm" method="post">
<div id="cardSearch">
	<h1>Search Card</h1>
    <div id="content">
        <table width="100%">
            <tr>
                <td width="180"><label for="cardBIN2">Card No.:</label></td>
                <td>        
                    <select name="cardBIN1" id="cardBIN1" style="width: 90px">
						<?php echo html_entity_decode($cardBIN); ?>
                    </select>
                    <input type="text" name="cardBIN2" id="cardBIN2" style="width: 106px" maxlength="13" class="validate[custom[onlyNumberSp]] numbersOnly" />
                </td>
            </tr>
            <tr>
            	<td><label for="custNo">Customer No.:</label></td>
                <td><input type="text" name="custNo" id="custNo" style="width: 200px" maxlength="11" class="validate[custom[onlyNumberSp]]" /></td>
            </tr>
            <tr>
            	<td><label for="lastName">Last Name / Company Name:</label></td>
                <td><input type="text" name="lastName" id="lastName" style="width: 200px" maxlength="50" /></td>
            </tr>
            <tr>
            	<td><label for="firstName">First Name:</label></td>
                <td><input type="text" name="firstName" id="firstName" style="width: 200px" maxlength="50" /></td>
            </tr>
		</table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button value="card/search">Search</button
        ><button value="card/browse">Browse</button
        ><button value="card/new">New</button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Clear</button
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
	
	$($resetBtn).click(function() {
		$('#cardBIN2').focus();
	});
});
</script>