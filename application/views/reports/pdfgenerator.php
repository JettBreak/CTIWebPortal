<form id="pdfGeneratorForm" method="post" target="formTarget">
<input type="hidden" name="branchName" id="branchName" value="ALL BRANCHES"/>
<div id="pdfGenerator" style="width:500px">
	<h1><?php echo $title; ?></h1>
    <div id="content">
    	<table>
        	<tr>
                <td width="120"><label>Transaction:</label></td>
            	<td>
                	<input type="radio" name="reports" id="currentLogRBtn" value="0" width="150" checked="checked" />
                    <label for="currentLogRBtn">Current log</label>
                    <input type="radio" name="reports" id="historyRBtn" value="1" width="150" />
                    <label for="historyRBtn">History</label>
                </td>
            </tr>
            <tr>
            	<td><label for="type">Type:</label></td>
                <td>
                	<select id="type" style="width:162px">
                        <option value="ALL">ALL</option>
                    	<option value="ACQ">Acquirer</option>
                        <option value="ISS">Issuer</option>
                    </select>
                
                
                	<!--<input type="radio" name="type" id="acqRBtn" value="ACQ" width="150" checked="checked" />
                    <label for="acqRBtn">Acquirer</label>
                    <input type="radio" name="type" id="issRBtn" value="ISS" width="150" />
                    <label for="issRBtn">Issuer</label>-->
                </td>
            </tr>
        	<tr>
            	<td width="100"><label for="reportType">Report:</label></td>
                <td>
                	<select name="reportType" id="reportType" style="width:362px">
                    	<?php echo html_entity_decode($reportTypes); ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td width="100"><label for="termType">Channel Type:</label></td>
                <td>
                	<select name="termType" id="termType" style="width:162px">
                    	<?php echo html_entity_decode($termTypes); ?>
                    </select>
                </td>
            </tr>
			<tr>
                <td width="100"><label for="dtFrom">From:</label></td>
                <td><input type="text" name="dtFrom" id="dtFrom" style="width:150px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
            </tr>
            <tr>
                <td><label for="dtTo">To:</label></td>
                <td><input type="text" name="dtTo" id="dtTo" style="width:150px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
            </tr>
            <?php echo html_entity_decode($branches); ?>
        </table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button value="<?php echo $formAction; ?>" id="previewBtn">Preview</button
        >
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Reset</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var previewBtn = '#previewBtn';
	var reportType = '#reportType';
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
	
	$(reportType).change(function () {
		//var selected = $(this).find('option:selected');
		//var action = selected.attr(''
		//console.log(this.value);
		$(previewBtn).val(this.value);
		
		var selected = $(reportType).find('option:selected');
		var isChannel = selected.attr('isChannel');
		
		var disabled = isChannel === '1' ? false : true;
		$('#termType').attr('disabled', disabled);
		
		if (disabled) {
			//set to all
			$('#termType option')
				.removeAttr('selected')
				.first()
				.attr('selected', true);
		}
	}).trigger('change');
	
	
	$('#type').change(function () {
		var r = $('#reportType');
		r.find('option').hide();
		r.find('option').removeAttr('selected');
		
		var selected = null;
		if (this.value === 'ALL') {
			selected = r.find('option');
		} else {
			selected = r.find('option[rtype="'+ this.value +'"]');
		}
		selected.show().first().attr('selected', true);
	});
});
</script>