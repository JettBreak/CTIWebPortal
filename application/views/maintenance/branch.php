<form id="branchForm" method="post">
    <input type="hidden" name="isEdited" id="isEdited" value="0"/>
    <?php echo html_entity_decode($hiddenInput); ?>
    <div id="brch">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
            	<tr>
                	<td colspan="2">
                    	<span class="hint floatRight"><span class="red">*</span> - Required Fields</span>
                    </td>
                </tr>
                <tr>
                    <td width="120"><label for="brchid">Branch ID: <span class="red">*</span></label></td>
                    <td width="270"><input name="brchid" id="brchid" style="width:100px" class="validate[required,custom[number]] numbersOnly" maxlength="4" value="<?php echo $brchid; ?>" autofocus/>
                    </td>
                </tr>
                <tr>
                    <td><label for="brchcode">Branch Code: <span class="red">*</span></label></td>
                    <td><input name="brchcode" id="brchcode" style="width:100px" class="validate[required,custom[number]] numbersOnly" maxlength="4" value="<?php echo $brchcode; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="brchname">Branch Name: <span class="red">*</span></label></td>
                    <td><input name="brchname" id="brchname" style="width:250px" class="validate[required] upperCase" maxlength="50"  value="<?php echo $brchname; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="areaname">Area Name: <span class="red">*</span></label></td>
                    <td>
                    	<select id="areaname" name="areaname" style="width:262px" class="validate[required] upperCase">
                            <?php echo html_entity_decode($areaname); ?>
                        </select>
					</td>
                </tr>
                <tr>
                    <td style="vertical-align:top !important"><label for="address">Address:</label></td>
                    <td><textarea name="address" id="address" style="width:250px;height:50px" maxlength="100" class="validate[maxSize[100]]"><?php echo $address; ?></textarea></td>
                </tr>
                <tr>
                    <td><label for="telno">Telephone No.:</label></td>
                    <td><input name="telno" id="telno" style="width:250px" class="validate[custom[number]] numbersOnly" maxlength="25" value="<?php echo $telno; ?>"/></td>
                </tr>
                <tr>
                	<td colspan="2">
                    Other options:
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input name="headoffice" id="headoffice" type="checkbox" <?php echo $checked; ?>/>
                        <label for="headoffice">Head Office</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2" class="ho">
                    	<input name="isRep" id="isRep" type="checkbox" <?php echo $isRep; ?>/>
                        <label for="isRep"<?php echo $isRepLabel; ?>>Can generate reports from other branches</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2" class="ho">
                    	<input name="isMon" id="isMon" type="checkbox" <?php echo $isMon; ?>/>
                        <label for="isMon"<?php echo $isMonLabel; ?>>Can monitor other branches</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2" class="ho">
                    	<input name="isUser" id="isUser" type="checkbox" <?php echo $isUser; ?>/>
                        <label for="isUser"<?php echo $isUserLabel; ?>>Can modify users from other branches</label>
                    </td>
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
<style>
label[disabled] {
	color: #aaa;
}
input[disabled] {
	border: 1px solid #000;
}
input[type="checkbox"] {
	position: relative;
	top: 2px;
}
</style>
<script>
$(function () {
    var submitBtn = '#submitBtn',
		backBtn = '#backBtn',
        form = $('form');

    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Creating new branch entry...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/brchlist';
				} else {
					form[0].reset();
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Confirm', 'confirm', function () {
                form.submit();
            })
        }
    });
	
	$(backBtn).click(function () {
		window.location.hash = 'maintenance/brchlist';
		return false;
	});
	
	$('#headoffice').change(function () {
		if ($(this).is(':checked')) {
			$('.ho input:checkbox').attr({
				checked: true,
				disabled: true
			});
			$('.ho label').attr({
				disabled: true
			});
		} else {
			$('.ho input:checkbox').attr({
				checked: false,
				disabled: false
			});
			$('.ho label').attr({
				disabled: false
			});
		}
	});
});
</script>