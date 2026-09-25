<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<form id="accountInfoForm" method="post">
    <div id="accountInfo">
        <h1>Account Information</h1>
        <div id="content"><span class="hint">Enter Account Number then press Verify to Search</span><span class="hint floatRight"><span class="red">*</span> - Required Fields</span>
            <table width="100%">
            	<tr>
                	<td width="140"><label for="accntNo">Account Number: <span class="red">*</span></label></td>
                    <td><input type="text" name="accntNo" id="accntNo" style="width:180px" placeholder="<?php echo $acctNoPlaceholder; ?>" class="validate[required]" autofocus/><button id="verifyBtn" value="accounts/info/verify">Verify</button></td>
                </tr>
                <tr>
                    <td><label for="accntType">Account Type:</label></td>
                    <td><select id="accntType" style="width: 192px">
                            <?php echo html_entity_decode($accountTypes); ?>
                        </select></td>
                </tr>
                <tr>
                	<td colspan="2"><div class="divider"></div></td>
                </tr>
                <tr>
                    <td>Branch:</td>
                    <td><input type="text" id="branchx" style="width: 250px" readonly/></td>
                </tr>
                <tr>
                    <td>Authorization Mode:</td>
                    <td><input type="text" id="authMode" style="width: 250px" readonly/></td>
                </tr>
                <tr>
                    <td>Account Status:</td>
                    <td><input type="text" id="accntStatus" style="width: 250px" readonly/></td>
                </tr>
                <tr>
                    <td>Account Owner:</td>
                    <td><input type="text" id="accntOwner" style="width: 250px" readonly/></td>
                </tr>
            </table>
        </div>
        <table class="dataTable">
            <thead>
                <tr>
                    <th>Card No.</th>
                    <th>CIF No.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="newBtn">New Account</button>
        </span>
        <span class="buttons floatRight">
            <button type="reset">Reset</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<style>
.dataTables_scrollBody{min-height:50px !important;max-height:150px !important;}
</style>
<script>
$(function () {
    var verifyBtn = '#verifyBtn',
        newBtn = '#newBtn',
        resetBtn = 'button:reset',
		accountNo = '#accntNo',
        form = $('form');
		
    //$(DATATABLE).find('tbody tr').die('dblclick');
	$(accountNo).focus();
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        'bRetrieve': true,
        'bJQueryUI': true,
        'aaSorting': [],
        'sScrollY': '100%',
        'sScrollX': '100%',
        'sPaginationType': 'full_numbers'
    });
    $('.dataTables_length').html('<strong>Cards Linked:</strong>').css({
		'font-size': '12px',
		'margin-top': '4px'
	});
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Verifying Account Number...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.verified === true) {
                $(MSGBOX).dialog('close');
				
                $(accountNo).attr('readonly', true);
				
				$('#branchx').val(data.branch);
                $('#authMode').val(data.authMode);
                $('#accntStatus').val(data.accntStats);
                $('#accntOwner').val(data.accntOwn);
				
                $(verifyBtn + ',#accntType').attr('disabled', true);
				
                $.fn.dataTableExt.iApiIndex = 0;
                oTable.fnClearTable(0);
                oTable.fnAddData(data.cardLink);
                oTable.fnDraw();
                oTable.fnAdjustColumnSizing();
            } else {
                messageBox('Account number does not exist');
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(accountNo).val('').focus();
				});
            }
            $(WRAPPER).height('auto')
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(newBtn).click(function () {
        window.location.hash = 'accounts/newentry';
        return false;
    });
	
    $(resetBtn).click(function () {
        $(verifyBtn + ',#accntType').removeAttr('disabled');
        $(accountNo).val('').focus().removeAttr('readonly');
		
        oTable.fnClearTable(0);
        oTable.fnDraw();
    });
	
	/*$(account).focusout(function () {
		if (this.value !== '') {
			this.value = zeroPad($(this).attr('maxlength'), this.value);
		}
	});*/
	
	$.mask.definitions = {
		'P': '[0-9]',
		'S': '[0-9]',
		'C': '[0-9]',
		'X': '<?php echo $XFormat; ?>'
	};
	
	$(accountNo).mask('<?php echo $mask; ?>');
	
	$('#accntType').change(function () {
		var selected = $(this).find('option:selected');
		var mask = selected.attr('mask');
		var xChar = selected.attr('xchar');
		var placeholder = selected.attr('inputph');
		
		$(accountNo)
			.mask(mask)
			.attr('placeholder', placeholder);
		$.mask.definitions['X'] = xChar;
	});
});
</script>