<form id="genDefPINForm" action="card/defaultpin/generate" method="post">
<table style="width:100%" id="trxTable">
	<tr>
    	<td width="120">Batch No.:</td>
        <td><input id="batchNo" style="width: 110px" value="<?php echo $batchNo; ?>" readonly/></td>
    </tr>
	<tr>
    	<td width="120">Default PIN:</td>
        <td><input id="defaultPIN" name="defaultPIN" type="password" maxlength="<?php echo $maxPIN; ?>" class="numbersOnly validate[required,minSize[<?php echo $minPIN; ?>]],maxSize[<?php echo $maxPIN; ?>]]" style="width: 110px"/></td>
    </tr>
    <tr>
    	<td width="120">Confirm PIN:</td>
        <td><input id="confirmPIN" type="password" maxlength="<?php echo $maxPIN; ?>" class="numbersOnly validate[required,minSize[<?php echo $minPIN; ?>]],maxSize[<?php echo $maxPIN; ?>],funcCall[checkInput]]" style="width: 110px"/></td>
    </tr>
</table>
</form>
<style>
#modalDialog td {
	padding:2px 0 !important;
}
td {
	vertical-align: middle;
}
.formError, .formErrorArrow {
	z-index: 9999;
}
</style>
<script>
$(function() {
	$('#defaultPIN').focus();
});

function checkInput(field, rules, i, options)
{
	if (field.val() !== $('#defaultPIN').val()) {
		return '* PIN does not match';
	} else {
		$('form').validationEngine('hideAll');
	}
}
</script>