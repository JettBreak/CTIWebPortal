<form id="cardReplace" method="post">
    <input type="hidden" name="cifseqno" value="<?php echo $cifseqno; ?>"/>
    <input type="hidden" name="prseqno" id="prseqno" value="<?php echo $prseqno; ?>"/>
    <input type="hidden" name="pseqnoLink" id="pseqnoLink" value=""/>
    <div id="cardReplace">
        <h1>Card Replacement Request</h1>
        <div id="content">
            <table>
                <tr>
                    <td width="170">Card Number:</td>
                    <td><input type="text" id="oldCardNo" name="oldCardNo" style="width:290px" value="<?php echo $cardNo; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Customer Name:</td>
                    <td><input type="text" name="custName" style="width:290px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Card Status:</td>
                    <td><input type="text" name="cardStatus" style="width:290px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Card Type:</td>
                    <td><input type="text" style="width:290px" value="<?php echo $cardType; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Primary Account:</td>
                    <td><input type="text" style="width:290px" value="<?php echo $primary; ?>" readonly/></td>
                </tr>
            </table>
            <div class="divider"></div>
            <table>
                <tr>
                    <td width="170"><label for="cardNo">Card Number: <span class="red">*</span></label></td>
                    <td><select name="cardBIN" id="cardBIN" style="width:80px">
                        <?php echo html_entity_decode($cardBIN); ?>
                    </select
                    ><input type="text" name="cardNo" id="cardNo" style="width:120px" class="validate[required,custom[onlyNumberSp]]" placeholder="<?php echo $cardNoPlaceholder; ?>"/>
                    <button id="verifyBtn" value="card/replacement/verify">Verify</button>
                    </td>
                </tr>
                <tr>
                    <td><label for="newCardType">New Card Type:</label></td>
                    <td><input type="text" name="newCardType" id="newCardType" style="width:200px" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cboReason">Reason for Replacement: <span class="red">*</span></label></td>
                    <td><select name="cboReason" id="cboReason" style="width:212px" disabled>
                            <option value="15">LOST</option>
                            <option value="8">STOLEN</option>
                            <option value="16">DAMAGED</option>
                        </select></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom"><span class="buttons floatLeft">
        <button id="submitBtn" value="card/replacement/replace" disabled>Submit</button>
        </span><span class="buttons floatRight">
        <button type="Reset">Reset</button>
        <button class="closebtn">Close</button>
        </span></div>
</form>
<script>
$(function () {
    var verifyBtn = '#verifyBtn',
        submitBtn = '#submitBtn',
        resetBtn = 'button[type="reset"]',
        form = $('form');
		
	var cardBIN = '#cardBIN';
	var cardNo = '#cardNo';
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            $('#newCardType').val('');
            var msg = null;
            if ($('#pseqnoLink').val() === '') {
                msg = 'Verifying card number...';
            } else {
                msg = 'Sending transaction...';
            }
            waitMessage(msg);
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.verified === true) {
				
                $(MSGBOX).dialog('close');
                $('#newCardType').val(data.cardType);
                $('#pseqnoLink').val(data.pseqnoLink);
                $('#cboReason').removeAttr('disabled');
                $(submitBtn).removeAttr('disabled');
                $(verifyBtn).attr('disabled', true);
				
            } else if (data.verified === false) {
				
                messageBox(data.message);
                $(MSGBOX).one('dialogbeforeclose', function () {
					$(cardNo)
						.val('')
						.focus();
				});
				
            }
			
            if (data.success === true) {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'card/verify/replace';
				});
            } else if (data.success === false) {
                messageBox(data.message);
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(verifyBtn).click(function (e) {
       
        var cardNumber = $('#cardBIN').val() + $('#cardNo').val();
        if ($('#oldCardNo').val() === cardNumber) {
			
            messageBox('Card number must not be the same');
			
			$(MSGBOX).one('dialogbeforeclose', function () {
				$(cardNo)
					.val('')
					.focus();
			});
				    
        } else {
            form.attr('action', $(verifyBtn).val());
            form.submit();
        }
		 e.preventDefault();
    });
	
    $(resetBtn).click(function () {
        $(cardNo).focus();
        $('#newCardType, #pseqnoLink').val('');
        $('#cboReason').attr('disabled', true);
        $(submitBtn).attr('disabled', true);
        $(verifyBtn).removeAttr('disabled')
    });
	
    $(submitBtn).click(function (e) {
        messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
            $(MSGBOX).dialog('close');
            showUserOverride();
        });
        e.preventDefault()
    });
	
	$(cardBIN).change(function () {		
		var selected = $(cardBIN).find('option:selected');
		var format = selected.attr('format');
		
		var brChar = 'B';
		var cnt = (format.split(brChar).length - 1);
		var rep = brChar.repeat(cnt);
		
		var br = zeroPad(cnt, $(branch).val());
		
		var mask = format.replace(rep, br);
		var placeholder = mask.replace(/N/g, '_');
		
		$(cardNo)
			.val('')
			.mask(mask)
			.attr('placeholder', placeholder);
	});
	
	$.mask.definitions = {
		'N': '[0-9]'
	};
	
	$(cardNo).mask('<?php echo $cardNoMask; ?>');
});
</script>