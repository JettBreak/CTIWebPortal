<style>
.ui-progressbar-value {
	background-image: url(images/ebank/pbar.gif);
}
</style>

<div id="welcomePage">
    <h1>Progress Bar<?php echo $x; ?></h1>
    <div id="content">
    	<div id="progressbar"></div>
        <span id="progVal"></span>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatRight">
    	<button id="submitBtn">Submit</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<script> 
function lpStart() {
	$.ajax({
		type: 'GET',
		url: 'progress/get',
		dataType: 'json',
		success: function(data) {
			$('#progressbar').progressbar({
				value: parseInt(data)
			});
			$('#progVal').html(data);
			// do more processing
			
			setTimeout(lpStart, 1000);
		}
	});
};
 
//$(document).ready(lpStart);

$(function() {
	
	$('#progressbar').progressbar({
		value: 0
	});
	
	$('#submitBtn').click(function () {
		$.post('progress/post', {}, {}, 'json');
		lpStart();
		return false;
	});
});
</script>