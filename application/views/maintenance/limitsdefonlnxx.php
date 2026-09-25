<form id="onlnLimitsDefFormxx" method="post" action="maintenance/limitsdefonlnadd/submit">
<input type="hidden" name="accttypex" value="<?php echo $accttype; ?>"/>
<table>
	<tr>
    	<td width="250"><label for="limitname">Limit Name:</label></td>
        <td>
        	<input type="text" name="limitname" id="limitname" style="width:200px" class="validate[required]" maxlength="50"/>
        </td>
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
        <td><label for="pinctrdefx">PIN Retry Count Default:</label></td>
        <td><input type="number" name="pinctrdef" id="pinctrdefx" style="width:60px" value="3" step="1" min="0" max="999" maxlength="3"/></td>
	</tr>
    <tr>
        <td><label for="pinctrmaxx">PIN Maximum Retry Count:</label></td>
        <td><input type="number" name="pinctrmax" id="pinctrmaxx" style="width:60px" value="4" step="1" min="0" max="999" maxlength="3"/></td>
    </tr>
</table>
</form>
<style>
.formError, .formErrorArrow {
	z-index: 9999;
}
#modalDialog td {
	padding:2px 0 !important;
}
td {
	vertical-align: middle;
}
</style>
<script>
$(function () {
	var form = $('#onlnLimitsDefFormxx');
	
	$('#limitname').focus();
	
    initSession('<?php echo $sessionExp; ?>');
	form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Creating new limits...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success) {
					
					$('#limitseqno')
						.find('option[value=""]')
						.remove();
					
					$('#limitseqno')
						.append('<option pinctrdef="' + data.pinctrdef + '" pinctrmax="' + data.pinctrmax + '" value="' + data.limitseqno + '">' + data.desc + '</option>')
						.find('option:first').attr('selected', true)
						.trigger('change');
						
					$(DIALOG).dialog('close');
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$('#pinctrdefx').focusout(function () {
		var maxInput = $('#pinctrmaxx').val();
		var maxInput = parseInt(maxInput.replace(/,/g, ''));
		var minInput = 0;
		var val = parseInt(this.value);
		
		if (val < minInput || this.value === '') {
			return this.value = minInput;
		}
		
		if (val > maxInput) {
			return this.value = maxInput;
		}
	});
});
</script>