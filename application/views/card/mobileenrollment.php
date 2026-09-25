<form id="mobileEnrollmentForm" method="post">
    <input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
    <input type="hidden" name="cellseqno" value="<?php echo $cellseqno; ?>"/>
    <div id="mobileEnrollment">
        <h1>Mobile Enrollment</h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="120"><label for="cardBIN">Card Number:</label></td>
                    <td><input type="text" name="cardBIN" id="cardBIN" style="width:240px" value="<?php echo $cardBIN; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="custName">Customer Name:</label></td>
                    <td><input type="text" id="custName" style="width:240px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cardStatus">Card Status:</label></td>
                    <td><input type="text" id="cardStatus" style="width:240px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cardType">Card Type:</label></td>
                    <td><input type="text" id="cardType" style="width:240px" value="<?php echo $cardType; ?>" readonly/></td>
                </tr>
            </table>
            <table width="100%" class="divider">
                <tr>
                    <td width="120"><label for="mobileNo">Mobile Number:</label></td>
                    <td><input type="text" name="mobileNo" id="mobileNo" style="width:240px" value="<?php echo $mobileNo; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="mobileStatus">Mobile Status:</label></td>
                    <td><input type="text" id="mobileStatus" style="width:240px" value="<?php echo $mobileStats; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="lastTranx">Last Transaction:</label></td>
                    <td><input type="text" id="lastTranx" style="width:150px" value="<?php echo $lastTranx; ?>" readonly/>
                        <input type="text" id="cellxml" style="width:74px" value="<?php echo $cellxml; ?>" readonly/></td>
                </tr>
                <tr id="mobileInfo">
                	<td colspan="2"<?php echo html_entity_decode($visibility1); ?>>
                    	Allowed Transactions:
                        <div style="height:100px;" class="box" id="allowedTrx">
                        <?php echo html_entity_decode($tranAllows);?>
                        </div>
                    </td>
                </tr>
			</table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="removeBtn" value="card/mobileenrollment/remove"<?php echo html_entity_decode($visibility1); ?>>Remove Mobile Link</button
            ><button id="addBtn"<?php echo html_entity_decode($visibility2); ?>>Add Mobile Link</button>
        </span>
        <span class="buttons floatRight">
        	<button type="reset">Reset</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<style>
label[disabled] {
	color: #aaa;
}
input[disabled], textarea[disabled] {
	border: 1px solid #000;
}
input[type="checkbox"] {
	position: relative;
	top: 2px;
}
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<script>
$(function () {
    var addBtn = '#addBtn',
        removeBtn = '#removeBtn',
        form = $('form');
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            if ($(removeBtn).length) {
                waitMessage('Removing linked mobile number...')
            }
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
            if (data.success === true) {
                $('#mobileNo').val('NONE');
				$('#mobileStatus').val('N/A');
                $('.buttons:first button').toggle();
				$('#mobileInfo').remove();
				bind();
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
    bind();

    function bind() {
        $(addBtn).click(function () {
            window.location.hash = 'card/enrollmobile';
            return false;
        });
    }
    if ($(removeBtn).length) {
        $(removeBtn).click(function (e) {
            $mobileNo = $('#mobileNo').val();
            e.preventDefault();
            messageBox('Do you want to remove mobile link<br/><strong>[' + $mobileNo + ']</strong> from this card?', 'Confirm', 'confirm', function () {
                $(MSGBOX).dialog('close');
                showUserOverride();
            });
        });
    }
	
	/*$('input:checkbox').change(function (e) {
		e.preventDefault();
	});*/
});
</script>