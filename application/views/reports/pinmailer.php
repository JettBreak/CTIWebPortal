<form id="acqForm" method="post" enctype="multipart/form-data" target="formTarget">
<div id="acq">
	<h1><?php echo $title; ?></h1>
    <div id="content">
    	<table>
            <tr>
            	<td style="width: 100px;"><label for="custNo">Job Number:</label></td>
                <td><input type="number" name="jobNo" id="jobNo" style="width: 100px" min="0" value="0" maxlength="11" class="validate[custom[onlyNumberSp]]" /></td>
            </tr>
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
	var form = $('form');
	var previewBtn = '#previewBtn';
	
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