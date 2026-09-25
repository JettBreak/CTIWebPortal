<form id="resetLimitsForm" action="card/resetlimits/submit" method="post">
<input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
<table style="width:100%" id="trxTable">
	<thead>
    	<tr>
        	<th>Transactions</th>
            <th>Amount</th>
            <th>Counter</th>
        </tr>
    </thead>
	<tbody>
    	<?php echo html_entity_decode($trx); ?>
    </tbody>
    <tfoot>
    	<tr>
        	<td colspan="3">&nbsp;</td>
        </tr>
    	<tr>
        	<td colspan="3">
            <input id="pinRetry" name="pinRetry" type="checkbox"/> PIN Retry Count
            </td>
        </tr>
    </tfoot>
</table>
</form>
<script>
$(function() {
	var form = $('#resetLimitsForm');
	
	form.submit(function (e) {
		requests.push(
			$.ajax({
				type: 'POST',
				url: form.attr('action'),
				data: form.serialize(),
				dataType: 'json',
				beforeSend: function () {
					waitMessage('Resetting...');
				},
				success: function (data) {
					messageBox(data.message);
					if (data.success === true) {
						$(MSGBOX).one('dialogbeforeclose', function () {
							$(DIALOG).dialog('close');
							if ($('#pinRetry').attr('checked')) {
								$('#pinRetryCnt').text('0');
							}
						});
					}
				}
			})
		);
		e.preventDefault();
	});
});
</script>