<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<form id="newEntryForm" method="post">
<input type="hidden" name="cifseqno" id="cifseqno" value="<?php echo $custid; ?>"/>
    <div style="width:750px">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                	<td colspan="3">&nbsp;</td>
                	<td rowspan="7" width="300">
                    	Allowed Transactions:
                        <div style="height:190px;" class="box" id="allowedTrx">
                            <?php echo html_entity_decode($tranAllows); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                	<td width="140"><label for="accntNo">Account Number: <span class="red">*</span></label></td>
                    <td><input type="text" name="accntNo" id="accntNo" style="width:200px" value="<?php echo $acctnoxml; ?>" placeholder="<?php echo $acctNoPlaceholder; ?>" class="validate[required]"/></td>

                </tr>
                <tr>
                    <td><label for="accntType">Account Type:</label></td>
                    <td><select name="accntType" id="accntType" style="width:212px">
                            <?php echo html_entity_decode($accountTypes); ?>
                        </select></td>
                </tr>
                <tr>
                	<td><label for="branch">Branch:</label></td>
                    <td>
                    	<select id="branch" name="branch" style="width:212px">
                            <?php echo html_entity_decode($branches); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="custName">Customer Name:</label></td>
                    <td>
                    	<input type="text" id="custName" value="<?php echo $custname; ?>" style="width:250px;cursor:pointer" readonly/>
                    </td>
                </tr>
                <tr>
                    <td><label for="authModex">Authorization Mode:</label></td>
                    <td><input type="text" id="authModex" style="width:200px" value="ONLINE" readonly/></td>
                </tr>
                <tr>
                    <td><label for="accntStatus">Account Status:</label></td>
                    <td><input type="text" id="accntStatus" style="width:200px" value="<?php echo $acctStatusDesc; ?>" readonly/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="<?php echo $formAction; ?>">Submit</button
            ><button id="browseBtn">Browse</button>
            <?php echo html_entity_decode($uploadBtn); ?>
		</span>
		<span class="buttons floatRight">
            <button type="reset">Reset</button
            ><button class="closebtn">Close</button>
		</span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn';
    var resetBtn = 'button:reset';
    var batchBtn = '#uploadBtn';
	var accountNo = '#accntNo';
	var form = $('form');
	var chk = $('#allowedTrx').find(':checkbox');
	
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Creating Account...')
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success) {
					window.location.hash = 'accounts/search';
            	} else {
                    if (data.expired) {
                        logOut();
                    } else {
                        $(resetBtn).trigger('click');
                    }
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$('#browseBtn').click(function (e) {
		window.location.hash = 'accounts/browse';
		e.preventDefault();
	});
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
				$(MSGBOX).dialog('close');
                showUserOverride();
            })
        }
        e.preventDefault();
    });
	
    $(resetBtn).click(function () {
        $(accountNo).focus().val('');
    });
	
	$.mask.definitions = {
		'P': '[0-9]',
		'S': '[0-9]',
		'C': '[0-9]',
		'X': '<?php echo $XFormat; ?>'
	};

    $(batchBtn).click(function () {
        window.location.hash = 'accounts/batchupload';
        return false;
    });
	
	$(accountNo).focus().mask('<?php echo $mask; ?>');
	
	$('#accntType').change(function () {
		var selected = $(this).find('option:selected');
		var mask = selected.attr('mask');
		var xChar = selected.attr('xchar');
		var placeholder = selected.attr('inputph');
		var allows = selected.attr('defaultallows');
		
		$(accountNo)
			.mask(mask)
			.attr('placeholder', placeholder);
		$.mask.definitions['X'] = xChar;
		
		popAllows(allows);
	});
	
	$('#custName').bind('click', function () {
		$(this).attr('disabled');
		$(this).validationEngine('hidePrompt');
		searchCustomer();
		return false;
	});
	
	function popAllows(allows) {
		allows = 'x' + allows;
		chk.removeAttr('checked');
		for (i=1; i <= allows.length; i++) {
			var a = allows.substring(i,i+1);
			var b = $('#bit'+i);
			
			if (a==='1') {
				b.attr('checked', 'checked');
			} else {
				b.removeAttr('checked');
			}
		}
	}
});
</script>