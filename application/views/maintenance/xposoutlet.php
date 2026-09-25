<form id="posForm" method="post">
    <div id="posNew" style="width:450px">
        <input type="hidden" name="outletseqno" id="outletseqno" value="<?php echo isset($outletseqno) ? $outletseqno : NULL; ?>"/>
        <h1><?php echo $header; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td colspan="2"><span class="floatRight">All fields are required</span></td>
                </tr>
                <tr>
                    <td width="250"><label for="outletid">Outlet ID:</label></td>
                    <td width="210"><input type="text" name="outletid" id="outletid" style="width:200px" value="<?php echo isset($outletid) ? $outletid : NULL; ?>" class="validate[required] numbersOnly" maxlength="8" <?php echo isset($instid) ? 'readOnly' : ''; ?> autofocus/></td>
                    
                </tr>
                <tr>
                    <td width="250"><label for="outletname">Outlet Name:</label></td>
                    <td width="210"><input type="text" name="outletname" id="outletname" style="width:200px" value="<?php echo isset($outletname) ? $outletname : NULL; ?>" class="validate[required,custom[onlyLetterNumberSp]] alphaNum" maxlength="30"/></td>
                    
                </tr>
                <tr>
                    <td><label for="institutions">Partner Institution:</label></td>
                    <td colspan="3"><select name="institutions" id="institutions" style="width:212px" class="validate[required]">
                            <?php echo html_entity_decode($institutions); ?>
                        </select></td>
                </tr>  
            </table>

        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="<?php echo $submitBtnVal; ?>">Submit</button
            ><button type="reset">Reset</button>
		</span><span class="buttons floatRight">
            <button id="backBtn">Back</button
            ><button class="closebtn">Close</button>
		</span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        backBtn = '#backBtn',
        form = $('form');
		
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('<?php echo $waitMsg; ?>')
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/xposoutletlist'
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');

	
    $(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Warning', 'confirm', function () {
                form.submit()
            })
        }
    });
	
    $(backBtn).click(function () {
        window.location.hash = 'maintenance/xposoutletlist';
        return false
    });
	
});
</script>