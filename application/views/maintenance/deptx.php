<form id="departmentForm" method="post">
<?php echo html_entity_decode($hiddenInput); ?>
    <div id="department">
        <h1><?php echo $title; ?></h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - All fields required</span>
            <table width="100%">
                <tr>
                    <td width="150"><label for="deptCode">Department Code: <span class="red">*</span></label></td>
                    <td><input name="newDeptCode" id="newDeptCode" style="width:150px" class="validate[required,custom[integer]] numbersOnly" maxlength="3" value="<?php echo $deptCode; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="deptName">Department Name: <span class="red">*</span></label></td>
                    <td><input name="deptName" id="deptName" style="width:150px" class="validate[required,custom[onlyLetterNumberSp]] alphaNum" value="<?php echo $deptName; ?>" maxlength="30"/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="<?php echo $formAction; ?>">Submit</button
            ><button type="reset">Reset</button>
        </span>
        <span class="buttons floatRight">
        	<button id="backBtn">Back</button
        	><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
		backBtn = '#backBtn',
		deptCode = '#newDeptCode',
		deptName = '#deptName',
        form = $('form');
		
	var oldDeptName = $(deptName).val();
		
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Submitting...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/departments';
				} else {
					form[0].reset();
					$('#isEdited').val('0');
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$(deptCode).focus();
	
	$(deptCode).live('focusout', function (e) {
		/*var value = parseInt(this.value, 5);
		if (isNaN(value)) {
			value = '';
		}*/
		this.value = this.value.replace(/^0+/, ''); //remove leading zeros
    });
	
	$(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Confirm', 'confirm', function () {
                form.submit();
            })
        }
    });
	
	$(backBtn).click(function () {
		window.location.hash = 'maintenance/departments';
		return false;
	});
	
	$(deptName).change(function() {
		if (oldDeptName !== this.value) {
			$('#isEdited').val('1');
		} else {
			$('#isEdited').val('0');
		}
	});
});
</script>