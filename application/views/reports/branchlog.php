<form id="branchLogReportForm" method="post" target="formTarget">
<input type="hidden" name="branchName" id="branchName" value="ALL BRANCHES"/>
    <div id="branchLogReport">
        <h1>Branch Audit Log Report</h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="120"><label for="procDate">Process Date: <span class="red">*</span></label></td>
                    <td><input type="text" name="procDate" id="procDate" style="width:218px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
                </tr>
                <tr>
                    <td><label for="trxcode">Select Process:</label></td>
                    <td><select name="trxcode" id="trxcode" style="width:230px">
                            <option value="0">ALL</option>
                            <?php echo html_entity_decode($procList); ?>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="procStats">Process Status:</label></td>
                    <td>
                    	<select name="procStats" id="procStats" style="width:170px">
                            <option value="0">ALL</option>
                            <option value="1">COMPLETED</option>
                            <option value="2">FAILED</option>
                        </select>
					</td>
                </tr>
                <?php echo html_entity_decode($branches); ?>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button value="reports/branchlog/preview" id="previewBtn">Preview</button>
        </span>
        <span class="buttons floatRight">
        	<button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var form = $('form');
	
    $(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
		maxDate: '+0d',
        yearRange: '-10y:-0y'
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