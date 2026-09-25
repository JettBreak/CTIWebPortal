<div style="width:500px">
    <h1>Card Inquiry</h1>
    <div id="content">
    	<div style="font-size:15px; padding-bottom: 10px">Customer Card Information</div>
    	<table>
        	<tr>
            	<td width="150"><label for="cardNo" class="idName">Card Number: <span class="red">*</span></label></td>
                <td><input type="text" name="cardNo" id="cardNo" style="width: 200px"/></td>
			</tr>
            <tr>
                <td><label>Customer Name:</label></td>
                <td><input type="text" style="width: 200px"/></td>
			</tr>
			<tr>
                <td><label>Card Status:</label></td>
                <td><input type="text" style="width: 200px"/></td>
            </tr>
            <tr>
                <td><label>Passbook Status:</label></td>
                <td><input type="text" style="width: 200px"/></td>
            </tr>
        </table>
        
        <div class="divider"></div>
        <div style="font-size:15px; padding-bottom: 10px">Balances</div>
        
        <table>
        	<tr>
                <td width="150"><label>Currency Used:</label></td>
                <td><input type="text" style="width: 200px" value="Philippine Peso"/></td>
			</tr>
            <tr>
                <td><label>Available Balance:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
			</tr>
            <tr>
                <td><label>Currenct Balance:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
            </tr>
			<tr>
                <td><label>Uncleared Deposits:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
            </tr>
            <tr>
                <td><label>Hold Balance:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
            </tr>
        </table>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="submitBtn">Submit</button>
    </span>
	<span class="buttons floatRight">
    	<button class="closebtn">Close</button>
    </span>
</div>