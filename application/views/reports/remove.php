<form id="removeForm" width="150" action="reports/remove/submit" method="post" enctype="multipart/form-data" target="formTarget">
<table width="200" id="trxTable">
    <?php echo html_entity_decode($remove); ?>
</table>
</form>
<iframe id="formTarget" name="formTarget" class="hidden"></iframe>
<style>
input[type="checkbox"] {
	position: relative;
	top: 1px;
}
#trxTable span:hover {
	cursor: pointer;
}
#trxTable tbody tr:nth-child(even) {
	background-color: #333;
}
#trxTable tbody tr:nth-child(odd) {
	background-color: #333;
}
.removeForm{width:100px;}
</style>