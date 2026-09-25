<form id="InstaPayForm" method="post">
	<div id="InstaPayTranEntry">
		<h1>Instapay Card <?php echo $pageLbl; ?></h1>
		<div id="content">

      <div>
        <strong>Card Information</strong>
        <table width="100%">
          <tr>
            <td width="100"><label for="senderAccntNo">Branch:</label></td>
            <td colspan="3">
              <select name="brseqno" id="brseqno" style="width:212px">
                <?php echo html_entity_decode($branches); ?>
              </select>
            </td>
          </tr>
          
          <?php if(empty($cardNumber)){ ?>
          <tr>
            <td><label for="cardNo">Card No.:</label></td>
            <td>
              <select name="cardBIN" id="cardBIN" style="width:80px">
                <?php echo html_entity_decode($cardBIN); ?>
              </select>
              <input type="text" name="cardNo" id="cardNo" style="width: 114px" class="validate[custom[onlyNumberSp]] numbersOnly" maxlength="7"/></td>
          </tr>

          <?php }else{ ?>

            <tr>
              <td style="width:100px;"><label>Card No.</label></td>
              <td><input type="text" id="cardNo" name="cardNo" style="width:200px" maxlength="30" value="<?php echo $cardNumber; ?>" readonly /></td>
            </tr>

          <?php } ?>

          <tr>
            <td><label for="cardType">Card Type:</label></td>
            <td>
              <input type="hidden" name="prType" id="prType"/>
              <select name="acctType" id="acctType" style="width:212px">
              <?php echo html_entity_decode($cardType); ?>
              </select>
            </td>
          </tr>
          <tr>
              <td><label for="cardStatus">Card Status:</label></td>
              <td><input type="text" name="cardStatus" id="cardStatus" style="width: 200px" value="<?php echo $cardStatusDesc; ?>" readonly/></td>
          </tr>
        </table>
      </div>

      <div class="divider">
        <strong>Merchant Information</strong>
        <table width="100%">
          <tr>
            <td width="100"><label for="merchantID">Merchant ID:<span class="red">*</span></label></td>
            <td><input type="text" name="merchantID" id="merchantID" maxlength="25" style="width: 200px" value="<?php echo $merchantID; ?>" class="validate[required, custom[onlyNumberSp]] numbersOnly"/></td>
          </tr>
          <tr>
            <td width="100"><label for="mallID">Mall ID:<span class="red">*</span></label></td>
            <td><input type="text" name="mallID" id="mallID" maxlength="25" style="width: 200px" value="<?php echo $mallID; ?>" class="validate[required, custom[onlyNumberSp]] numbersOnly"/></td>
          </tr>
        </table>
      </div>

      <?php if(empty($cardNumber)){ ?>
      <div class="divider">
        <strong>User Information</strong>
        <table width="100%">
          <tr>
            <td width="130"><label for="userID">User ID:<span class="red">*</span></label></td>
            <td><input type="text" name="userID" id="userID" maxlength="25" style="width: 170px" value="<?php echo $userID; ?>" class="validate[required]"/></td>
          </tr>
          <tr>
            <td width="130"><label for="userPwd">Password:<span class="red">*</span></label></td>
            <td><input type="password" name="userPwd" id="userPwd" maxlength="15" style="width: 170px" class="validate[required]"/></td>
          </tr>
          <tr>
            <td width="130"><label for="Conf">Confirm Password:<span class="red">*</span></label></td>
            <td><input type="password" name="userConfPwd" id="userConfPwd" maxlength="15" style="width: 170px" class="validate[required,equals[userPwd]]"/></td>
          </tr>
        </table>
      </div>
      <?php } ?>

		</div>
	</div>

	<div id="bottom">
  	<span class="buttons floatLeft">
    	<button id="saveBtn" value="<?php echo $pageUrl; ?>">Save</button>
    </span>
    <span class="buttons floatRight">
        <button type="reset">Reset</button>
        <button class="closebtn">Close</button>
    </span>
	</div>

</form>

<script>
$(function() {

  var saveBtn = '#saveBtn';

  var branch = '#brseqno';
  var cardBIN = '#cardBIN';
  var cardNo = '#cardNo';
  var cardType = '#acctType';

  var form = $('form');

  initSession('<?php echo $sessionExp; ?>');
  ipayUserValidation('<?php echo $isHeadOffice; ?>');

  form.validationEngine({
    ajaxFormValidation: true,
    onBeforeAjaxFormValidation: function() {
      <?php if(!empty($cardNumber)){ ?>
        waitMessage('Updating IPay Card...');
      <?php }else{ ?>
        waitMessage('Enrolling IPay Card...');
      <?php } ?>
    },
    onAjaxFormComplete: function(form, status, data, options) {

      messageBox(data.message);
      $(MSGBOX).one('dialogbeforeclose', function () {
        
        if (data.success) {
          var form = $('form');
          form[0].reset();
          $(cardNo).focus();
        }
        window.location.reload();
      });
    },
    scroll: false
  });

  form.validationEngine('attach');

  $(saveBtn).click(function (e) {
    e.preventDefault();

    if (form.validationEngine('validate') === true) {
      messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
        form.submit();
      });
    }
  });

  <?php if(empty($cardNumber)){?>

  $(branch + ',' + cardBIN + ',' + cardType).change(function () {   
    var selected = $('#acctType').find('option:selected');
    var format = selected.attr('format');
    var weight = selected.attr('weights');
    var prType = selected.attr('prtype');
    var cbin = $(cardBIN).val();
    
    var brChar = 'B';
    var cnt = format.split(brChar).length - 1;
    var rep = brChar.repeat(cnt);
    var brCode = $(branch).find('option:selected').attr('brcode');
    var br = '';
    var mask = '';
    if (cnt > 0) {
      br = zeroPad(cnt, String(brCode.substring(0,cnt)));
    } else {
      br = zeroPad(cnt, '');
    }
    //alert('cntr:'+cnt);
    //alert('rep:'+rep);
    mask = format.replace(rep, br);

    //alert('format:'+format);
    //alert('mask:'+mask);
    
    // if ($(productCode).length > 0) {
    //   var prcdChar = 'P';
    //   cnt = format.split(prcdChar).length - 1;
    //   rep = prcdChar.repeat(cnt);
    //   var pr = zeroPad(cnt, $(productCode).val());
    //   mask = mask.replace(rep, pr);
    // } else {
    //   var prcdChar = 'P';
    //   cnt = format.split(prcdChar).length - 1;
    //   //alert('cnt:'+cnt);
    //   rep = prcdChar.repeat(cnt);
    //   //alert('rep:'+rep);
    //   var pr = '';
    //   if (cnt > 0) {
    //     pr = zeroPad(cnt, $('#acctType').val());
    //   } else {
    //     pr = zeroPad(cnt, '');
    //   }
    //   mask = mask.replace(rep, pr);
    //   //lert('pr:'+pr);
    //   //alert('rep:'+rep);
    // }
    
    var srChar = 'N';
    var cnt = mask.split(srChar).length - 1;
    var rep = srChar.repeat(cnt);
    var sr = '';
    var srSeq = $(branch).find('option:selected').attr('seqno');

    if (cnt > 0) {
      sr = zeroPad(cnt, String(srSeq.substring(0,cnt)));
    } else {
      sr = zeroPad(cnt, '');
    }
    //alert('cntr:'+cnt);
    //alert('rep:'+rep);
    mask = mask.replace(rep, sr);
    maskx = mask.replace('C', '');
    mask = mask.replace('C', setcheckdigit(cbin.concat(maskx), weight));
    
    var placeholder = mask;//.replace(/N/g, '_'); //.replace(/N/g, '_')
    $(cardNo)
      .val(mask)
      .mask(mask)
      .attr('placeholder', placeholder);

    $('#prType').val(prType);

  });

  $.mask.definitions = {
    'N': '[0-9]'
  };

  $(branch).trigger('change');

  function setcheckdigit(card, weights) {

    var v_strlen = weights.length;
    var v_temp = '';
    var v_templen = 0;
    var v_tempx = 0;
    var v_val1 = 0;
    var v_checksum = 0;
    var v_chkdigit = 1;
    var v_tempy = '';

    //console.log(card);
    // console.log(weights);

    while (v_strlen >= 1 ) {
      v_temp = card.substring((v_strlen - 1), v_strlen) * weights.substring((v_strlen - 1), v_strlen);

      //v_tempy = v_temp;
      v_templen = v_temp.toString().length;
      v_tempx = 0;

      //console.log(v_templen);
      while (v_templen >= 1) {
        v_val1 = v_temp.toString().substring((v_templen - 1), v_templen);
        v_tempx = parseInt(v_tempx) + parseInt(v_val1);
        //console.log(v_tempx);
        v_templen = v_templen - 1;
      }
      v_temp = parseInt(v_temp);

      v_checksum = parseInt(v_checksum) + parseInt(v_tempx);
      v_strlen = v_strlen - 1;
    }

    //console.log(v_checksum);

    v_chkdigit = v_checksum % 10;

    if (v_chkdigit > 0) {
      v_chkdigit = ((v_checksum - v_chkdigit) + 10) - v_checksum;
    }

    return v_chkdigit;
  }
<?php } ?>

});
</script>
