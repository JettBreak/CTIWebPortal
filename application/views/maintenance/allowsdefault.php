<form id="allowsDefaultForm" method="post">
<div style="width:360px">
    <h1>Allows Default</h1>
    <div id="content">
    	<table width="100%">
        	<tr>
            	<td width="120"><label for="prtype"><?php echo $prtypelbl; ?></label></td>
                <td>
                	<select name="prtype" id="prtype" style="width:200px">
						<?php echo html_entity_decode($prTypes); ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td width="120"><label for="accttype"><?php echo $accttypelbl; ?></label></td>
                <td>
                	<select name="accttype" id="accttype" style="width:200px">
						<?php echo html_entity_decode($acctTypes); ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td colspan="2">
                	<?php echo html_entity_decode($tranAllows); ?>
                </td>
            </tr>
            <tr>
            	<td colspan="2" id="chkBoxControl">
                	<a title="Checks all the checkboxes above" href="#" id="checkAll">Check All</a> |
                    <a title="Greys all the checkboxes above" href="#" id="greyAll">Grey All</a> |
                    <a title="Unchecks all the checkboxes above" href="#" id="unCheckAll">Uncheck All</a> |
                    <a title="Toggle the checkboxes above" href="#" id="toggleCheck">Toggle Check</a>
                </td>
            </tr>
        </table>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
		<button id="submitBtn">Save</button
        ><button id="updateAllBtn">Update All Allows</button>
	</span>
	<span class="buttons floatRight">
    	<button class="closebtn">Close</button>
    </span>
</div>
</form>
<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
label {
	position: relative;
}
</style>
<script>
$(function() {
	var form = $('form');
	var prtype = '#prtype';
	var accttype = '#accttype';
	
    initSession('<?php echo $sessionExp; ?>');
	$(prtype).change(function () {
		var opt = $(accttype);
		
		opt.find('option').hide().removeAttr('selected');
		
		var selected = opt.find('option[grouptype="' + this.value + '"]');
		
		selected.removeAttr('style');
		selected.first().attr('selected', true);
		
		$('.box').hide();
		$('#' + this.value).show();
		
		var allows = $(accttype).find(':selected').attr('defaultallows');
		var greyed = $(accttype).find(':selected').attr('defaultgreyed');
		popAllows(allows, greyed);
		
		initCheckBoxes();
	});
	
	
	$(accttype).change(function () {
		var allows = $(this).find(':selected').attr('defaultallows');
		var greyed = $(this).find(':selected').attr('defaultgreyed');
		popAllows(allows, greyed);
	});
	
	initCheckBoxes();
	
	function initCheckBoxes() {
		// find the disabled elements
		var disabled = $('.box :checkbox');
		
		// loop through each of the elements and create an overlay
		disabled.each(function () {
			// get the disabled element
			var self = $(this);
			// get it's parent label element
			var parent = self.closest('label');
			// create an overlay
			var overlay = $('<div />');
	
			// style the overlay
			overlay.css({
			// position the overlay in the same real estate as the original parent element 
				position: 'absolute',
				//top: parent.position().top,
				//left: parent.position().left,
				top: 1,
				left: 2,
				width: parent.outerWidth(),
				height: parent.outerHeight(),
				zIndex: 10000,
				// IE needs a color in order for the layer to respond to mouse events
				backgroundColor: '#fff',
				// set the opacity to 0, so the element is transparent<br>
				opacity: 0
			})
			// attach the click behavior
			.click(function (){
				// trigger the original event handler
				
				if (!self.attr('checked') && !self.is(':disabled')) {
					//e.preventDefault();
					
					self.attr('checked', true)
							.attr('disabled', true);
							
				} else if (self.attr('checked') && self.is(':disabled')) {
					
					self.attr('checked', false)
							.attr('disabled', false);
							
				}
			});
	
			// add the overlay to the page  
			parent.append(overlay);
		});
	}
	
	$('#submitBtn').click(function (e) {
		var acctTypeSelected = $(accttype).find(':selected');
		
		var formData = {
			prtype: $('#prtype option:selected').val(),
			accttype: acctTypeSelected.val(),
			allows: null,
			greyed: null
		};
		
		var allows = [];
		var greyed = [];
		
		$('#' + $(prtype).val()).find(':checkbox').each(function () {
			var chk = $(this);
			
			if (chk.is(':checked')) {
				if (chk.is(':disabled')) {
					greyed.push(parseInt(chk.val()));
				} else {
					allows.push(parseInt(chk.val()));
				}
			}
		});
		
		var allowsBin = '';
		var greyedBin = '';
		
		var maxAllows = Math.max.apply(null, allows);
		
		//console.log('Max Allows: ' + maxAllows);
		
		for (var i = 1; i <= maxAllows; i++) {
			if ($.inArray(i, allows) > -1) {
				allowsBin += '1';
			} else {
				allowsBin += '0';
			}
		}
		
		var maxGreyed = Math.max.apply(null, greyed);
		
		//console.log('Max Greyed: ' + maxGreyed);
		
		for (var i = 1; i <= maxGreyed; i++) {
			if ($.inArray(i, greyed) > -1) {
				greyedBin += '1';
			} else {
				greyedBin += '0';
			}
		}
		
		formData.allows = allowsBin;
		formData.greyed = greyedBin;
		
		console.log(formData);
		//console.log(allowsBin);
		//console.log(greyedBin);
		
		messageBox('Save changes?', 'Confirm', 'confirm', function () {
			
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/allowsdefault/save',
					data: formData,
					dataType: 'json',
					beforeSend: function() {
						waitMessage('Updating...');
					},
					success: function(data) {
						messageBox(data.message);
						
						acctTypeSelected.attr('defaultallows', allowsBin);
						acctTypeSelected.attr('defaultgreyed', greyedBin);
					}
				})
			);
		});
		e.preventDefault();
	});
	
	$('#updateAllBtn').click(function (e) {
		var pType = $(prtype).find('option:selected').text();
		var aType = $(accttype).find('option:selected').text();
		
		messageBox('Warning! This will replace all existing<br /><strong>[' + pType + ' - ' + aType + ']</strong><br /> allows. Continue anyway?', 'Confirm', 'confirm', function () {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/allowsdefault/updateAll',
					data: form.serialize(),
					dataType: 'json',
					beforeSend: function() {
						waitMessage('Updating...');
					},
					success: function(data) {
						messageBox(data.message);
					}
				})
			);
		});
		e.preventDefault();
	});
	
	$('input, select').change(function (e) {
		$(MSGBOX).dialog('close');
	});
	
	function popAllows(allows, greyed) {
				
		console.log('allows: ' + allows);
		console.log('greyed: ' + greyed);
		var	chk = $('#' + $(prtype).val()).find(':checkbox');

		chk.each(function () {
			var self = $(this);
			var bitno = this.id.substr(7) - 1;
			
			var bin1 = allows.substr(bitno,1);
			var bin2 = greyed.substr(bitno,1);
			
			console.log(this.id + ' ' + bin1);
			
			if (bin1 === '1') {
				self.attr('checked', true)
					.attr('disabled', false);
			} else {
				if (bin2 === '1') {
					self.attr('checked', true)
						.attr('disabled', true);
				} else {
					self.attr('checked', false)
						.attr('disabled', false);
				}
			}
		});
	}
	
	$(prtype).triggerHandler('change');
	
	$('#checkAll').click(function(e) {
		var	chk = $('#' + $(prtype).val()).find(':checkbox');
		
		chk.attr('checked', true)
			.attr('disabled', false);
		e.preventDefault();
	});
	
	$('#greyAll').click(function(e) {
		var	chk = $('#' + $(prtype).val()).find(':checkbox');
		
		chk.attr('checked', true)
			.attr('disabled', true);
		e.preventDefault();
	});
	
	$('#unCheckAll').click(function(e) {
		var	chk = $('#' + $(prtype).val()).find(':checkbox');
		
		chk.attr('checked', false)
			.attr('disabled', false);
		e.preventDefault();
	});
	
	$('#toggleCheck').click(function(e) {
		var	chk = $('#' + $(prtype).val()).find(':checkbox');
		
		chk.not(':disabled').attr('checked', !chk.attr('checked'));
		e.preventDefault();
	});
});
</script>