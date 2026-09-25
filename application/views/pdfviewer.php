<iframe src="<?php echo $src; ?>" width="100%" height="100%" id="formTarget" name="formTarget" onLoad="init()"></iframe>
<script>
function init()
{
	var content = $('#formTarget').contents().find('body').html();

	if (content.substring(1,6) === 'table') {
		var width = 270;
		var height = 170;
		var buttons = {
			'OK': function () {
				$(this).dialog('close')
			}
		}
	} else {
		var width = '80%';
		var height = 600;
		var buttons = null;
	}
	$(DIALOG).dialog('option', {
		width: width,
		height: height,
		buttons: buttons
	});
	$(DIALOG).dialog('option', {
		position: 'center'
	});
}
</script>