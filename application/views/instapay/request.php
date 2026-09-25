<form id="InstaPayForm" method="post">
	<div id="InstaPayTranEntry">
		<h1>Instapay Request</h1>
		<div id="content">


      <table class="divider" width="100%">
        <tr>
          <td width="180"><label for="senderAcctNo">Sender Account Number:<span class="red">*</span></label></td>
          <td colspan="3"><input type="text" id="senderAcctNo" name="senderAcctNo" style="width:250px" maxlength="19" class="numbersOnly validate[required]"/></td>
        </tr>
        <tr>
          <td width="180"><label for="senderAcctType">Account Type:<span class="red">*</span></label></td>
          <td>
            <select name="senderAcctType" id="senderAcctType" style="width:212px">
              <option value="10">Savings</option>
              <option value="20">Current</option>
            </select>
          </td>
        </tr>
        <tr>
          <td width="180"><label for="senderAcctName">Sender Name:<span class="red">*</span></label></td>
          <td><input type="text" id="senderAcctName" name="senderAcctName" style="width:250px" maxlength="30" class="customLetterNumberSp validate[required,custom[onlyLetterNumberSpCustom]]"/></td>
        </tr>
        <tr>
          <td width="180"><label for="senderAddress">Address:<span class="red">*</span></label></td>
          <td><textarea style="width:250px" id="senderAddress" name="senderAddress" maxlength="40" class="validate[required]"></textarea></td>
        </tr>
      </table>

			<table class="divider" width="100%">
        <tr>
          <td width="180"><label for="recptAcctNo">Recipient Account Number:<span class="red">*</span></label></td>
          <td><input type="text" id="recptAcctNo" name="recptAcctNo" style="width:250px" maxlength="19" class="numbersOnly validate[required]"/></td>
        </tr>
        <tr>
          <td width="180"><label for="recptAcctName">Recipient Name:<span class="red">*</span></label></td>
          <td><input type="text" id="recptAcctName" name="recptAcctName" style="width:250px" maxlength="30" class="customLetterNumberSp validate[required,custom[onlyLetterNumberSpCustom]]" /></td>
        </tr>
        <tr>
          <td width="180"><label for="recptAcctBank">Recipient Bank:<span class="red">*</span></label></td>
          <td>
            <select name="recptBankCode">
              <?php echo html_entity_decode($recptBankList); ?>
            </select>
          </td>
        </tr>
        
			</table>

      <table class="divider" width="100%">
       <tr>
          <td width="180"><label for="tranAmount">Transaction Amount:<span class="red">*</span></label></td>
          <td><input type="text" id="tranAmount" name="tranAmount" value="0.00" maxlength="12" style="width:250px" class="currencyOnly validate[required, funcCall[checkTransactionAmtMax]]"/></td>
        </tr>
        <tr>
          <td width="180"><label for="recptPurpose">Purpose:<span class="red">*</span></label></td>
          <td><textarea id="recptPurpose" name="recptPurpose" style="width:250px" maxlength="125" class="validate[required]"></textarea></td>
        </tr>
      </table>

			
		</div>
	</div>

	<div id="bottom">
  	<span class="buttons floatLeft">
    	<button id="submitBtn" value="instapay/request/submit">Submit</button>
    </span>
    <span class="buttons floatRight">
        <button type="reset">Clear</button>
        <button class="closebtn">Close</button>
    </span>
	</div>

</form>


<script>
$(function() {

  var submitBtn = '#submitBtn';

  var form = $('form');

  initSession('<?php echo $sessionExp; ?>');
  ipayUserValidation('<?php echo $isHeadOffice; ?>');
  
  form.validationEngine({
    ajaxFormValidation: true,
    onBeforeAjaxFormValidation: function() {
      waitMessage('Sending Request...');
    },
    onAjaxFormComplete: function(form, status, data, options) {

      messageBox(data.message);


      $(MSGBOX).one('dialogbeforeclose', function () {
        //redirect

        if (data.success) {
          var form = $('form');
          form[0].reset();
          // $(cardNo).focus();
        } else {
          //$(cardNo).focus();
        }
        window.location.reload();
      });
    },
    scroll: false
  });
  
  form.validationEngine('attach');

  $(submitBtn).click(function (e) {
    if (form.validationEngine('validate') === true) {
      messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
        // form.submit();
        showUserOverride();
      });
    }
    e.preventDefault();
  });

});

function checkTransactionAmtMax() {
  var transactionAmt = $('#tranAmount').val();
  var tranmax = '50,000.00';
  
  if ( tranmax !== 'N/A' ) {
    
    console.log(tranmax);
    console.log(transactionAmt);

    transactionAmt = parseFloat(transactionAmt.replace(/,/gi, ''));
    tranmax = parseFloat(tranmax.replace(/,/gi, ''));

    if(transactionAmt == 0){
      return 'Transaction Amount must be greater than zero';
    } else if ((tranmax < transactionAmt) ) {
      return 'Maximum Transaction Amount must be less than <br />or equal to Maximum Transaction Amount';
    } else {
      $('#tranAmount').validationEngine('hidePrompt');
    }
    
  }
}

$('#senderAddress').focusout(function () {
  if ($(this).val().trim() == '') {
    $(this).val('');
  }
});

</script>