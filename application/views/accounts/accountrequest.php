<form id="accountRequestForm" method="post">
    <div id="searchCustomerForm">
        <h1>Search Customer</h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="140"><label for="custID">CIF Number: <span class="red">*</span></label></td>
                    <td><input type="text" name="custID" id="custID" style="width:200px" maxlength="15" class="validate[required] numbersOnly"/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="accounts/accountrequest/submit">Search</button>
        </span>
        <span class="buttons floatRight">
            <button type="reset">Reset</button>
            <button class="closebtn">Close</button>
        </span>
    </div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        resetBtn = 'button:reset',
        customerID = '#custID',
        form = $('form');
    initSession('<?php echo $sessionExp; ?>');
        
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Searching Customer...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
                //redirect
                //$(MSGBOX).dialog('close');
                //window.location.hash = 'customer/edit';
                //window.location.hash = 'customer/requestCustomer';
                searchRedirect($(customerID).val());
            } else {
                messageBox(data.message);
                $(MSGBOX).one('dialogafterclose', function () {
                    $(resetBtn).trigger('click');
                });
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
    
    $(resetBtn).click(function () {
        $(customerID).focus().val('');
    });
});
function searchRedirect(cifseqno) {
    var type = 'newacct',
        page = null;
    switch (type) {
    case 'newacct':
        page1    = 'accounts/newentry/';
        pagemany = 'accounts/accountresultlist/';
        break;
    }
    $.ajax({
        type: 'POST',
        url: 'accounts/accountrequest/cache',
        data: {
            'cifseqno': cifseqno
        },
        dataType: 'json',
        success: function (data) {
            if (data.success === true) {
                if (data.many === false) {
                    window.location.hash = page1;
                } else {
                    window.location.hash = pagemany;
                }
            } else {
                messageBox(data.message);
                $(MSGBOX).one('dialogafterclose', function () {
                    $(resetBtn).trigger('click');
                });
                //messageBox('An error has occured');
            }
        }
    })
}
</script>