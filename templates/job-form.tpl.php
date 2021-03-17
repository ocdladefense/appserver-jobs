<?php
$classNames = $job["OpenUntilFilled__c"] ? "open-until-filled" : "";

?>

<!--CSS to toggle closing date-->
<style>
	.open-until-filled #closingDate {
		display: none;
	}
	
	
	
	input[type="submit"],
	input[type="file"] {
		display: block;
		padding: 4px;
	}
</style>

<form enctype="multipart/form-data" onsubmit="onSubmit();" class="<?php print $classNames; ?>" id="jobs-form" name="form-jobs" method="post" action="/jobs/create">

	<?php if(!$isEdit): ?> <h2>OCDLA: Post a job</h2> <?php else: ?> <h2>OCDLA: Update a job posting</h2> <?php endif ?>
	<p>Job postings are made available to the public and OCDLA members.  Postings are removed on the close date you specify.  Postings open until filled are removed 6 weeks after the posted date.</p>

	<?php if($isEdit): ?>
	<strong>Job Id: <?php print $job["Id"]; ?></strong><br /><br />
	<?php endif ?>

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

	<div id="closingDate">
		<!--beginning closingdate field-->
		<label for="ClosingDate__c">Closing Date</label><br />
		<input type="date" name="ClosingDate__c" id="ClosingDate__c" value="<?php print $job["ClosingDate__c"]; ?>" placeholder="Enter the closing date." />
		<br /><br />
	</div>


	<div onclick="handleCheck()">
		<label for="OpenUntilFilled__c">Open Until Filled?</label>&nbsp&nbsp
		<?php if ($job["OpenUntilFilled__c"] == true) : ?>
			<input type="checkbox" name="OpenUntilFilledHelper__c" id="OpenUntilFilledHelper__c" value="true" checked />
		<?php else : ?>
			<input type="checkbox" name="OpenUntilFilledHelper__c" id="OpenUntilFilledHelper__c" value="true" />
		<?php endif; ?>
		<br /><br />
	</div>
	<input type="hidden" name="OpenUntilFilled__c" id="OpenUntilFilled__c" value="" />

	<?php if($isEdit): ?>
	<strong>Attachments already uploaded for this job:</strong><br />
	<?php foreach($attachments as $attachment):?>
	<a href="job/delete/Attachment/<?php $attachment["Id"] ?>">Delete</a> <?php print $attachment["Name"] ?> <br />
	<?php endforeach ?>
	<?php endif ?><br /><br />


	<!--changed attachment to an array type-->
	<label for="Attachments__c[]">Upload Files</label>
	<input type="file" id="Attachments__c[]" name="Attachments__c[]" >
	<br /><br />

	<input type="submit" value="Save" />
</form>

<script>
	function onSubmit() {
		this.openUntilFilled = document.getElementById('OpenUntilFilledHelper__c');
		this.openUntilFilled.disabled = true;
	}

	//*JavaScript function to toggle closing date in form view*//
	function handleCheck() {

		//*Variables*//
		this.jobsForm = document.getElementById('jobs-form');
		this.openUntilFilledHelper = document.getElementById('OpenUntilFilledHelper__c');
		this.openUntilFilled = document.getElementById('OpenUntilFilled__c');
		this.closingDate = document.getElementById("ClosingDate__c");
		this.isChecked = this.openUntilFilledHelper.checked;


		if (isChecked) {
			this.jobsForm.classList.add('open-until-filled');
			this.closingDate.disabled = true;
			//0 out closing date field
			this.openUntilFilled.value = "true";


		} else {
			this.jobsForm.classList.remove('open-until-filled');
			this.closingDate.disabled = false;
			this.openUntilFilled.value = "false";
		}
	}
</script>