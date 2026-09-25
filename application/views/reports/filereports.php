<form id="fileReportForm" method="post" enctype="multipart/form-data" target="formTarget">
<input type="hidden" name="branchName" id="branchName" value="ALL BRANCHES"/>
    <div id="branchLogReport">
        <h1>File Reports</h1>
        <div id="content">
            <table width="100%">
                <!-- <tr>
                    <td width="120"><label for="procType">Process Type: <span class="red">*</span></label></td>
                    <td><select name="procType" id="procType" style="width:230px">
                            <?php //echo html_entity_decode($procType); ?>
                    	</select></td>
                </tr> -->
                <tr>
                    <td><label for="procDate" width="120" id="dtlabel">Date: </label></td>
                    <td><input type="text" name="procDate" id="procDate" style="width:218px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
                </tr><!-- 
                <tr>
                    <td width="120"><label for="procDateTo" id="dttolabel">Date To: </label></td>
                    <td><input type="text" name="procDateTo" id="procDateTo" style="width:218px" maxlength="10" value="<?php //echo $date; ?>" class="validate[required] datePicker" readonly/></td>
                </tr> -->
                <tr>
                    <td><label for="trxcode">Select Process:</label></td>
                    <td><select name="trxcode" id="trxcode" style="width:230px">
                            <?php echo html_entity_decode($procList); ?>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="xcard" width="120" id="xcardlbl">Card No: </label></td>
                    <td><input type="text" name="xcard" id="xcard" style="width:218px" maxlength="19" class="numbersOnly" /></td>
                </tr>
                <?php echo html_entity_decode($branches); ?>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="previewBtn">Submit</button>
        	<button value="reports/branchlog/preview" id="previewBtnx" class="hidden">Submit</button>
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

			var option = this.id === 'procDate' ? 'minDate' : 'maxDate',
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
			if ($(MSGBOX).length > 0) {
				$(MSGBOX).dialog('close');
			}
		},
        scroll: false
    });
    form.validationEngine('attach');

    $('#previewBtnx').val($('#trxcode').val());

    
    $('#trxcode').change(function () {

    	switch (this.value) {
			case 'loanpayrep':
			case 'onusrep':
			case 'barts':
			case 'bpay':
				$('#xcardlbl').hide();
				$('#xcard').hide();
				$('#previewBtnx').hide();
				$('#previewBtn').show();
				break;
			case 'xcard':
				$('#xcardlbl').show();
				$('#xcard').show();
				$('#previewBtnx').show();
				$('#previewBtn').hide();
				break;
			default:
				$('#xcardlbl').hide();
				$('#xcard').hide();
				$('#previewBtnx').show();
				$('#previewBtn').hide();
				break;
		}

		if ($('#trxcode').val() == 'xcard') {
			$('#previewBtnx').val('card/info/getCardHistory?pr=0&br=0&cr='+$('#xcard').val());
		} else {
    		$('#previewBtnx').val($('#trxcode').val());
		}
    });

    $('#xcard').focusout(function () {
    	$('#previewBtnx').val('card/info/getCardHistory?pr=0&br=0&cr='+$('#xcard').val());
    });

    form.submit(function () {		

		waitMessage('Processing...');

		if ($('#formTarget').length > 0) {
			$('#formTarget').remove();
		}

    	$('#content').append('<iframe id="formTarget" name="formTarget" src="#" onunload="formComplete()" onLoad="formComplete()" class="hidden"></iframe>');

	});

	function formComplete() {
		//console.log('TRIGGERED');
		//if ($(MSGBOX).length > 0) {
			$(MSGBOX).dialog('close');
		//}
	}
	
	var submitted = false;
	$('#previewBtn').click('click', function (e) {
		var trancode = 0;

		//console.log($('#trxcode').val());

		if ($('#trxcode').val() == 'onusrep') {
			trancode = 39;
		} else if ($('#trxcode').val() == 'loanpayrep') {
			trancode = 40;
		} else if ($('#trxcode').val() == 'barts') {
			trancode = 34;
		} else if ($('#trxcode').val() == 'bpay') {
			trancode = 37;
		}
		//alert();
		messageBox('Submit request?', 'Confirm', 'confirm', function () {
				//serialize AJAX request

			
			//console.log($('#trxcode').val());
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'reports/reportrequest/submit',
					data: {
						procType: 1,
						procDateFrom: $('#procDate').val(),
						procDateTo: $('#procDate').val(),
						trxcode: trancode,
						branch: $('#branchList').val()

					},
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

	$('#xcardlbl').hide();
	$('#xcard').hide();

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
			case '5':
				$('#xcardlbl').show();
				$('#xcard').show();
				break;
		}
		console.log(this.value);
	})

	$('procDate').change(function () {
		if ($('#procType') === 2) {
			this.val();
		}
	})
	
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