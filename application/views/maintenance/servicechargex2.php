<form id="serviceChargeForm" method="post">
<?php echo html_entity_decode($hiddenInput); ?>
<input type="hidden" name="checkIB" id="checkIB" value="<?php echo $checkIB; ?>"/>
<div style="width:850px">
    <h1><?php echo $title; ?></h1>
    <div id="content">
    	<table width="100%">
    		<tr>
    			<td width="120"><label for="cardtype">Type:</label></td>
                <td colspan="3">
                	<select name="cardtype" id="cardtype" style="width:162px">
                		<?php echo html_entity_decode($cardtype); ?>
                	</select>
               	</td>
            </tr>
        	<tr>
            	<td width="120"><label for="termCode">Terminal:</label></td>
                <td><input type="text" name="termCode" id="termCode" style="width:150px" value="<?php echo $termCode; ?>" readonly/></td>
                <td width="120"><label for="feeType">Fee Type:</label></td>
                <td><select name="feeType" id="feeType" style="width:162px"><?php echo html_entity_decode($feeTypes); ?></select></td>
            </tr>
            <tr>
            	<td><label for="branchx">Branch:</label></td>
                <td><select name="branchx" id="branchx" style="width:162px"><?php echo html_entity_decode($branchList); ?></select></td>
                <td><label for="minRange"<?php echo $rangeAttr; ?>>Minimum Range:</label></td>
                <td><input type="text" name="minRange" id="minRange" style="width:150px" class="currencyOnly" value="<?php echo $minRange; ?>" maxlength="12"<?php echo $rangeAttr; ?>/></td>
            </tr>
            <tr>
            	<td><label for="serviceType">Service Type:</label></td>
                <td><select name="serviceType" id="serviceType" style="width:162px"><?php echo html_entity_decode($serviceTypes); ?></select></td>
                <td><label for="maxRange"<?php echo $rangeAttr; ?>>Maximum Range:</label></td>
                <td><input type="text" name="maxRange" id="maxRange" style="width:150px" class="currencyOnly" value="<?php echo $maxRange; ?>" maxlength="12"<?php echo $rangeAttr; ?>/></td>
            </tr>
            <tr>
            	<td><label for="terminalType">Terminal Type:</label></td>
                <td><select name="terminalType" id="terminalType" style="width:162px"><?php echo html_entity_decode($terminalTypes); ?></select></td>
                <td><label for="feeValue"><?php echo $feeValueLabel; ?></label></td>
                <td><input type="text" name="feeValue" id="feeValue" style="width:150px" <?php echo $feeAttr; ?> value="<?php echo $feeValue; ?>"/></td>
            </tr>
            <tr>
            	<td><label for="cardholder">Cardholder:</label></td>
                <td><select name="cardholder" id="cardholder" style="width:162px"><?php echo html_entity_decode($cardholders); ?></select></td>
                <td><label for="chargeType">Charge Type:</label></td>
                <td><?php echo html_entity_decode($chargeTypes); ?></td>
            </tr>
            <tr>
            	<td><label for="networkType">Network Type:</label></td>
                <td><select name="networkType" id="networkType" style="width:162px"><?php echo html_entity_decode($networkTypes); ?></select></td>
                <td><label for="serviceCode">Service Code:</label></td>
                <td><?php echo html_entity_decode($serviceCodeList); ?></td>
            </tr>
            <tr>
            	<td><label for="transaction">Transaction:</label></td>
                <td colspan="3"><?php echo html_entity_decode($transactionList); ?></td>
            </tr>
            <tr>
            	<td><label for="remarks">Remarks:</label></td>
                <td colspan="3"><input type="text" name="remarks" id="remarks" style="width:328px" value="<?php echo $remarks; ?>" maxlength="50"/></td>
            </tr>
        </table>   
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="saveBtn" value="<?php echo $formAction; ?>"><?php echo $submitLabel; ?></button
        >
    </span>
	<span class="buttons floatRight">
    	<button id="backBtn">Back</button
        ><button type="reset" id="resetBtn">Reset</button
        ><button class="closebtn">Close</button>
    </span>
</div>
</form>
<style>
label[disabled] {
	color: #aaa;
}
input[disabled] {
	border: 1px solid #000;
}
</style>
<script>
$(function () {
	var backBtn = '#backBtn';
	var resetBtn = '#resetBtn';
	var form = $('form');
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Saving...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/servicecharges';
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$('#saveBtn').click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
                form.submit();
            });
        }
		e.preventDefault();
	});
	
	$('#feeType').change(function () {
		var text = '';
		var minRange = '#minRange';
		var maxRange = '#maxRange';
		var feeValue = '#feeValue';
		
		switch (this.value) {
			case 'F': //fixed
				//disable min and max range
				$(minRange + ', label[for="'+ $(minRange).attr('id') +'"], ' + maxRange + ', label[for="'+ $(maxRange).attr('id') +'"]').attr('disabled', 'disabled');
				text = 'Charge Amount:';
				
				var feeAttr = {
					class: 'currencyOnly',
					maxlength: 12,
					value: 0.00
				}
				
				break;
			case 'R': //range
				//enable min and max range
				$(minRange + ', label[for="'+ $(minRange).attr('id') +'"], ' + maxRange + ', label[for="'+ $(maxRange).attr('id') +'"]').removeAttr('disabled');
				text = 'Charge Amount:';
				
				var feeAttr = {
					class: 'currencyOnly',
					maxlength: 12,
					value: 0.00
				}
				
				break;
			case 'P': //percentage
				//disable min and max range
				$(minRange + ', label[for="'+ $(minRange).attr('id') +'"], ' + maxRange + ', label[for="'+ $(maxRange).attr('id') +'"]').attr('disabled', 'disabled');
				text = 'Rate (%):';
				
				var feeAttr = {
					class: 'integersOnly',
					maxlength: 3,
					value: 0
				}
				
				break;
		}
		
		var chargeAmt = $('#feeValue');
		$('label[for="'+ chargeAmt.attr('id') +'"]').text(text);
		
		$(feeValue).attr(feeAttr);
	});
	
	$('#minRange').focusout(function () {
		var maxInput = $('#maxRange').val();
		var maxInput = parseInt(maxInput.replace(/,/g, ''));
		var minInput = 0;
		var val = parseInt(this.value);
		
		if (val < minInput || this.value === '') {
			return this.value = minInput;
		}
		
		if (val > maxInput) {
			return this.value = maxInput;
		}
	});
	
	$('#maxRange').focusout(function () {
		var maxInput = '999999999999.00';
		var minInput = $('#minRange').val();
		var minInput = parseInt(minInput.replace(/,/g, ''));
		var val = parseInt(this.value);
		
		if (val < minInput || this.value === '') {
			return this.value = minInput;
		}
		
		if (val > maxInput) {
			return this.value = maxInput;
		}
	});
	
	$('#serviceType').change(function () {
		//filter termtypes
		var termTypes = $(this).find('option:selected').attr('term');
		termTypes = termTypes.split(',');
		termTypes.push('zzzz');
		
		var instance = $('#terminalType');
		instance.find('option').removeAttr('selected').hide();
		
		$.each(termTypes, function(index, value) {
			instance.find('option[value='+ value +']').show();
		});
		
    	instance.find('option:visible').first().attr('selected', true);
		
		//filter auth
		var auth = $(this).find('option:selected').attr('auth');
		auth = auth.split(',');
		
		var instance = $('#cardholder');
		instance.find('option').removeAttr('selected').hide();
		
		$.each(auth, function(index, value) {
			instance.find('option[value='+ value +']').show();
		});
		
    	instance.find('option:visible').first().attr('selected', true);
		
		//set checkIB value
		var ib = $(this).find('option:selected').attr('ib');
		$('#checkIB').val(ib);
	});
	
	$('#chargeType').change(function () {
		var instance = $('#serviceCode');
		
		instance.find('option').removeAttr('selected').hide();
		instance.find('option[chargetype="' + this.value + '"]')
			.show()
			.first()
			.attr('selected', true);
	});
	
	$(backBtn).click(function (e) {
		window.location.hash = 'maintenance/servicecharges';
		e.preventDefault();
	});
});
</script>