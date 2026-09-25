<form id="atmAvailabilityForm" method="post" target="formTarget">
<input type="hidden" name="branchName" id="branchName" value="ALL BRANCHES"/>
<div id="atmAvailability">
	<h1>ATM Availability Report</h1>
    <div id="content">
    	<table>
			<tr>
                <td width="100"><label for="dtFrom">From: <span class="red">*</span></label></td>
                <td><input type="text" name="dtFrom" id="dtFrom" style="width:150px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
            </tr>
            <tr>
                <td><label for="dtTo">To: <span class="red">*</span></label></td>
                <td><input type="text" name="dtTo" id="dtTo" style="width:150px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
            </tr>
            <?php echo html_entity_decode($branches); ?>
        </table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button value="reports/atmavailability/preview" id="previewBtn">Preview</button
        >
	</span>
    <span class="buttons floatRight">
    	<!--<button id="resetBtn" type="reset">Reset</button
        >--><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var form = $('form');
	
	var dates = $(DTPICKER).datepicker({
		changeMonth: true,
        changeYear: true,
		maxDate: '+0d',
        yearRange: '-10y:-0y',
		onSelect: function( selectedDate, instance ) {
			var option = this.id === 'dtFrom' ? 'minDate' : 'maxDate',
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( 'option', option, date );
		}
	});
	
	form.validationEngine({
        ajaxFormValidation: false,
        onBeforeAjaxFormValidation: function () {
			
		},
        onAjaxFormComplete: function (a, b, c, d) {
			
		},
        scroll: false
    });
    form.validationEngine('attach');
	
    if ('<?php echo $showMsg; ?>' === '1') {
        messageBox('No record found');

    }
	
	var submitted = false;
	$('#previewBtn').click('click', function (e) {
		if (submitted === false) {
			submitted = true;
			var loading = '<center><img src="images/connect.gif" alt="loading" class="loading"></center>';
			//set default params
			if (!title) {
				var title = 'Please wait'
			}
			if ($(DIALOG).length === 0) {
				$('<div id="' + DIALOG.substring(1) + '">' + loading + '</div>').dialog({
					create: function () {
						$(this).load('pdfviewer/preview', function () {
							$(this).dialog('option', 'position', 'center');
							setTimeout(function () {
								form.submit();
								submitted = false;
							}, 500);
						})
					},
					title: 'Preview',
					show: 'fade',
					//hide: 'fade',
					modal: false,
					resizable: false,
					draggable: false,
					buttons: {
						'OK': function () {
							$(this).dialog('close');
						}
					},
					width: 270,
					height: 170,
					close: function () {
					   $(this).remove();
					}
				});
			}
		}
		e.preventDefault();
	});
	
	$('#branchList').change(function () {
		var selected = $(this).find('option:selected');
		var brName = selected.attr('brname');
		$('#branchName').val(brName);
	});
});

</script>