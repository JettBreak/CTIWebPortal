<form id="setPWForm" method="post">
    <input type="hidden" name="secretAnswer" id="secretA"/>
    <input type="hidden" name="newPassword" id="newPW"/>
    <div id="setPW">
        <h1>Forgot Password</h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - All fields required </span>
            <table width="100%">
                <tr>
                    <td width="200"><label for="forgottenID">User ID: <span class="red">*</span></label></td>
                    <td><input type="text" name="forgottenID" id="forgottenID" style="width: 240px" class="validate[required] alphaNumNoSp" maxlength="16"/></td>
                </tr>
                <tr class="x hidden">
                    <td><label for="secretQuestion">Secret Question: <span class="red">*</span></label></td>
                    <td><input type="text" name="secretQuestion" id="secretQuestion" style="width: 240px" readonly/></td>
                </tr>
                <tr class="x hidden">
                    <td><label for="secretAnswer">Secret Answer: <span class="red">*</span></label></td>
                    <td><input type="password" id="secretAnswer" style="width: 240px" class="validate[required]" maxlength="20" autocomplete="off"/></td>
                </tr>
                <tr class="y hidden">
                    <td style="vertical-align:top !important"><label for="newPassword">New Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="newPassword" style="width: 240px" maxlength="20" autocomplete="off"/></td>
                </tr>
                <tr class="y hidden">
                    <td><label for="confirmPassword">Confirm Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="confirmPassword" style="width: 240px" maxlength="20" autocomplete="off"/></td>
                </tr><!-- 
                <tr class="y hidden">
                    <td><label for="confirmPassword">Confirm Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="confirmPassword" style="width: 240px" class="validate[required,equals[newPassword]" maxlength="20" autocomplete="off"/></td>
                </tr> -->
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button value="forgotpw/verify" id="submitBtn">Verify</button>
        </span>
        <span class="buttons floatRight">
            <button id="resetBtn" type="reset">Clear</button
            ><button id="backBtn">Back</button>
        </span>
	</div>
</form>
<script>
$(function () {
	//init vars
    var submitBtn = '#submitBtn',
        resetBtn = '#resetBtn',
        backBtn = '#backBtn',
        newPassword = '#newPassword',
        newPW = '#newPW',
        secretAnswer = '#secretAnswer',
        secretA = '#secretA',
		forgottenID = '#forgottenID',
        form = $('form');
	//end
	
	$(forgottenID).focus();
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            if ($(submitBtn).val() === 'forgotpw/verify') {
                var msg = 'Verifying User ID...'
            } else {
                var msg = 'Updating User Password...'
            }
            waitMessage(msg);
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.verified === true) {
				
                $(MSGBOX).dialog('close');
                $(forgottenID).attr('readonly', true);
				$(newPassword).addClass('validate[required,minSize[' + data.minchar + ']]');
                $('#secretQuestion').val(data.question);
                $(submitBtn).val('forgotpw/submit').text('Submit');
                //$(submitBtn).attr('disabled', true);
                $('.x').show();
				$(secretAnswer).focus();
				
            } else if (data.verified === false) {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					form[0].reset();
					$(forgottenID).focus();
				});
            }
			
            if (data.success === true) {
				messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'login';
				});
				
            } else if (data.success === false) {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(resetBtn).trigger('click');
					$(secretAnswer).focus();
				});
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(newPassword).pstrength();
	
    $(newPassword).bind('change keyup', function () {
        if (this.value === '') {
            $(newPW).val('')
        } else {
            $(newPW).val(coreencrypt(this.value));
            $('#submitBtn').attr('disabled', $('.pstrength-bar').width() < 127);
        }
    });
	
    $(secretAnswer).bind('change keyup', function () {
        if (this.value === '') {
            $(secretA).val('')
        } else {
            $(secretA).val(coreencrypt(this.value))
        }
    });
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            if ($(submitBtn).val() === 'forgotpw/submit') {
                messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
                    form.submit();
                });
            } else {
                return true;
            }
        }
        e.preventDefault();
    });
	
    $(resetBtn).click(function (e) {
        form.validationEngine('hideAll');
        $('.pstrength-bar, .pstrength-info').hide();
        $(secretAnswer + ',' + newPassword + ',' + forgottenID + ':not([readonly=readonly]),#confirmPassword').val('');
		e.preventDefault();
    });
	
    $(backBtn).click(function () {
        window.location.hash = 'login';
        return false;
    });
});
</script>