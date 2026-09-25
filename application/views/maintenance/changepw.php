<form id="changepwForm" method="post">
    <input type="hidden" name="currentPassword" id="currentPW"/>
    <input type="hidden" name="newPassword" id="newPW"/>
    <div id="changepw">
        <h1>Change Password</h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - Required Fields</span>
            <table width="100%">
                <tr>
                    <td><label for="currentPassword">Current Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="currentPassword" style="width:240px" class="validate[required,funcCall[validateCurrentPW]]" maxlength="20"/></td>
                </tr>
                <tr>
                    <td style="vertical-align:top !important"><label for="newPassword">New Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="newPassword" style="width:240px" class="validate[required,funcCall[checkPW],minSize[<?php echo $minChar; ?>]]" maxlength="20"/></td>
                </tr>
                <tr>
                    <td><label for="confirmPassword">Confirm Password: <span class="red">*</span></label></td>
                    <td><input type="password" id="confirmPassword" style="width:240px" class="validate[required,equals[newPassword]" maxlength="20"/></td>
                </tr>
                <tr>
                  <td colspan="2"><div class="divider">In case you forgot your password, please provide a secret question and secret answer to verify your identity.</div></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="200"><label for="secretQuestion">Secret Question:</label></td>
                    <td>
                      <select name="secretQuestion" id="secretQuestion" style="width:252px">
                            <?php echo html_entity_decode($secretQuestions); ?>
                        </select>
          </td>
                </tr>
                <tr>
                  <td><label for="secretAnswer">Secret Answer:</label></td>
                    <td><input name="secretAnswer" id="secretAnswer" style="width:240px" maxlength="20"/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom"><span class="buttons floatLeft">
        <button id="submitBtn" value="maintenance/changepw/submit" disabled>Submit</button>
        </span><span class="buttons floatRight">
        <button id="resetBtn" type="reset">Clear</button>
        <button class="closebtn">Close</button>
        </span></div>
</form>
<script>
var x = false;
$(function () {
    var resetBtn = '#resetBtn',
        form = $('form'),
        input2 = '',
        input = $("<input>");
    
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Updating user password...')
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
                messageBox(data.message+'. Please relogin.');
                $(MSGBOX).one('dialogbeforeclose', function () {
                    logOut();
                });
                //$(resetBtn).trigger('click');
                //x = false;
            } else if (data.success === false) {
                messageBox(data.message);
                $(MSGBOX).one('dialogbeforeclose', function () {
                  if (data.status) {
                    logOut();
                  }
                });
            } else if (data['void'] === true) {
                window.location.hash = 'login';
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
  
    $('#newPassword').pstrength();
  
    $('#newPassword').bind('change keyup', function () {
        if (this.value === '') {
            $('#newPW').val('');
        } else {
            $('#newPW').val(coreencrypt(this.value));
            $('#submitBtn').attr('disabled', $('.pstrength-bar').width() < 127);
            input2 = $('.pstrength-bar').width();
        }
    });
  
    $('#currentPassword').bind('change keyup', function () {
        if (this.value === '') {
            $('#currentPW').val('');
        } else {
            $('#currentPW').val(coreencrypt(this.value));
        }
    });
  
    $('#submitBtn').click(function (e) {
      if (x) {
        if (form.validationEngine('validate') === true) {
            messageBox('Update current password?', 'Confirm', 'confirm', function () {
                input = $("<input>")
                   .attr("type", "hidden")
                   .attr("name", "certificate").val(input2);
                $('#changepwForm').append($(input));
                form.submit()
            });
        }
      } else {
        messageBox('Invalid current password.');
      }
      e.preventDefault();
    });
  
    $(resetBtn).click(function () {
        form.validationEngine('hideAll');
        $('.pstrength-bar, .pstrength-info').hide();
    });
  
    $('#currentPassword').focus(function () {
        x = false;
    })
});

function checkPW(input, c, i, d) {
  if (input.val() === $('#currentPassword').val()) {
    return '* Your new password must differ from your old one';
  } else {
    input.validationEngine('hidePrompt');
  }
}

function validateCurrentPW(input, c, i, d) {
    if (x === false) {
        var e = $.ajax({
            type: 'POST',
            url: 'maintenance/changepw/verify',
            data: 'currentPW=' + $('#currentPW').val(),
            async: false,
            success: function (data) {
                if (data === '0') {} else {
                    $('#currentPassword').validationEngine('showPrompt', '* Password verified', 'pass', 'topRight', true);
                    x = true;
                }
            }
        }).responseText;
        if (e === '0') {
            if (input.val() !== '') {
                return '* Invalid password';
            }
        }
    }
}
</script>