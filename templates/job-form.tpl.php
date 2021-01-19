<?php
$classNames = $job["OpenUntilFilled__c"] ? "open-until-filled" : "";

?>

<!--CSS to toggle closing date-->
<style>
	.hidden {
		display: none;
	}
</style>

<form class="<?php print $classNames; ?>" name="form-jobs" method="post" action="/jobs/create">

	<h2>Enter the job!</h2>

	<label for="Name">Job Title</label><br />
	<input type="text" name="Name" id="Name" value="<?php print $job["Name"]; ?>" placeholder="Enter your job title." />
	<input type="hidden" name="Id" id="Id" value="<?php print $job["Id"]; ?>" />
	<br /><br />

	<label for="Salary__c">Salary</label><br />
	<input type="text" name="Salary__c" id="Salary__c" value="<?php print $job["Salary__c"]; ?>" placeholder="Enter the salary." />
	<br /><br />

	<label for="Location__c">Location</label><br />
	<input type="text" name="Location__c" id="Location__c" value="<?php print $job["Location__c"]; ?>" placeholder="Enter the location." />
	<br /><br />

	<label for="PostingDate__c">Posting Date</label><br />
	<input type="date" name="PostingDate__c" id="PostingDate__c" value="<?php print $job["PostingDate__c"]; ?>" placeholder="Enter the date posted." />
	<br /><br />

	<!--this if statement allows the closingDate__c to be toggled repeatedly in form view-->
	<?php if ($job["OpenUntilFilled__c"] == true) : ?>
		<div class="hidden" id="closingDate">
		<?php else : ?>
			<div id="closingDate">
			<?php endif; ?>
			<label for="ClosingDate__c">Closing Date</label><br />
			<input type="date" name="ClosingDate__c" id="ClosingDate__c" value="<?php print $job["ClosingDate__c"]; ?>" placeholder="Enter the closing date." />
			<br /><br />
			</div>

			<div onclick="handleCheck()">
				<label for="OpenUntilFilled__c">Open Until Filled?</label>&nbsp&nbsp
				<?php if ($job["OpenUntilFilled__c"] == true) : ?>
					<input type="checkbox" name="OpenUntilFilled__c" id="OpenUntilFilled__c" value="<?php print $job["OpenUntilFilled__c"]; ?>" checked />
				<?php else : ?>
					<input type="checkbox" name="OpenUntilFilled__c" id="OpenUntilFilled__c" value="<?php print $job["OpenUntilFilled__c"]; ?>" />
				<?php endif; ?>
				<br /><br />
			</div>

			<input type="submit" value="Save" />
</form>

<script>
	//*Variables*//
	let closingDate = document.getElementById("closingDate");

	//*JavaScript function to toggle closing date in form view*//
	function handleCheck() {

		if (window.getComputedStyle(closingDate).display == "none") {
			document.getElementById("closingDate").classList.remove('hidden');

			//console.log("true"); //testTrash
		} else {
			document.getElementById("closingDate").classList.add('hidden');
			document.getElementById("ClosingDate__c").innerHTML = null;

			//console.log("false");  //testTrash
		}



	}
</script>