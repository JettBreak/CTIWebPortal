<form id="atmKeyForm" method="post">
<input type="hidden" name="oldSecCode" value="<?php echo $oldSecCode; ?>"/>
<div id="atmKey">
	<h1><?php echo $title; ?></h1>
    <div id="content">
    	<span class="floatRight"><span class="red">*</span> - Required Fields</span></span>
    	<table width="100%">
        	<tr>
            	<td width="150"><label for="nodeNamex">Node Name:</label></td>
                <td>
                	<select id="nodeNamex" name="nodeNamex" style="width:162px" class="validate[required]">
                    	<?php echo html_entity_decode($nodeList); ?>
                    </select>
                </td>
            </tr>
        	<tr>
                <td><label for="encMode">Encryption Mode:</label></td>
                <td>
                	<select name="encMode" id="encMode" style="width:162px" class="validate[required]">
                    	<?php echo html_entity_decode($encModeList); ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td><label for="secCode">Security Code: <span class="red">*</span></label></td>
                <td><input type="text" name="secCode" id="secCode" style="width:150px" class="validate[required] alphaNum" value="<?php echo $secCode; ?>" maxlength="18"/></td>
                
                <td><input type="checkbox" name="zone" id="zone" <?php echo $zoneAttr; ?>/><label for="zone">Zone</label></td>
            </tr>
            <tr>
            	<td colspan="4" height="30"><div class="divider"></div><strong>Incoming (Receive Keys)</strong></td>
            </tr>
            <tr>
            	<td><label for="inVariant">Variant:</label></td>
                <td colspan="3"><input type="text" name="inVariant" id="inVariant" style="width:150px" class="validate[required,custom[onlyNumberSp]] numbersOnly" value="<?php echo $inVariant; ?>" maxlength="2"/></td>
            </tr>
            <tr class="pekkek">
            	<td><label for="inMasterKey">Master Key: <span class="index"><?php echo $inMasterKeyIndex; ?></span><span class="red">*</span></label></td>
                <td><input type="text" name="inMasterKey" id="inMasterKey" style="width:250px" class="<?php echo $inMasterKeyAttr; ?>" value="<?php echo $inMKey; ?>" maxlength="<?php echo $inMasterKeyMaxLength; ?>"/></td>
                <td colspan="2"><input type="checkbox" name="inMKey" id="inMKey"<?php echo $inMKeyAttr; ?>/><label for="inMKey">HSM Stored Keys</label></td>
            </tr>
            <tr class="pekkek">
            	<td><label for="inWorkingKey">Working Key: <span class="index"><?php echo $inWorkingKeyIndex; ?></span><span class="red">*</span></label></td>
                <td><input type="text" name="inWorkingKey" id="inWorkingKey" style="width:250px" class="<?php echo $inWorkingKeyAttr; ?>" value="<?php echo $inWKey; ?>" maxlength="<?php echo $inWorkingKeyMaxLength; ?>"/></td>
                <td colspan="2"><input type="checkbox" name="inWKey" id="inWKey"<?php echo $inWKeyAttr; ?>/><label for="inWKey">HSM Stored Keys</label></td>
            </tr>
            <tr>
            	<td><label for="inEncType">Encryption Type:</label></td>
                <td>
                	<select name="inEncType" id="inEncType" style="width:162px">
                    	<?php echo html_entity_decode($encType1); ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td colspan="4" height="30"><div class="divider"></div><strong>Outgoing (Send Keys)</strong></td>
            </tr>
            <tr>
            	<td><label for="outVariant">Variant:</label></td>
                <td colspan="3"><input type="text" name="outVariant" id="outVariant" style="width:150px" class="validate[required,custom[onlyNumberSp]] numbersOnly" value="0" maxlength="2"/></td>
            </tr>
            <tr class="pekkek">
            	<td><label for="outMasterKey">Master Key: <span class="index"><?php echo $outMasterKeyIndex; ?></span><span class="red">*</span></label></td>
                <td><input type="text" name="outMasterKey" id="outMasterKey" style="width:250px" class="<?php echo $outMasterKeyAttr; ?>" value="<?php echo $outMKey; ?>" maxlength="<?php echo $outMasterKeyMaxLength; ?>"/></td>
                <td colspan="2"><input type="checkbox" name="outMKey" id="outMKey"<?php echo $outMKeyAttr; ?>/><label for="outMKey">HSM Stored Keys</label></td>
            </tr>
            <tr class="pekkek">
            	<td><label for="outWorkingKey">Working Key: <span class="index"><?php echo $outWorkingKeyIndex; ?></span><span class="red">*</span></label></td>
                <td><input type="text" name="outWorkingKey" id="outWorkingKey" style="width:250px" class="<?php echo $outWorkingKeyAttr; ?>" value="<?php echo $outWKey; ?>" maxlength="<?php echo $outWorkingKeyMaxLength; ?>"/></td>
            	<td colspan="2"><input type="checkbox" name="outWKey" id="outWKey"<?php echo $outWKeyAttr; ?>/><label for="outWKey">HSM Stored Keys</label></td>
            </tr>
            <tr>
            	<td><label for="outEncType">Encryption Type:</label></td>
                <td>
                	<select name="outEncType" id="outEncType" style="width:162px">
                    	<?php echo html_entity_decode($encType2); ?>
                    </select>
                </td>
            </tr>
        </table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="submitBtn" value="<?php echo $submitBtnVal; ?>">Submit</button>
	</span>
    <span class="buttons floatRight">
    	<button type="reset">Reset</button
        ><button id="backBtn">Back</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var aKey = '#aKey',
		stored = '#stored',
		submitBtn = '#submitBtn',
		backBtn = '#backBtn',
		encMode = '#encMode',
		form = $('form');
		
    initSession('<?php echo $sessionExp; ?>');
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('<?php echo $waitMsg; ?>')
		},
		onAjaxFormComplete: function(form, status, data, options) {
			
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					var msg = 'Information';
					window.location.hash = 'maintenance/securitykey'
				}
			});
		},
		scroll: false
	});
	form.validationEngine('attach');
	
	$(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Confirm', 'confirm', function () {
                form.submit();
            });
        }
    });
	
	$(backBtn).click(function () {
		window.location.hash = 'maintenance/securitykey';
		return false;
	});
	
	//$($resetBtn).click(function() {
	//});
	
	$(encMode).change(function () {
		if (this.value === 'DES') {
			var length = 16;
		} else { //TDES
			var length = 32;
		}
		
		//trim according to length
		var inMasterKey = $('#inMasterKey').val();
		$('#inMasterKey').val(inMasterKey.substr(0, length));
		
		var inWorkingKey = $('#inWorkingKey').val();
		$('#inWorkingKey').val(inWorkingKey.substr(0, length));
		
		var outMasterKey = $('#outMasterKey').val();
		$('#outMasterKey').val(outMasterKey.substr(0, length));
		
		var outWorkingKey = $('#outWorkingKey').val();
		$('#outWorkingKey').val(outWorkingKey.substr(0, length));
		//end
		
		//set maxlength (DES = 16, TDES = 32)
		$('#inMasterKey').attr({
			maxlength: function () {
				if ($('#inMKey').is(':checked')) {
					return 5;
				} else {
					return length;
				}
			},
			class: function () {
				var minSize;
				var filter = 'hexOnly upperCase';
				
				if ($('#inMKey').is(':checked')) {
					minSize = 5;
					filter = 'numbersOnly';
				} else {
					minSize = length;
				}
				return 'validate[required,minSize['+ minSize +']] ' + filter;
			}
		});
		
		$('#inWorkingKey').attr({
			maxlength: function () {
				if ($('#inWKey').is(':checked')) {
					return 5;
				} else {
					return length;
				}
			},
			class: function () {
				var minSize;
				var filter = 'hexOnly upperCase';
				
				if ($('#inWKey').is(':checked')) {
					minSize = 5;
					filter = 'numbersOnly';
				} else {
					minSize = length;
				}
				return 'validate[required,minSize['+ minSize +']] ' + filter;
			}
		});
		
		$('#outMasterKey').attr({
			maxlength: function () {
				if ($('#outMKey').is(':checked')) {
					return 5;
				} else {
					return length;
				}
			},
			class: function () {
				var minSize;
				var filter = 'hexOnly upperCase';
				
				if ($('#outMKey').is(':checked')) {
					minSize = 5;
					filter = 'numbersOnly';
				} else {
					minSize = length;
				}
				return 'validate[required,minSize['+ minSize +']] ' + filter;
			}
		});
		
		$('#outWorkingKey').attr({
			maxlength: function () {
				if ($('#outWKey').is(':checked')) {
					return 5;
				} else {
					return length;
				}
			},
			class: function () {
				var minSize;
				var filter = 'hexOnly upperCase';
				
				if ($('#outWKey').is(':checked')) {
					minSize = 5;
					filter = 'numbersOnly';
				} else {
					minSize = length;
				}
				return 'validate[required,minSize['+ minSize +']] ' + filter;
			}
		});
		
		form.validationEngine('hideAll');
	});
	
	$('.pekkek input[type=checkbox]').change(function() {
		var input = $(this).parents('.pekkek').find('input[type=text]');
		var label = $(this).parents('.pekkek').find('label span.index');
		
		if ($(encMode).val() === 'DES') {
			var length = 16;
		} else { //TDES
			var length = 32;
		}
		
		//form.validationEngine('detach');
		if ($(this).is(':checked')) {
			input
				.val('')
				.attr({
					maxlength: 5,
					class: 'validate[required,custom[onlyNumberSp]] numbersOnly'
				});
			label.html('(index) ');
		} else {
			input
				.val('')
				.attr({
					maxlength: length,
					class: 'validate[required,minSize['+ length +']] hexOnly upperCase'
				});
			label.html('');
		}
		
		form.validationEngine('hideAll')
		//form.validationEngine('attach');
	});
});
</script>