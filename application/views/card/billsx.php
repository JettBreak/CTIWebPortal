<form id="billsForm" method="post">
<input type="hidden" name="instID" id="instID" value="<?php echo $instID; ?>"/>
    <div id="billsForm">
        <h1><?php echo $title; ?></h1>
        <div id="content"><span class="hint floatRight">All Fields Required</span>
            <table width="100%">
            	<tr>
                	<td width="150"><label for="institution">Institution:</label></td>
                    <td>
                    	<select id="institution" name="institution" style="width:212px;" class="validate[required]">
                        	<?php echo html_entity_decode($billsInstList); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                	<td><label for="billNo">Bill No.:</label></td>
                    <td>
                    	<select id="billNo" name="billNo" style="width:212px;" class="validate[required]">
                        	<?php echo html_entity_decode($billsNo); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                	<td><label for="subsNo">Subscriber No.:</label></td>
                    <td><input id="subsNo" name="subsNo" style="width:200px;" class="validate[required]" value="<?php echo $subsNo; ?>"/></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                    <td>(Format: <span id="billNumFormat"><?php echo $billNumFormat; ?></span>)</td>
                </tr>
                <tr>
                	<td><label for="subsName">Subscriber Name:</label></td>
                    <td><input id="subsName" name="subsName" style="width:200px;" class="validate[required]" value="<?php echo $subsName; ?>"/></td>
                </tr>
                <tr>
                	<td><label for="statusx">Status:</label></td>
                    <td>
                    	<select id="statusx" name="statusx" style="width:212px;" class="validate[required]">
                        	<?php echo html_entity_decode($bpayStatOptions); ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="<?php echo $formAction; ?>">Submit</button
            ><button type="reset">Reset</button>
        </span>
        <span class="buttons floatRight">
        	<button id="backBtn">Back</button
        	><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
	var submitBtn = '#submitBtn',
		backBtn = '#backBtn',
		instList = '#institution',
		subsNo = '#subsNo',
        form = $('form');
	
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Validating request...');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success) {
					window.location.hash = 'card/billspayment';
				} else if (data.msgType === 43) {
					$(subsNo).val('').focus();
				}
			});
		},
		scroll: false
	});
	form.validationEngine('attach');
	
	/*$(submitBtn).click(function (e) {
		if ($(subsNo).val() !== '') {
			$.ajax({
				type: 'POST',
				url: 'card/billsadd/validateSubscriber',
				dataType: 'json',
				data: {
					billID: $('#institution').val(),
					subsNo: $(subsNo).val()
				},
				beforeSend: function () {
					$(subsNo).validationEngine('showPrompt', '* Processing...', 'load', 'topRight', true);
				},
				success: function (data) {
					if (data.success) {				
						form.submit();
					} else {
						$(subsNo).validationEngine('showPrompt', '* ' + data.message, '', 'topRight', true);
					}
				}
			});
		}
		e.preventDefault();
	});*/
	
	$(backBtn).click(function (e) {
		window.location.hash = 'card/billspayment';
		e.preventDefault();
	});
	
	$.mask.definitions = {
		'N': '[0-9]',
		'A': '[a-zA-Z]',
		'X': '[a-zA-Z0-9]',
		'S': '[_]',
		'*': '[a-zA-Z0-9]'
	};
	
	$(subsNo).mask('<?php echo $subsNoMask; ?>');
	
	$(instList).change(function () {
		var selected = $(this).find('option:selected');
		var snFormat = selected.attr('snformat');
		var mask = snFormat !== 'None' ? snFormat : '?*******************************';
		var instID = selected.attr('instid');
		
		$('#instID').val(instID);
		$('#billNumFormat').text(snFormat);
		
		$(subsNo)
			.val('')
			.mask(mask);
	});
});

function validateSubscriberNo(input, c, i, d)
{
	if (input.val() !== '') {
		$.ajax({
			type: 'POST',
			url: 'card/billsadd/validateSubscriber',
			dataType: 'json',
			data: {
				billID: $('#institution').val(),
				subsNo: $('#subsNo').val()
			},
			beforeSend: function () {
				input.validationEngine('showPrompt', '* Processing...', 'load', 'topRight', true);
			},
			success: function (data) {
				var pass = data.success ? 'pass' : '';
				
				input.validationEngine('showPrompt', '* ' + data.message, pass, 'topRight', true);
			}
		});
	}
}
</script>