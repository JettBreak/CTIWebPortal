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
        </table>
      </div>
    </div>
    <div id="bottom"><span class="buttons floatLeft">
        <button id="submitBtn" value="instapay/changeuserpwd/submit" disabled>Submit</button>
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
      onBeforeAjaxFormValidation: function() {
        waitMessage('Updating user password...')
      },
      onAjaxFormComplete: function(form, status, data, options) {

        messageBox(data.message);

        $(MSGBOX).one('dialogbeforeclose', function () {
          //redirect

          if (data.success) {
            var form = $('form');
            form[0].reset();
            // $(cardNo).focus();
          } else {
            //$(cardNo).focus();
          }
          window.location.reload();
        });
      },
      scroll: false
    });
    form.validationEngine('attach');
  
    $('#newPassword').pstrength();
  
    $('#newPassword').bind('change keyup', function () {
        if (this.value === '') {
            $('#newPW').val('');
        } else {
          $('#newPW').val(this.value);
          $('#submitBtn').attr('disabled', $('.pstrength-bar').width() < 127);
          input2 = $('.pstrength-bar').width();
        }
    });
  
    $('#currentPassword').bind('change keyup', function () {
        if (this.value === '') {
          $('#currentPW').val('');
        } else {
          $('#currentPW').val(this.value);
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
        url: 'instapay/changeuserpwd/verify',
        data: 'currentPW=' + $('#currentPassword').val(),
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