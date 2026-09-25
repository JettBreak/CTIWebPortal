<form id="batchOrderForm" method="post">
	<input type="hidden" name="acctDesc" id="acctDesc" value="<?php echo $acctDesc; ?>"/>
    <div id="batchOrder">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
            	<tr>
                	<td colspan="2" class="hint">
                    Enter Number of Cards to Request and Press Submit<span style="margin-left:20px"><span class="red">*</span> - Required Fields </span>
                    </td>
                </tr>
                <tr>
                	<td width="190"><label for="branchx">Branch:</label></td>
                    <td>
                    	<select name="branchx" id="branchx" style="width:200px">
                            <?php echo html_entity_decode($branches); ?>
                        </select>
					</td>
                </tr>
                <tr>
                    <td><label for="cardBIN">Card BIN:</label></td>
                    <td>
                    	<select name="cardBIN" id="cardBIN" style="width:200px">
                            <?php echo html_entity_decode($cardBIN); ?>
                        </select>
					</td>
                </tr>
                <?php echo html_entity_decode($productCodes); ?>
                <tr>
                    <td><label for="cardCount">No. of Cards to Request: <span class="red">*</span></label></td>
                    <td><input type="number" name="cardCount" id="cardCount" style="width:188px" class="validate[required,custom[integer],funcCall[checkInput]] integersOnly" value="<?php echo $reqQty; ?>" maxlength="4" step="1" min="1" max="9999"/></td>
                </tr>
                <tr>
                    <td><label for="cardType">Requested Card Type:</label></td>
                    <td>
                    	<select name="cardType" id="cardType" style="width:200px">
                            <?php echo html_entity_decode($cardType); ?>
                        </select>
					</td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="<?php echo $formAction; ?>"><?php echo $label; ?></button>
        </span>
        <span class="buttons floatRight">
            <button type="reset">Reset</button
            ><button id="backBtn">Back</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
		backBtn = '#backBtn',
        form = $('form');
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('<?php echo $waitMsg; ?>');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				window.location.hash = 'card/orderrequest';
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $confirmMsg; ?>', 'Confirm', 'confirm', function () {
                $(MSGBOX).dialog('close');
                showUserOverride();
            });
        }
		e.preventDefault();
    });
	
	$(backBtn).click(function () {
		window.location.hash = 'card/orderrequest';
		return false;
	});
	
	/*$('#cardCount').focusout(function (e) {
		var maxInput = 1000;
		var minInput = 1;
		var val = parseInt(this.value);
		
		if (val < minInput || this.value === '') {
			return this.value = minInput;
		}
		
		if (val > maxInput) {
			return this.value = maxInput;
		}
	});*/
	
	$('#cardType').change(function () {
		$('#acctDesc').val($(this).find('option:selected').text());
	});
});

//min 1 max 1000
function checkInput(field, rules, i, options) {
	var maxInput = field.attr('max');
	var minInput = field.attr('min');
	var val = parseInt(field.val());
	
	if (val < minInput || val > maxInput) {
		return '* Must be between ' + minInput + ' and ' + maxInput;
	}
}
</script>