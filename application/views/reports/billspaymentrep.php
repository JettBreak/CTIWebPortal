<form id="acqForm" method="post" enctype="multipart/form-data" target="formTarget">
<input type="hidden" name="trxType" value=""/>
<div id="acq">
	<h1><?php echo $title; ?></h1>
    <div id="content">
    	<table>
			<tr>
                <td width="100"><label for="trxDate">Date: <span class="red">*</span></label></td>
                <td><input type="text" name="trxDate" id="trxDate" style="width:150px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
            </tr>
        </table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button value="<?php echo $formAction; ?>" id="previewBtn">Process</button
        >
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Clear</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {
	var form = $('form');
    $(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
		maxDate: '+0d',
        yearRange: '-10y:-0y'
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
	form.submit(function () {
		waitMessage('Processing...');
		
		if ($('#formTarget').length > 0) {
			$('#formTarget').remove();
		}
		
		$('#content').append('<iframe id="formTarget" name="formTarget" src="#" onLoad="formComplete()" class="hidden"></iframe>');
	});
	
	/*$('#previewBtn').click(function () {
		var request = new XMLHttpRequest();  
		request.open('POSt', this.value, false);   
		
		var bb = new BlobBuilder();
		bb.append('trxDate', '10/10/2011');
		
		request.send(bb.getBlob('text/plain')); 
		return false;
	});*/
});
function formComplete() {
	if ($(MSGBOX).length > 0) {
		$(MSGBOX).dialog('close');
	}
}
	/*var previewBtn = '#previewBtn';
	var form = $('form');
    $(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
		maxDate: '+0d',
        yearRange: '-10y:-0y'
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
    if ('<//?php echo $showMsg; ?>' === '1') {
        messageBox('No record found');

    }
	
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
});

function formComplete() {
	if ($(MSGBOX).length > 0) {
		$(MSGBOX).dialog('close');
	}
}*/
</script>