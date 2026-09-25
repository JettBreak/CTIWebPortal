<form id="setPWForm" method="post">
    <input type="hidden" name="secretAnswer" id="secretA"/>
    <input type="hidden" name="newPassword" id="newPW"/>
    <div id="setPW">
        <h1>Set Password</h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - All fields required </span>
            <table width="100%">
                <tr>
                    <td width="200"><label for="secretQuestion">Secret Question: <span class="red">*</span></label></td>
                    <td><select name="secretQuestion" id="secretQuestion" style="width:252px">
                            <option>What is your mother's middle name?</option>
                            <option>What was the name of your first school?</option>
                            <option>Who was your childhood hero?</option>
                            <option>Where did you first meet your spouse?</option>
                            <option>What is your pet's name?</option>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="secretAnswer">Secret Answer: <span class="red">*</span></label></td>
                    <td><input type="text" id="secretAnswer" style="width:240px" class="validate[required,minSize[6]]" maxlength="20" autocomplete="off"/></td>
                </tr>
                <tr>
                    <td style="vertical-align:top !important"><label for="newPassword">New Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="newPassword" style="width:240px" class="validate[required,minSize[<?php echo $minChar; ?>]]" maxlength="20" autocomplete="off"/></td>
                </tr>
                <tr>
                    <td><label for="confirmPassword">Confirm Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="confirmPassword" style="width:240px" class="validate[required,equals[newPassword]" maxlength="20" autocomplete="off"/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button value="setpw/submit" id="submitBtn" disabled>Submit</button>
        </span>
        <span class="buttons floatRight">
            <button id="resetBtn" type="reset">Clear</button
            ><button id="backBtn">Back</button>
        </span></div>
</form>
<script>
$(function () {
	$('#secretAnswer').focus();
	
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
			messageBox(data.message);
			if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'login';
				});
			}
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $('#newPassword').pstrength();
	
	$('#secretAnswer').bind('change keyup', function () {
        if (this.value === '') {
            $('#secretA').val('');
        } else {
            $('#secretA').val(coreencrypt(this.value));
        }
    });
	
    $('#newPassword').bind('change keyup', function () {
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
            })
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