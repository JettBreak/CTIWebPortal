<form id="dailyActReport" method="post" target="formTarget">
    <div id="dailyActReport">
        <h1>Daily Card Activity Report</h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="200"><label for="transDate">Transaction Date: <span class="red">*</span></label></td>
                    <td><input type="text" name="transDate" id="transDate" style="width:100px" maxlength="10" value="<?php echo $date; ?>" class="validate[required] datePicker" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cardHolderOpts">Cardholder Options:</label></td>
                    <td><select name="cardHolderOpts" id="cardHolderOpts" style="width:112px">
                            <option value="0">ALL</option>
                            <option value="1">ISSUER</option>
                        </select></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="previewBtn" value="reports/dailycardact/preview">Preview</button>
        </span><span class="buttons floatRight">
        	<button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var form = $('form');
	
    $(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: '+0d',
        yearRange: '-10y:-0y'
    });
		
	$('#previewBtn').click(function (e) {
		pdfViewer('pdfviewer/preview', 'Preview');
		//
		e.preventDefault();
		
		setTimeout(function () {
			form.submit();
		}, 500);
		
	});
});
</script>