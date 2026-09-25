<form id="reportRequestForm" method="post" target="formTarget">
<input type="hidden" name="branchName" id="branchName" value="ALL BRANCHES"/>
    <div id="branchLogReport">
        <h1>Report Request</h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="120"><label for="procType">Process Type: <span class="red">*</span></label></td>
                    <td><select name="procType" id="procType" style="width:230px">
                            <?php echo html_entity_decode($procType); ?>
                    	</select></td>
                </tr>
                <tr>
                    <td><label for="procDateFrom" width="120" id="dtlabel">Date From: </label></td>
                    <td><input type="text" name="procDateFrom" id="procDateFrom" style="width:218px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
                </tr>
                <tr>
                    <td width="120"><label for="procDateTo" id="dttolabel">Date To: </label></td>
                    <td><input type="text" name="procDateTo" id="procDateTo" style="width:218px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
                </tr>
                <tr>
                    <td><label for="trxcode">Select Process:</label></td>
                    <td><select name="trxcode" id="trxcode" style="width:230px">
                            <?php echo html_entity_decode($procList); ?>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="fileType">File Type:</label></td>
                    <td><select name="fileType" id="fileType" style="width:230px">
                            <option value="1">PDF</option>
                            <option value="2">CSV</option>
                        </select></td>
                </tr>
                <?php echo html_entity_decode($branches); ?>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button value="reports/branchlog/preview" id="previewBtn">Submit</button>
        </span>
        <span class="buttons floatRight">
        	<button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var form = $('form');
	
    var dates = $(DTPICKER).datepicker({
		changeMonth: true,
        changeYear: true,
		maxDate: '+0d',
        yearRange: '-10y:-0y',
		onSelect: function( selectedDate, instance ) {

			var option = this.id === 'procDateFrom' ? 'minDate' : 'maxDate',
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( 'option', option, date );
		}
	});
	
    form.validationEngine({
        ajaxFormValidation: false,
        onBeforeAjaxFormValidation: function () {},
        onAjaxFormComplete: function (a, b, c, d) {
			
		},
        scroll: false
    });
    form.validationEngine('attach');
	
	var submitted = false;
	$('#previewBtn').click('click', function (e) {
		
		messageBox('Submit request?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'reports/reportrequest/submit',
					data: form.serialize(),
					dataType: 'json',
					beforeSend: function() {
						waitMessage('Requesting reports...')
					},
					success: function(data) {
						 if (data.success === true) {
			                 messageBox(data.message, 'Confirm', 'confirm', function () {
			                 	window.location.hash = 'reports/reportprocesslist';
        						return false;
			                 });
			            } else {
			                 messageBox(data.message);

			                 $(MSGBOX).one('dialogbeforeclose', function () {
			                 	window.location.hash = 'reports/reportprocesslist';
			                 });
			            }
					}
				})
			);
        })
		e.preventDefault();
	});

	$('#dttolabel').hide();
	$('#procDateTo').hide();

	$('#procType').change(function(e) {
		switch (this.value) {
			case '1':
				$('#dtlabel').text = 'Date:';
				$('#dttolabel').hide();
				$('#procDateTo').hide();
				break;
			case '2':
				$('#dtlabel').text = 'Date:';
				$('#dttolabel').hide();
				$('#procDateTo').hide();
				break;
			case '3':
				$('#dtlabel').text = 'Date From:';
				$('#dttolabel').show();
				$('#procDateTo').show();
				break;
		}
	})

	$('procDateFrom').change(function () {
		if ($('#procType') === 2) {
			this.val();
		}
	})

	var selected = '';

	$('#trxcode').change(function () {
		selected = $(this).find('option:selected');
		$('#fileType').val(selected.attr('rkey'));

		selected.attr('rkey')

		if (selected.attr('rkey') != 3) {
			$('#fileType option').hide();
        	$('#fileType option[value="' + selected.attr('rkey') + '"]').show();
		} else {
			$('#fileType option').show();
		}
	})
	
	$('#trxcode').trigger('change');
	
	/*form.bind('submit', function () {
		$('#previewBtn').attr('disabled', 'disabled');
		return true;
	});*/
	
	/*$(MSGBOX).dialog('option', {
		beforeClose: function () {
			
		}
	});*/
	
    /*if ('<?php //echo $showMsg; ?>' === '1') {
        messageBox('No record found');
        window.location.hash = 'reports/branchlog'
    }*/
	
	$('#branchList').change(function () {
		var selected = $(this).find('option:selected');
		var brName = selected.attr('brname');
		$('#branchName').val(brName);
	});
});
</script>