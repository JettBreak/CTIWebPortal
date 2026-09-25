<div id="welcomePage">
    <h1>Long Poll Example</h1>
    <div id="content">
    	<textarea id="response">
        </textarea>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatRight">
    	<button id="submitBtn">Submit</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<script>
var timestamp = null;
	
function waitForMsg() {
	requests.push(
		$.ajax({
			type: 'GET',
			url: 'longpoll/getData?timestamp=' + timestamp,
			dataType: 'json',
			global: false,
			timeout: 0,
			success: function(data) {
				if (data['msg'] !== '') {
					$('#response').val(data['msg']);
				}
				timestamp = data['timestamp'];
				setTimeout(waitForMsg, 1000);
			},
			error: function(a, b, c) {
				$('#response').val('Error:' + b + '(' + c + ')');
				setTimeout(waitForMsg, 15000);
			}
		})
	);
}
$(function() {
	$('#submitBtn').click(function(e) {
		waitForMsg();
		e.preventDefault();
	});
});
</script>