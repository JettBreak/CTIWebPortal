<form id="issueLogForm" method="post">
<?php echo html_entity_decode($hiddenInput); ?>
    <div id="issueLog">
        <h1><?php echo $title; ?></h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - Required Fields</span>
            <table width="100%">
            	<tr>
                	<td width="180">Terminal Code:</td>
                    <td><input type="text" name="termCode" style="width:180px" value="<?php echo $termCode; ?>" readonly/></td>
                </tr>
            	<tr>
                	<td><label for="dtLog">Date:</label></td>
                    <td><input type="text" id="dtLog" name="dtLog" class="datePicker" style="width:180px" value="<?php echo $currentDT; ?>" readonly/></td>
                </tr>
                <tr>
                	<td><label for="issueType">Issue Type:</label></td>
                    <td><select id="issueType" name="issueType" style="width:192px">
                    	<?php echo html_entity_decode($issueTypes); ?>
                    </select></td>
                </tr>
                <tr id="hardwareTypeRow"<?php echo html_entity_decode($hardwareTypeRow); ?>>
                	<td><label for="hardwareType">Hardware Type:</label></td>
                    <td><select id="hardwareType" name="hardwareType" style="width:192px">
                    	<?php echo html_entity_decode($hardwareTypes); ?>
                    </select></td>
                </tr>
                <tr id="othersRow"<?php echo html_entity_decode($othersRow); ?> default="<?php echo $othersDefault; ?>">
                	<td><label for="others">Others:</label></td>
                    <td><input type="text" name="others" id="others" style="width:180px" value="<?php echo $others; ?>"/></td>
                </tr>
                <tr>
                	<td><label for="downtime">Downtime:</label></td>
                    <td><input type="text" id="downtime" name="downtime" class="validate[funcCall[checkDowntime]] numbersOnly" style="width:180px" value="<?php echo $downtime; ?>" placeholder="HH:mm" maxlength="5"/></td>
                </tr>
                <tr>
                	<td><label for="dtResolved">Date Resolved:</label></td>
                    <td><input type="text" id="dtResolved" name="dtResolved" class="datePicker" style="width: 180px" value="<?php echo $dtResolved; ?>" readonly default="<?php echo $dtResolvedDefault; ?>"/></td>
				</tr>
                <tr id="exDowntimeRow"<?php echo html_entity_decode($exDowntimeRow); ?>>
                	<td><label for="exDowntime">Exempted Downtime:</label></td>
                    <td><input type="text" id="exDowntime" name="exDowntime" class="validate[funcCall[checkExDowntime]] numbersOnly" style="width:180px" value="<?php echo $exDowntime; ?>" placeholder="HH:mm" maxlength="5"/></td>
                </tr>
                <tr id="reasonRow"<?php echo html_entity_decode($reasonRow); ?>>
                	<td style="vertical-align: top !important"><label for="reason">Reason:</label></td>
                    <td><textarea name="reason" id="reason" style="width:180px;height:50px" class="validate[maxSize[50]]"><?php echo $reason;?></textarea></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="<?php echo $formAction; ?>">Submit</button
            >
        </span>
        <span class="buttons floatRight">
        	<button id="backBtn">Back</button
        	><button id="resetBtn" type="reset">Reset</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function() {
	var submitBtn = '#submitBtn';
	var backBtn = '#backBtn';
	var resetBtn = '#resetBtn';
	var form = $('form');
	
    initSession('<?php echo $sessionExp; ?>');
	$(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
		maxDate: '+0d',
        yearRange: '-10y:-0y'
    });
	
	$('#dtLog').datepicker('option', {
		onSelect: function( selectedDate, instance ) {	
			var target = '#dtResolved';
			var option = 'minDate',
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			$(target).datepicker( 'option', option, date );
			
			//if ($(target).val() === 'Unresolved') {
			$(target).val('Unresolved');
			//}
			$('#exDowntime').val('00:00');
			$('#reason').val('');
		}
	});
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('<?php echo $waitMsg; ?>');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			//redirect
			messageBox(data.message);
            if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'maintenance/issuelist/<?php echo $termCode; ?>';
				});
            }
        },
        scroll: false
    });
	form.validationEngine('attach');
	
	$(submitBtn).click(function (e) {
		if (form.validationEngine('validate') === true) {
			messageBoxV2('Are all entries correct?', 'Confirm', {
				OK: function () {
					form.submit();
				},
				Cancel: function () {
					$(this).dialog('close');
				}
			});
		}
		e.preventDefault();
	});
	
	$(backBtn).click(function (e) {
		window.location.hash = 'maintenance/issuelist/<?php echo $termCode; ?>';
		e.preventDefault();
	});
	
	$(resetBtn).click(function () {
		if ($('#dtResolved').attr('default') === '0') {
			$('#exDowntimeRow, #reasonRow').hide();
		}
		
		if ($('#othersRow').attr('default') === '0') {
			if ($('#others').is(':visible')) {
				$('#othersRow').hide();
				$('#hardwareTypeRow').show();
			}
		}
	});

	$('#exDowntime, #downtime').keypress(function () {
		if (this.value.length === 2) {
			this.value = this.value + ':'; 
		}
	});
	
	$('#downtime').focusout(function () {
		if (this.value === '00:00') {
			this.value = '01:00';
		}
		if ( parseInt(this.value.replace(':','')) > 2400 ) {
			this.value = '24:00';
		}
	}).change(function () {
		$('#exDowntime').val('00:00');
	});
	
	$('#issueType').change(function () {
		if (this.value === '1') {
			$('#hardwareTypeRow').show();
			$('#othersRow').hide();
		} else if (this.value === '4') {
			$('#othersRow').show();
			$('#hardwareTypeRow').hide();
		} else {
			$('#hardwareTypeRow, #othersRow').hide();
		}
	});
	
	$('#hardwareType').change(function () {
		if (this.value === '6') {
			$('#othersRow').show();
		} else {
			$('#othersRow').hide();
		}
	});
	
	$('#dtResolved').change(function () {
		$('#exDowntimeRow, #reasonRow').show();
	});
});
//HH:mm
function checkDowntime(field, rules, i, options) {
	if (!field.val().match(/^([0-1]?[0-9]{1}|2[0-4]{1}):([0-5]{1}[0-9]{1})$/)) {
		return '* Invalid format. Must be HH:mm';
	}
}
function checkExDowntime(field, rules, i, options) {
	if (!field.val().match(/^([0-1]?[0-9]{1}|2[0-4]{1}):([0-5]{1}[0-9]{1})$/)) {
		return '* Invalid format. Must be HH:mm';
	} else {
		var downtime = $('#downtime').val();
		var downtime = parseInt(downtime.replace(':',''));
		
		var exdowntime = field.val();
		var exdowntime = parseInt(exdowntime.replace(':',''));
		if (exdowntime > downtime) {
			return '* Must not be greater than downtime';
		}
	}
}
</script>