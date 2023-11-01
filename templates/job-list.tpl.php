<link rel="stylesheet" type="text/css" href="<?php print module_path(); ?>/assets/css/jobs.css" />


<h2>OCDLA Jobs</h2>



	<h5>
		<a href="/jobs/new">
			<img src="/content/images/icon-postit.jpg" alt="login" width="25" height="25" style="border:none;" />
			Create a Job Posting
		</a>
	</h5>

<style type="text/css">
	.warning {
		border: 1px solid red;
		border-radius: 4px;
		padding:8px;
	}
</style>
<div style="margin-bottom:20px;">
	<p>Welcome to OCDLA's Job Board. Job postings are removed three days after the Closing Date. Postings that are marked as "Open Until Filled" are removed six weeks after the Posting Date.</p>
	<p>You may also email your posting description to <a href="mailto:cpainter@ocdla.org">cpainter@ocdla.org</a> and we will post the job. Include the job title, salary, location, and closing date.</p>
	<p class="warning">We are currently working to fix an issue with Job Posting attachments.  In the meantime, please email your posting attachments to OCDLA.</p>
</div>

<div class="table" id="job-postings">
	

	<ul class="table-row">
		<?php if($user->isAdmin() || $user->isMember()): ?>
			<li class='table-header'>Actions</li>
		<?php endif ?>
		<li class="table-header">Title</li>
		<li class="table-header">Posted</li>
		<li class="table-header">Closes</li>
		<li class="table-header">Location</li>
		<li class="table-header">Salary</li>
		<li class="table-header">Documents</li>
	</ul>



	<?php foreach($jobs as $job): ?>

		<?php 
			$attachedSObject = $job["Attachments"]["records"][0];
			$docName = $attachedSObject["Name"];
			$hasAttachment = isset($job["AttachmentUrl__c"]);//$attachedSObject != null;

			$contentDocument = $job["ContentDocument"];
			$hasContentDocument = $contentDocument != null;
			$userCreatedJob = $job["CreatedById"] == $user->getId();

			
			if($hasAttachment) {
				$parts = explode(".", $docName);
				$ext = array_pop($parts);
				$name = implode(".", $parts);
				$tooLong = strlen($name) > 20;
				$short = substr($name, 0, 10);

				$filename = ($tooLong ? ($short . "...") : ($name.".")) . $ext;
			}
		?>


		<ul class="table-row"> 
			<?php if($user->isAdmin() || $userCreatedJob): ?>
				<li class="table-cell admin-area">
					<a href="/job/edit/<?php print $job["Id"]; ?>">edit</a>
					<a href="/job/delete/Job__c/<?php print $job["Id"]; ?>">delete</a>
				</li>
			<?php endif ?>
			<?php if((!$user->isAdmin() && $user->isMember()) && !$userCreatedJob): ?>
				<li class="table-cell cart-first"></li>
			<?php endif ?>
			<li class="table-cell title"><?php print $job["Name"]; ?></li>
			<li class="table-cell date"><?php print $job["PostingDate__c"]; ?></li>
			<li class="table-cell date">
				<?php if($job["OpenUntilFilled__c"]): ?>
				<!--not showing up in form? -->
					Open until filled
					<!--END-->
				<?php else: ?>
					<?php print $job["ClosingDate__c"]; ?>
				<?php endif; ?>
			</li>
			<li class="table-cell location"><?php print $job["Location__c"]; ?></li>
			<li class="table-cell salary"><?php print $job["Salary__c"]; ?></li>


			<li class="table-cell files">
				<?php if(false): ?>

					attachment forthcoming
				<?php elseif($hasAttachment): ?>
					<a title="<?php print $docName; ?>" target="_blank" href="<?php print $job["AttachmentUrl__c"]; ?>">
						View attachment
					</a>
				<?php elseif(false && $hasContentDocument): ?>
					<a title="<?php print $contentDocument["Title"]; ?>" target="_blank" href="/file/download/<?php print $contentDocument["Id"]; ?>">
						<?php print $contentDocument["Title"]; ?>
					</a>
				<?php endif; ?>
			</li>

			
		</ul>
	<?php endforeach; ?>
		
</div>
