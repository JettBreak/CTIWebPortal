<form id="verifyMobileForm" method="post">
<div id="verifyMobile">
	<h1>Verify Mobile</h1>
    <div id="content">
    	<span class="hint">
        	Enter Cell Number then press Verify to Search <span style="margin-left: 50px;">
        </span>
        <table width="100%">
            <tr>
                <td width="100"><label for="cellBIN2">Mobile No.: <span class="red">*</span></label></td>
                <td>        
                    <select name="cellBIN1" id="cellBIN1" style="width: 70px">
						<?php echo $cellBIN; ?>
                    </select>
                    <input type="number" name="cellBIN2" id="cellBIN2" style="width: 80px" maxlength="7" class="validate[required,custom[onlyNumberSp]] numbersOnly" />
                </td>
            </tr>
		</table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button type="submit" value="card/verifymobile/submit">Verify</button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn">Clear</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var form = $('form');
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Verifying Mobile Number...');
		},
		onAjaxFormComplete: function(f, status, data, options) {
			if (data.success === true) {
				//redirect
				$(MSGBOX).dialog('close');
				window.location.hash = 'card/mobileinfo';
			} else {
				form[0].reset();
				messageBox(data.message);
			}
		},
		scroll: false
	});
	form.validationEngine('attach');
});
</script>