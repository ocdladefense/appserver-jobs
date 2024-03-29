<?php
$classNames = array();

$toggleClosingDateClass = $job["OpenUntilFilled__c"] ? "hidden" : "";

$isOpenUntilFilled = $job["OpenUntilFilled__c"] ? "checked" : "";

$hasAttachment = $attachment != null;

$contentDocument = $job["ContentDocument"];
$hasContentDocument = $contentDocument != null;


if($hasAttachment || $hasContentDocument){
	
	$classNames .= " has-attachment ";
}

?>

<!--CSS to toggle closing date-->
<style>


	label {
		color: rgba(30,30,30,0.7);
		font-size: 14px;
	}
	
	.form-item {
		margin-top: 27px;
	}
	
	.has-attachment #uploader {
		display:none;
	}

	#existing-attachments{
		display:none;
	}
	.has-attachment #existing-attachments{
		
		display:block;
	}
	
	button, input {
		overflow: visible;
		padding: 8px;
		color: rgba(30,30,30,0.9);
		width: 100%;
	}	
	
	
	input[type="submit"],
	input[type="file"] {
		display: block;
		padding: 4px;
	}
	
	input[type=checkbox], input[type=radio] {
			box-sizing: border-box;
			padding: 0;
			width: auto;
	}

	.sidenav {
    	display: none;
	}

	.hidden{
		display:none;
	}
 

	@media screen and (min-width:800px) {
		.label {
			display:none;
		}
		
		.form-item {
			max-width: 50%;
		}
	}
</style>




<form enctype="multipart/form-data" onsubmit="onSubmit();" class="<?php print $classNames; ?>" id="jobs-form" name="form-jobs" method="post" action="/jobs/create">

	<h2>OCDLA: post a job</h2>
	<p>Job postings are made available to the public and OCDLA members.  Postings are removed on the close date you specify.  Postings open until filled are removed 6 weeks after the posted date.</p>

	<div class="form-item">
		<label for="Name">Job Title</label><br />
		<input type="text" name="Name" id="Name" value="<?php print $job["Name"]; ?>" placeholder="Enter your job title." />
		<input type="hidden" name="Id" id="Id" value="<?php print $job["Id"]; ?>" />
	</div>


	<div class="form-item">
		<label for="Salary__c">Salary</label><br />
		<input type="text" name="Salary__c" id="Salary__c" value="<?php print $job["Salary__c"]; ?>" placeholder="Enter the salary." />
	</div>


	<div class="form-item">
		<label for="Location__c">Location</label><br />
		<input type="text" name="Location__c" id="Location__c" value="<?php print $job["Location__c"]; ?>" placeholder="Enter the location." />
	</div>


	<div class="form-item">
		<label for="PostingDate__c">Posting Date</label><br />
		<input type="date" name="PostingDate__c" id="PostingDate__c" value="<?php print $job["PostingDate__c"]; ?>" placeholder="Enter the date posted." />
	</div>



	<div id="closing-date" class="form-item <?php print $toggleClosingDateClass; ?>">
		<!--beginning closingdate field-->
		<label for="ClosingDate__c">Closing Date</label><br />
		<input type="date" name="ClosingDate__c" id="ClosingDate__c" value="<?php print $job["ClosingDate__c"]; ?>" placeholder="Enter the closing date." />
	</div>

	<div id="open-until-filled" class="form-item">
		Open until filled?
		<input type="checkbox" name="OpenUntilFilled__c" value="1" <?php print $isOpenUntilFilled; ?> />
	</div>



	<div class="form-item">
		<!--changed attachment to an array type-->
		
		<?php if($hasAttachment): ?>
			<input type="hidden" name="attachmentId" id="attachmentId" value="<?php print $attachment["Id"]; ?>" />
			<div id="existing-attachments">
				<strong>Uploaded Attachments</strong><br />
				<a class="toggle-file-upload" href="#" onclick="toggleFileUploadElement(); return false;">Edit</a>
				<label><?php print $attachment["Name"] ?></label><br />
			</div>
		<?php elseif($hasContentDocument): ?>
			<input type="hidden" name="ContentDocumentId" id="ContentDocumentId" value="<?php print $contentDocument["Id"]; ?>" />
			<div id="existing-attachments">
				<strong>Uploaded Attachments</strong><br />
				<a class="toggle-file-upload" href="#" onclick="toggleFileUploadElement(); return false;">Edit</a>
				<label><?php print $contentDocument["Title"] ?></label><br />
			</div>
		<?php endif; ?>
			<div id="uploader">
				<label for="Attachments__c[]">Upload Files</label>
				<input type="file" id="Attachments__c[]" name="Attachments__c[]" />
				<a class="toggle-file-upload" href="#" onclick="toggleFileUploadElement(); return false;">Cancel</a>
			</div>
	</div>


	<div class="form-item">
		<input type="submit" value="Save" />
	</div>
	
	
</form>

<script>

	let closingDate = document.getElementById("closing-date");
	let checkbox = document.getElementById("open-until-filled");
	checkbox.addEventListener("change", hideClosingDate);

	function hideClosingDate(e){

		closingDate.classList.toggle("hidden");
	}

	function toggleFileUploadElement(e){
		console.log(e);
		let theForm = document.getElementById("jobs-form");
		let hasExisting = theForm.classList.contains("has-attachment");
		theForm.classList.toggle("has-attachment");
	}
</script>