<form id="overrideForm" method="post" action="override/submit">
    <input type="hidden" name="overridePW" id="overridePW"/>
    <table>
        <tr>
            <td width="120"><label for="overrideUID">Override User ID:</label></td>
            <td><input type="text" name="overrideUID" id="overrideUID" style="width: 150px" maxlength="20" class="validate[required] alphaNumNoSp"/></td>
        </tr>
        <tr>
            <td><label for="overridePW">Password:</label></td>
            <td><input type="password" id="overridePassword" style="width: 150px" maxlength="20" class="validate[required]"/></td>
        </tr>
    </table>
</form>
<style>
.formError, .formErrorArrow {
	z-index: 9999;
}
#modalDialog td {
	vertical-align: middle;
	padding:2px 0 !important;
}
</style>
<script>
$(function () {
	var oUID = '#overrideUID',
		oPW = '#overridePassword',
		oPWx = '#overridePW',
		oForm = '#overrideForm';
	
    $(oUID).focus();
    
    $(oForm).validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Validating login...');
			$(DIALOG).dialog('close');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
                $('form').not(oForm).submit();
            } else {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', showUserOverride);
            }
        },
        scroll: false
    });
    $(oForm).validationEngine('attach');
	
    $(oPW).bind('change keyup', function () {
        if (this.value === '') {
            $(oPWx).val('')
        } else {
            $(oPWx).val(coreencrypt(this.value));
        }
    });
	
    $(oUID + ',' + oPW).keyup(function (e) {
        if ((e.keyCode === 13) && ($(oForm).validationEngine('validate') === true)) {	
            $('.ui-dialog').find('button:first').trigger('click');
        }
    })
});</script>