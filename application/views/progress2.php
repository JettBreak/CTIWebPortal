<style>
.ui-progressbar-value {
	background-image: url(images/ebank/pbar.gif);
}
</style>
<form id="myForm" action="progress2/screenstatus" method="post" target="statusFrame">
<div id="welcomePage">
    <h1>Progress Bar</h1>
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
</form>
<iframe id="statusFrame" name="statusFrame"></iframe>
<script> 
$(function() {
	$('#progressbar').progressbar({
		value: 0
	});
	
	$('#submitBtn').click(function (e) {
		$('#myForm').submit();
		e.preventDefault();
	});
});

function updateStatus(progress) {
	$('#progressbar').progressbar({
		value: progress
	});
}
</script>