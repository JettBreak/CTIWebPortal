<style>
.category {
	text-transform:uppercase;
}
.controller {
	width: 100%;
	height: 100%;
	background: #333;
}
.controls {
	padding: 3px 20px;
}
span[disabled] {
	color: #aaa;
}
</style>
<form id="userEnrollForm" method="post">
<input type="hidden" name="update" id="update" value="1"<?php echo html_entity_decode($isUpdate); ?>/>
<input type="hidden" id="userIDOld" value="<?php echo $userIDValue; ?>"/>
<input type="hidden" id="groupname" name="groupname" value=""/>
<div id="userEnroll">
	<h1><?php echo $title; ?></h1>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabAllows">Restrictions</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content"> 
            <table width="100%">
                <tr>
                    <td width="170"><label for="userID">User ID: <span class="red">*</span></label></td>
                    <td><input type="text" name="userID" id="userID" style="width:250px" value="<?php echo $userIDValue; ?>" class="validate[required,minSize[3]] alphaNumNoSp" maxlength="16" <?php echo $userIDAttr; ?>/></td>
                </tr>
                <tr>
                    <td><label for="userName">User Name: <span class="red">*</span></label></td>
                    <td><input type="text" name="userName" id="userName" style="width:250px" value="<?php echo $userNameValue; ?>" class="validate[required] lettersOnly properCase" maxlength="40"/></td>
                </tr>
                <tr<?php echo html_entity_decode($dtCreatedVisibility); ?>>
                	<td><label for="dtCreated">Date Created:</label></td>
                    <td><input type="text" id="dtCreated" style="width:250px" value="<?php echo $dtCreated; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="grpseqno">User Group: <span class="red">*</span></label></td>
                    <td><?php echo html_entity_decode($userGroups); ?></td>
                </tr>
                <tr>
                    <td><label for="userStatus">Status:</label></td>
                    <td><input type="text" id="userStatus" style="width:250px" value="<?php echo $userStatus; ?>"readonly/></td>
                </tr>
                <tr>
                    <td><label for="branch">Branch: <span class="red">*</span></label></td>
                    <td>
                    	<select id="branch" name="branch" style="width:262px" class="validate[required]">
                            <?php echo html_entity_decode($branches); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="position">Position:</label></td>
                    <td><input type="text" name="position" id="position" style="width:250px" value="<?php echo $userPosition; ?>" class="lettersOnly properCase"/></td>
                </tr>
                <tr>
                    <td><label for="department">Department: <span class="red">*</span></label></td>
                    <td>
                        <select id="department" name="department" style="width:262px" class="validate[required]">
                            <?php echo html_entity_decode($departments); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="emailAddr">Email: <span class="red">*</span></label></td>
                    <td><input type="text" name="emailAddr" id="emailAddr" style="width:250px" value="<?php echo $emailAddr; ?>" class="validate[custom[email]]" maxlength="40"/></td>
                </tr>
                
                <tfoot id="setPass"<?php echo html_entity_decode($setPassAttr); ?>>
                    <tr>
                        <td colspan="2">&nbsp;<input type="hidden" name="secretAnswer" id="secretA"/>
    <input type="hidden" name="newPassword" id="newPW"/></td>
                    </tr>
                    <tr>
                        <td width="200"><label for="secretQuestion">Secret Question: <span class="red">*</span></label></td>
                        <td><select name="secretQuestion" id="secretQuestion" style="width:262px">
                                <option>What is your mother's middle name?</option>
                                <option>What was the name of your first school?</option>
                                <option>Who was your childhood hero?</option>
                                <option>Where did you first meet your spouse?</option>
                                <option>What is your pet's name?</option>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="secretAnswer">Secret Answer: <span class="red">*</span></label></td>
                        <td><input type="text" id="secretAnswer" style="width:250px" class="validate[required,minSize[6]]" maxlength="20" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top !important"><label for="newPassword">New Password: <span class="red">*</span></label></td>
                        <td><input type="password" id="newPassword" style="width:250px" class="validate[required,minSize[<?php echo $minChar; ?>]]" maxlength="20" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td><label for="confirmPassword">Confirm Password: <span class="red">*</span></label></td>
                        <td><input type="password" id="confirmPassword" style="width:250px" class="validate[required,equals[newPassword]" maxlength="20" autocomplete="off"/></td>
                    </tr>
                </tfoot>
            </table>
		</div>
        <div id="tabAllows" class="tab_content nopadding">
        	<div id="checkBoxControl" class="controller">
            	<table style="margin:2px 0 0 15px">
                	<tr>
                        <td><label for="tmseqno">Template:</label></td>
                        <td>
                            <select id="tmseqno" name="tmseqno" style="width:262px">
                                <?php echo html_entity_decode($templates); ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="controls">
                    <a title="Checks the entire tree below" href="#" id="checkAll">Check All</a> |
                    <a title="Unchecks the entire tree below" href="#" id="unCheckAll">Uncheck All</a> |
                    <a title="Toggle the checkboxes below" href="#" id="toggleCheck">Toggle Check</a><!-- |
                    <a title="Inverts the current selected checkboxes" href="#" id="invert">Invert Selection</a>-->
                </div>
            </div>
        	<div id="treeView">
        		<?php echo html_entity_decode($menuList); ?>
            </div>
            <table id="treeControl" class="controller">
                <tr>
                    <td class="controls">
                        <a title="Collapse the entire tree above" href="#"><img src="images/treeview/minus.gif" /> Collapse All</a> |
                        <a title="Expand the entire tree above" href="#"><img src="images/treeview/plus.gif" /> Expand All</a> |
                        <a title="Toggle the tree above, opening closed branches, closing open branches" href="#">Toggle All</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="createBtn" value="security/users/submit"><?php echo $btnLabel; ?></button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Reset</button
        ><button id="backBtn">Back</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>

<script>
$(function() {	
	var createBtn = '#createBtn',
		backBtn = '#backBtn',
		resetBtn = '#resetBtn',
		cboTemplate = '#tmseqno',
		cboUserGroup = '#grpseqno',
		allows = '#allows',
		treeView = '#treeView',
		treeControl = '#treeControl',
		chk = $(treeView).find(':checkbox');
		
	var	form = $('form');
	
	$(treeView).treeview({
		animated: 'fast',
		control: treeControl,
		toggle: function() {

		}
	});
	
	$('#userID').focus();
	
	//popTemplate('<?php //echo $allows; ?>');
	
    initSession('<?php echo $sessionExp; ?>');
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('<?php echo $waitMsg; ?>');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			messageBox(data.message);
			if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'security/users';
				});
			} else {
				$(MSGBOX).one('dialogbeforeclose', function () {
					$('#userEnrollForm')[0].reset();
					$('#userID').focus();
				});
			}
			
		},
		scroll: false
	});
	form.validationEngine('attach');	
	
	//default Action
	$(TABCONTENT).hide(); //hide all content
	$(TABS).first().addClass('active').show(); //activate first tab
	$(TABCONTENT).first().show(); //show first tab content
	       			
	//onClick Event
	$(TABS).click(function() {
		if (form.validationEngine('validate') === true) {
			if (!$(this).hasClass('active')) {
				form.validationEngine('hideAll');
				$(TABS).removeClass('active'); //remove any "active" class
				$(this).addClass('active'); //add "active" class to selected tab
				$(TABCONTENT).hide(); //hide all tab content
				
				var $activeTab = $(this).find('a').attr('href'); //find the rel attribute value to identify the active tab + content
				$($activeTab).show();
				
				$(WRAPPER).height('auto');
			}
		}
		return false;
	});

	$('#groupname').val($('#grpseqno option:selected').text());
	
	$(cboUserGroup).change(function () {
		var selected = $(this).find('option:selected');
		var minChar = selected.attr('minchar');
		var setPass = selected.attr('setpass');

		if (setPass === undefined) {
			$('#setPass').hide();
		} else {
			$('#setPass').show();
		}

		$('#newPassword').attr('class', 'validate[required,minSize['+ minChar +']]');
		
		var selected = $(cboTemplate).find('option[predefined][grpseqno="' + this.value + '"]');
		selected.attr('selected', true);
		$('#groupname').val($('#grpseqno option:selected').text());

		popTemplate(selected.attr('allows'));
		
		//hide error balloons
		$('#secretAnswer').validationEngine('hidePrompt');
		$('#newPassword').validationEngine('hidePrompt');
		$('#confirmPassword').validationEngine('hidePrompt');
	});
	
	$(cboTemplate).change(function() {
		var selected = $(this).find('option:selected');
		
		$(allows).val(selected.attr('allows'));
		
		popTemplate(selected.attr('allows'));
	});
	
	$(createBtn).click(function(e) {
		var checked = $(treeView).find('input:checked').length;
		var isValidChk = checked > 0 ? true : false;
		var isValidInput = form.validationEngine('validate');
		if (isValidInput) {
			if (isValidChk) {
				messageBox('<?php echo $confirmMsg; ?>','Confirm','confirm',function() {			
					form.submit();
				});
			} else {
				messageBox('At least one module must be checked');
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(TABS).find('a[href="#tabAllows"]').trigger('click');
				});
			}
		}
		e.preventDefault();
	});
	
	$(backBtn).click(function(e) {
		window.location.hash = 'security/users';
		e.preventDefault();
	});
	
	function popTemplate(allows) {
		allows = 'x' + allows;
		chk.removeAttr('checked');
		for (i=1;i<=allows.length+100;i++) {
			var a = allows.substring(i,i+1);
			var b = $('#'+i);
			
			if (a==='1') {
				b.attr('checked', 'checked');
			} else {
				b.removeAttr('checked');
			}
		}
	}
	
	//checkbox controls
	$('#checkAll').click(function(e) {
		chk.not(':disabled').attr('checked', 'checked');
		e.preventDefault();
	});
	
	$('#unCheckAll').click(function(e) {
		chk.removeAttr('checked');
		e.preventDefault();
	});
	
	$('#toggleCheck').click(function(e) {
		chk.not(':disabled').attr('checked', !chk.attr('checked'));
		e.preventDefault();
	});
	
	//check subcategory
	$(treeView + ' input:checkbox.category').change(function() {
		var chk = '.'+this.id+'sub:not(:disabled)';
		
		if ($(this).is(':checked') === true) {
			$(chk).attr('checked', 'checked');
		} else {
			$(chk).removeAttr('checked');
		}
	});
	
	//check parent
	var subcat = 'input:checkbox:not(.category)';
	$(subcat).change(function() {
		
		var y = $(this).attr('class');
		var x = y.replace('sub', '');
		var z = $('#'+ x);
		z.attr('checked', 'checked');
		
		//uncheck parent
		if ($('.'+ y +':checked').length == 0) {
			
			z.removeAttr('checked');
		}
	});
	
	
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
        }
    });
});
</script>