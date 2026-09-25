<form id="userGroupForm" method="post">
    <div id="userGroup">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                	<td width="100"><label for="groupName">Group Name: <span class="red">*</span></label></td>
                    <td><input type="text" name="groupName" id="groupName" style="width: 200px" class="validate[required] alphaNum" value="<?php echo $groupName; ?>" maxlength="50"/></td>
                </tr>
                <tr>
                	<td colspan="2" height="30">Security Options:</td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="securityOpt[0]" id="securityOpt0" <?php echo $secOpt0Chk; ?>/>
                        <label for="securityOpt0">Display the date and time of the last succesful login and no. of login failed.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="securityOpt[1]" id="securityOpt1" <?php echo $secOpt1Chk; ?>/>
                        <label for="securityOpt1">User profile can be enabled/disabled.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="securityOpt[2]" id="securityOpt2" <?php echo $secOpt2Chk; ?>/>
                        <label for="securityOpt2">User expiration is </label><input type="number" name="userExpiry" id="userExpiry" style="width:45px" class="numbersOnly" step="1" min="90" max="9999" value="<?php echo $userExpiry; ?>" <?php echo $secOpt2Attr; ?>/><label for="securityOpt2"> days</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="checkbox" name="securityOpt[3]" id="securityOpt3" <?php echo $secOpt3Chk; ?>/>
                        <label for="securityOpt3">User expiration due to inactivity </label><input type="number" name="userInactive" id="userInactive" style="width:45px" class="numbersOnly" step="1" min="1" max="<?php echo $userExpiry; ?>" value="<?php echo $userInactive; ?>" <?php echo $secOpt3Attr; ?>/><label for="securityOpt3"> days</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="securityOpt[7]" id="securityOpt7" <?php echo $secOpt7Chk; ?>/>
                        <label for="securityOpt7">Session will expire in </label><input type="number" name="sessionExp" id="sessionExp" style="width:45px" class="numbersOnly" step="1" min="1" max="360" value="<?php echo $sessionExpx; ?>" <?php echo $secOpt7Attr; ?>/><label for="securityOpt7"> minutes</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2" height="30">
                    	<label for="maxRetry">Maximum no. of consecutive failed login attempts: </label><input type="number" id="maxRetry" name="maxRetry" style="width:25px" class="numbersOnly" step="1" min="1" max="6" value="<?php echo $maxRety;?>"/>
                    </td>
                </tr>
                <tr>
                	<td colspan="2" height="30">Password Options:</td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="passOpt[0]" id="passOpt0" <?php echo $passOpt0Chk; ?>/>
                        <label for="passOpt0">Password must be minimum of </label><input type="number" name="minChar" id="minChar" style="width:25px" class="numbersOnly" step="1" min="8" max="20" value="<?php echo $minChar; ?>" <?php echo $passOpt0Attr; ?>/><label for="passOpt0"> characters.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="passOpt[1]" id="passOpt1" <?php echo $passOpt1Chk; ?>/>
                        <label for="passOpt1">Password expiration is </label><input type="number" name="passExpiry" id="passExpiry" style="width:45px" class="numbersOnly" step="1" min="<?php echo $passExpiryMin; ?>" max="9999" value="<?php echo $passExpiry; ?>" <?php echo $passOpt1Attr; ?>/><label for="passOpt1"> days.</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="checkbox" name="passOpt[6]" id="passOpt6" <?php echo $passOpt6Chk; ?>/>
                        <label for="passOpt6">Password recycle after </label><input type="number" name="passCycle" id="passCycle" style="width:45px" class="numbersOnly" step="1" min="5" max="99" value="<?php echo $passRecycle; ?>" <?php echo $passOpt6Attr; ?>/><label for="passOpt6"> times.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="passOpt[2]" id="passOpt2" <?php echo $passOpt2Chk; ?>/>
                        <label for="passOpt2">Upon resetting the password the user must change it upon logon.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="passOpt[3]" id="passOpt3" <?php echo $passOpt3Chk; ?>/>
                        <label for="passOpt3">Prompt user to change password upon password expiry.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="passOpt[4]" id="passOpt4" disabled <?php /*echo $passOpt4Chk;*/ ?>/>
                        <label for="passOpt4">User ID will be used as system default password for new users.</label>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<input type="checkbox" name="passOpt[5]" id="passOpt5" disabled <?php /*echo $passOpt5Chk;*/ ?>/>
                        <label for="passOpt5">User ID will be used as system default password upon resetting password.</label>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button value="<?php echo $formAction; ?>" id="saveBtn">Save</button>
        </span>
        <span class="buttons floatRight">
        	<button id="backBtn">Back</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<style>
input[type="checkbox"] {
	position: relative;
	top: 2px;
}
input[step] {
	padding: 3px;
	font-size: 11px;
}
</style>
<script>
$(function () {
	var form = $('form');
	var saveBtn = '#saveBtn';
	var backBtn = '#backBtn';
	var groupName = '#groupName';
	
	$(groupName).focus();
	
	/*$('input[step]').keypress(function () {
		return false;
	});*/
	
    initSession('<?php echo $sessionExp; ?>');
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('<?php echo $waitMsg; ?>');
		},
		onAjaxFormComplete: function (form, status, data, options) {
			messageBox(data.message);
			if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'security/usergroups';
				});
			} else {
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(saveBtn).attr('disabled', false);
					$(groupName).val('').focus();
				});
			}
		},
		scroll: false
	});
	form.validationEngine('attach');
	
	$(saveBtn).click(function (e) {
		if (form.validationEngine('validate') === true) {
			messageBox('Are all entries correct?', 'Confirm', 'confirm', function() {
				$(saveBtn).attr('disabled', true);
				form.submit();
			});
		}
		e.preventDefault();
	});
	 
	$(backBtn).click(function (e) {
		window.location.hash = 'security/usergroups';
		e.preventDefault();
	});
	
	$('#securityOpt2').change(function () {
		var isDisabled = $(this).is(':checked') ? false : true;
		$('#userExpiry').attr('disabled', isDisabled);
	});

    $('#securityOpt3').change(function () {
        var isDisabled = $(this).is(':checked') ? false : true;
        $('#userInactive').attr('disabled', isDisabled);
    });
	
	$('#securityOpt7').change(function () {
		var isDisabled = $(this).is(':checked') ? false : true;
		$('#sessionExp').attr('disabled', isDisabled);
	});
	
	$('#passOpt0').change(function () {
		var isDisabled = $(this).is(':checked') ? false : true;
		$('#minChar').attr('disabled', isDisabled);
	});

    $('#passOpt6').change(function () {
        var isDisabled = $(this).is(':checked') ? false : true;
        $('#passCycle').attr('disabled', isDisabled);

        if (isDisabled) {
            $('#passCycle').val(0);
        } else {
            $('#passCycle').val(<?php echo isset($passRecycle) ? ($passRecycle == 0 ? 5 : $passRecycle ) : 5 ; ?>);
        }
    });
	
	$('#passOpt1').change(function () {
		var isDisabled = $(this).is(':checked') ? false : true;
		$('#passExpiry').attr('disabled', isDisabled);
	});
});
</script>