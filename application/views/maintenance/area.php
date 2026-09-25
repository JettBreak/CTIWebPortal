<form id="areaForm" method="post">
<input type="hidden" name="isEdited" id="isEdited" value="0"/>
<?php echo html_entity_decode($hiddenInput); ?>
    <div id="area">
        <h1><?php echo $title; ?></h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - Required Fields</span>
            <table width="100%">
                <tr>
                    <td width="100"><label for="areaCode">Area Code: <span class="red">*</span></label></td>
                    <td><input name="areaCode" id="areaCode" style="width:100px" class="validate[required,custom[integer]] numbersOnly" maxlength="3" value="<?php echo $areacode; ?>" autofocus/></td>
                </tr>
                <tr>
                    <td><label for="areaName">Area Name: <span class="red">*</span></label></td>
                    <td><input name="areaName" id="areaName" style="width:250px" class="validate[required,custom[onlyLetterNumberSp]] alphaNum upperCase" value="<?php echo $areaname; ?>" maxlength="30"/></td>
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
		areaName = '#areaName',
		areaCode = '#areaCode',
        form = $('form');
		
	var oldAreaName = $(areaName).val();
		
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Creating new area entry...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/arealist';
				} else {
					form[0].reset();
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$(areaCode).focus();
	
	$(areaCode).live('focusout', function (e) {
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
		window.location.hash = 'maintenance/arealist';
		return false;
	});
	
	$(areaName).change(function() {
		if (oldAreaName !== this.value) {
			$('#isEdited').val('1');
		} else {
			$('#isEdited').val('0');
		}
	});
});
</script>