<form id="setPWForm" method="post">
    <input type="hidden" name="newPassword" id="newPW"/>
    <div id="setPW2">
        <h1>Set Password</h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - All fields required </span>
            <table width="100%">
                <tr>
                    <td style="vertical-align:top !important"><label for="newPassword">New Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="newPassword" style="width:150px" class="validate[required,minSize[<?php echo $minChar; ?>]]" maxlength="20" autocomplete="off"/></td>
                </tr>
                <tr>
                    <td><label for="confirmPassword">Confirm Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="confirmPassword" style="width:150px" class="validate[required,equals[newPassword]" maxlength="20" autocomplete="off"/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button value="setpw2/submit" id="submitBtn" disabled>Submit</button>
        </span>
        <span class="buttons floatRight">
            <button id="resetBtn" type="reset">Clear</button
            ><button id="backBtn">Back</button>
        </span></div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        resetBtn = '#resetBtn',
        backBtn = '#backBtn',
        form = $('form');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Updating User Information...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            
			if (data.success === true) {
                messageBox(data.message);
				
                $(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'login';
				});

			} else if (data.success === false) {
                messageBox(data.message);
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $('#newPassword').pstrength();
	
    $('#newPassword').focus().bind('change keyup', function () {
        if (this.value === '') {
            $('#newPW').val('');
        } else {
            $('#newPW').val(coreencrypt(this.value));
            $('#submitBtn').attr('disabled', $('.pstrength-bar').width() < 127);
        }
    });
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
                form.submit();
            });
        }
        e.preventDefault();
    });
	
    $(resetBtn).click(function () {
        form.validationEngine('hideAll');
        $('.pstrength-bar, .pstrength-info').hide();
    });
	
    $(backBtn).click(function () {
        window.location.hash = 'login';
        return false
    })
});
</script>