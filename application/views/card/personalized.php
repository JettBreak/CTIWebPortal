<form id="cardPersonalizedForm" method="post">
    <input type="hidden" name="custID" value="<?php echo $cifseqno; ?>"/>
    <input type="hidden" name="acctDesc" id="acctDesc" value="<?php echo $acctDesc; ?>"/>
    <div style="width:430px">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
            	<tr>
                	<td colspan="2" height="30">
                    	<span class="floatRight">All fields required</span>
                    </td>
                </tr>
                <tr>
                    <td width="180"><label for="custName">Customer Name:</label></td>
                    <td><input type="text" name="custName" id="custName" style="width:200px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                	<td><label for="branchx">Branch:</label></td>
                    <td>
                    	<select name="branchx" id="branchx" style="width:212px">
                            <?php echo html_entity_decode($branches); ?>
                        </select>
					</td>
                </tr>
                <tr>
                    <td><label for="cardBIN">Card BIN:</label></td>
                    <td><select name="cardBIN" id="cardBIN" style="width:212px">
                            <?php echo html_entity_decode($cardBIN); ?>
                        </select></td>
                </tr>
                <?php echo html_entity_decode($productCodes); ?>
                <tr>
                    <td><label for="embossName">Card Emboss Name:</label></td>
                    <td><input type="text" name="embossName" id="embossName" style="width:200px" class="validate[required]" maxlength="25" value="<?php echo $embossName; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="card_type">Requested Card Type:</label></td>
                    <td><select name="cardType" id="cardType" style="width:212px">
                            <?php echo html_entity_decode($cardType); ?>
                        </select></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="<?php echo $formAction; ?>"><?php echo $label; ?></button>
        </span>
        <span class="buttons floatRight">
            <button type="reset">Clear</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        resetBtn = 'button:reset',
        form = $('form');
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('<?php echo $waitMsg; ?>');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'card/orderrequest';
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(resetBtn).click(function () {
        form.validationEngine('hideAll');
        $('#card_emboss').focus();
    });
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $confirmMsg; ?>', 'Confirm', 'confirm', function () {
                $(MSGBOX).dialog('close');
                showUserOverride();
            });
        }
		e.preventDefault();
    });
	
	$('#cardType').change(function () {
		$('#acctDesc').val($(this).find('option:selected').text());
	});
});
</script>