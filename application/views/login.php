<div id="login">
    <form id="loginForm" method="post">
        <input type="hidden" name="userPW" id="userPW"/>
        <h1>Welcome!</h1>
        <div id="content">
            <table width="100%">
            	<tr>
                	<td colspan="2" align="center" height="110"><a href="<?php echo $siteURL; ?>" target="_blank"><img src="images/<?php echo $folder; ?>logo.png" alt="<?php echo $bankName; ?>" title="<?php echo $bankName; ?>" class="logo"/></a></td>
                </tr>
                <tr>
                    <td width="120"><label for="userID">User ID:</label></td>
                    <td><input type="text" name="userID" id="userID" maxlength="16" style="width:180px" class="validate[required] alphaNumNoSp"/></td>
                </tr>
                <tr>
                    <td width="120"><label for="userPW">Password:</label></td>
                    <td><input type="password" id="userPassword" maxlength="20" style="width:180px" class="validate[required]" autocomplete="off"/></td>
                </tr>
            </table>
        </div>
        <div id="bottom">
        	<span class="buttons">
            	<button value="login/submit">Login</button>
            </span>
            <span class="buttons floatRight">
            	<button value="forgot" id="forgotBtn">Forgot password?</button>
            </span>
		</div>
    </form>
</div>
<script>
$(function () {
	$('#userID').focus();
	
    var forgotBtn = '#forgotBtn',
        form = $('form');
		
    $('#userPassword').bind('change keyup', function () {
        if (this.value === '') {
            $('#userPW').val('')
        } else {
            $('#userPW').val(coreencrypt(this.value))
        }
    });
	
    $('#userID').keyup(function (e) {
		//if enter key pressed
        if (this.value !== '' && e.keyCode === 13) {
            $('#userPassword').focus();
        }
        e.preventDefault()
    });
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Establishing connection to server...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            $('#userPW').val('');
			
            if (data.success) {
                $(MSGBOX).dialog('close');
				//$(MSGBOX).dialog('option', {
					//close: function () {			
						$('body').prepend(data.div);
						
						
						
						/*$('#instName').html(data.instName);
						$('#address').html('<strong>Address: </strong>' + data.address);
						$('#branch').html('<strong>Branch: </strong>' + data.branch);
						$('#userName').html('<strong>Username: </strong>' + data.userName);
						$('#lastLogin').html(data.lastLogin);
						$('#currentDT').html('<strong>Current Date: </strong>' + data.currentDT);
						$('#topRight').html('<a href="logout" id="logout">Logout</a>');*/
							
						/*preload([
							'images/bg.png',
							'images/black.png',
							'images/ebank/form.png',
							'images/ebank/purple.png',
							//'images/grey.png',
							'images/ebank/panel.png'
							//'images/nullphoto.jpg',
						]);*/
						
						window.location.hash = 'welcome';
						
						$('#xInfo').html(data.header);
						autoAdjustSection();
						$.getScript('js/menu.js');
						initFloatMenu();
							
						if (data.isTeller) {
							$('#tellerMenu').fadeIn();
						}
						
						
						
						initSession(data.sessionExp);
					//}
				//});
            } else {
                messageBox(data.message);
				//messageBox(data.heading);
				$(MSGBOX).one('dialogbeforeclose', function () {
					if (data.isDefine === true) {
						window.location.hash = 'setpw';
					} else if (data.isReset === true) {
						window.location.hash = 'setpw2';
					} else {
						form[0].reset();
						$('#userID').focus();
					}
				});
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(forgotBtn).click(function () {
        window.location.hash = 'forgotpw';
        return false;
    });
	
    preload([
		'images/connect.gif'
	]);
});
</script>