<form id="changeCIFForm" method="post">
<input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
<div id="changeCIF">
	<h1>Change Customer Link <span class="floatRight">[<?php echo $cardNo; ?>]</span></h1>
    <div id="content">
        <table width="100%">
            <tr>
                <td width="130"><label for="dtEnrolled">Date Enrolled:</label></td>
                <td><input type="text" id="dtEnrolled" style="width: 200px" value="<?php echo $dtEnrolled; ?>" readonly/></td>
            </tr>
            <tr>
                <td><label for="custCard">Customer Card:</label></td>
                <td><input type="text" id="custCard" style="width: 200px" value="<?php echo $cardNo; ?>" readonly/></td>
            </tr>
            <tr>
                <td><label for="cardStatus">Card Status:</label></td>
                <td><input type="text" id="cardStatus" style="width: 200px" value="<?php echo $status; ?>" readonly/></td>
            </tr>
            <tr>
                <td><label for="cardType">Card Type:</label></td>
                <td><input type="text" id="cardType" style="width: 200px" value="<?php echo $acctdesc; ?>" readonly/></td>
            </tr>
            <tr>
                <td><label for="embossName">Emboss Name:</label></td>
                <td><input type="text" id="embossName" style="width: 200px" value="" readonly/></td>
            </tr>
            <tr>
                <td><label for="custName">Customer Name:</label></td>
                <td><input type="text" id="custName" style="width: 200px" value="<?php echo $custName; ?>" readonly/></td>
            </tr>
            <tr>
            	<td colspan="2">&nbsp;</td>
            </tr>
            <tr>
            	<td><label for="newLink"><strong>New Customer Link:</strong></label></td>
                <td width="260"><input type="text" name="newLink" id="newLink" style="width: 200px" readonly/> <button id="searchBtn">Search</button></td>
            </tr>
        </table>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="saveBtn" value="card/changelink/submit">Save</button>
	</span>
    <span class="buttons floatRight">
    	<button id="resetBtn" type="reset">Reset</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>
<style>
.dataTables_scrollBody {
	height: 150px !important;
	max-height: 150px !important;
}
</style>

<script>
$(function() {
	var $saveBtn 	= '#saveBtn',
		$resetBtn 	= '#resetBtn',
		$refreshBtn = '#refreshBtn',
		$searchBtn	= '#searchBtn',
		$form 		= $('form');
	
	$form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Sending request...');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			if (data.success === true) {
				window.location.hash = 'card/search';
			}
			messageBox(data.message);
		},
		scroll: false
	});
	$form.validationEngine('attach');
	
	$($saveBtn).click(function(e) {
		messageBox('Continue card type change?', 'Confirm', 'confirm', function() {
			$form.submit();
		});
		e.preventDefault();
	});
	
	$($resetBtn).click(function() {

	});
	
	$($searchBtn).click(function(e) {
		e.preventDefault();
	});
});
</script>