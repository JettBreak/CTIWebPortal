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
	padding: 3px 10px;
}
span[disabled] {
	color: #aaa;
}
</style>
<form id="userTemplatesForm" method="post">
	<input name="tmseqno" type="hidden" value="<?php echo $tmseqno; ?>"/>
    <!--<input type="hidden" id="oldTempName" value=""/>
    <input type="hidden" id="oldGroup" value=""/>
    <input type="hidden" name="change" id="change" value="0"/>-->
    <div id="userTemplate">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td><label for="templateName">Template Name: <span class="red">*</span></label></td>
                    <td>
                    	<input type="text" name="newTemplateName" id="newTemplateName" style="width:200px" class="validate[required,custom[onlyLetterNumberSp]] alphaNum" value="<?php echo $templateName; ?>" maxlength="40"<?php echo $controlsAttr; ?>/>
					</td>
                </tr>
                <tr>
                    <td><label for="grpseqno">User Group: <span class="red">*</span></label></td>
                    <td>
                        <select name="grpseqno" id="grpseqno" class="validate[required]" style="width:212px"<?php echo $controlsAttr; ?>>
                            <?php echo html_entity_decode($userGroup); ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div id="checkBoxControl" class="controller<?php echo $visibility; ?>">
        	<div class="controls">
            	<a title="Checks the entire tree below" href="#" id="checkAll">Check All</a> |
                <a title="Unchecks the entire tree below" href="#" id="unCheckAll">Uncheck All</a> |
                <a title="Toggle the checkboxes below" href="#" id="toggleCheck">Toggle Check</a><!-- |
                <a title="Inverts the current selected checkboxes" href="#" id="invert">Invert Selection</a>-->
			</div>
        </div>
        <div id="treeView" style="border-top: 1px solid #333; border-bottom: 1px solid #333;">
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
    <div id="bottom">
        <span class="buttons floatLeft">
        	<button id="saveBtn" value="<?php echo $formAction; ?>"<?php echo $controlsAttr; ?>>Save</button
            >
        </span>
        <span class="buttons floatRight">
        	<!--<button type="reset" id="resetBtn">Reset</button
            >--><button id="backBtn">Back</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function() {
	var saveBtn = '#saveBtn',
		resetBtn = '#resetBtn',
		updateBtn = '#updateBtn',
		backBtn = '#backBtn',
		treeView = '#navMenu',
		treeControl = '#treeControl',
		newTempName = '#newTemplateName',
		grpseqno = '#grpseqno',
		userGroup = '#userGroup',
		chk = $(treeView).find(':checkbox'),
		form = $('form'),
		oldTempName = '#oldTempName',
		oldGroup = '#oldGroup',
		change = '#change';
	
    initSession('<?php echo $sessionExp; ?>');
	$(treeView).treeview({
		animated: 'fast',
		control: treeControl,
		toggle: function() {

		}
	});
	
	$(newTempName).focus();
	
	form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('<?php echo $waitMsg; ?>');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			messageBox(data.message);
			if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'security/templates';
				});
			} else {
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(newTempName).focus();
				});
			}
		},
		scroll: false
	});
	form.validationEngine('attach');
	
	/*$(cboTemplate).change(function() {
		var selected = $(this).find('option:selected');
		$(tmseqno).val(selected.attr('tmseqno'));
		$(userGroup).val(selected.attr('grpdesc'));
		//getTempAllows(this.value);
		popTemplate(selected.attr('allows'));
	});*/
	
	/*function popTemplate(allows) {
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
	}*/
	
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
	
	$('#invert').click(function(e) {
		chk.each(function() {
			$(this).attr('checked', !$(this).attr('checked'));
		});
		e.preventDefault();
	});
	
	$('input').change(function (e) {
		$(MSGBOX).dialog('close');
	});
				
	$(saveBtn).click(function(e) {
		var checked = $(treeView).find('input:checked').length;
		var isValidChk = checked > 0 ? true : false;
		var isValidInput = form.validationEngine('validate');
		if (isValidInput) {
			if (isValidChk) {
				messageBox('Are all entries correct?','Confirm','confirm',function() {			
					form.submit();
				});
			} else {
				messageBox('At least one module must be checked');
			}
		}
		e.preventDefault();
	});
	
	$(updateBtn).click(function(e) {
		if (form.validationEngine('validate') === true) {
			messageBox('Update users using "'+$(cboTemplate+' option:selected').text()+'" template?','Confirm','confirm',function(){
				waitMessage('Updating users...');
				form.submit();
			});
		}
		e.preventDefault();
	});
	
	$(backBtn).click(function () {
		window.location.hash = 'security/templates';
		return false
	});
	
	
	
	/*$(grpseqno).change(function () {
		var forbidden = $(this).find('option:selected').attr('forbidden');
		if (forbidden) {;
			menuindex = forbidden.split(',');
	
			$('#treeView input[type="checkbox"]').removeAttr('disabled');
			$.each(menuindex, function(index, value) {
				$('#'+ value +',#'+ value +' ~ span').attr('disabled', true);
			});
		} else {
			$('#treeView input[type="checkbox"]').removeAttr('disabled');
		}
	});*/
	
	//if changes occur set var change to 1
	/*$(newTempName).change(function() {
		if ($(oldTempName).val() !== this.value) {
			$(change).val('1');
		} else {
			$(change).val('0');
		}
	});
	
	$(grpseqno).change(function() {
		if ($(oldGroup).val() !== this.value) {
			$(change).val('1');
		} else {
			$(change).val('0');
		}
	});*/
	//end
	
	<?php echo html_entity_decode($script); ?>
});
</script>