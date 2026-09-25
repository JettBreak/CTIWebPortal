<form id="searchAccountForm" method="post">
    <div id="searchAccount">
        <h1>Search Account</h1>
        <div id="content">
            <table width="100%">                
                <tr>
                    <td><label for="accntType">Account Type:</label></td>
                    <td><select name="accntType" id="accntType" style="width: 212px">
                            <?php echo html_entity_decode($accountTypes); ?>
                        </select></td>
                </tr>
                <tr>
                	<td width="140"><label for="accntNo">Account Number: <span class="red">*</span></label></td>
                    <td><input type="text" name="accntNo" id="accntNo" style="width:200px" placeholder="<?php echo $acctNoPlaceholder; ?>" class="validate[required]"/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="accounts/search2/submit">Search</button
            >
		</span>
		<span class="buttons floatRight">
            <button type="reset">Reset</button
            ><button class="closebtn">Close</button>
		</span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        resetBtn = 'button:reset',
		accountNo = '#accntNo',
        form = $('form');
		
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Searching Account...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
                //redirect
				window.location.hash = 'accounts/changestatus';
            } else {
				messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(resetBtn).trigger('click');
				});
			}
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(resetBtn).click(function () {
        $(accountNo).focus().val('');
    });
	
	$.mask.definitions = {
		'P': '[0-9]',
		'S': '[0-9]',
		'C': '[0-9]',
		'X': '<?php echo $XFormat; ?>'
	};
	
	$(accountNo).focus().mask('<?php echo $mask; ?>');
	
	$('#accntType').change(function () {
		var selected = $(this).find('option:selected');
		var mask = selected.attr('mask');
		var xChar = selected.attr('xchar');
		var placeholder = selected.attr('inputph');
		var accntType = selected.attr('value');
		
		$(accountNo)
			.mask(mask)
			.attr('placeholder', placeholder);
		$.mask.definitions['X'] = xChar;
	});
});
</script>