<link rel="stylesheet" type="text/css" href="<?php print module_path(); ?>/assets/css/jobs.css"></link>

<div class="page-header">
	<h2>OCDLA Job List</h2>
</div>

<div>
	<a href="/jobs/new"><i class="fas fa-plus-circle"></i> new posting</a>
</div>

</br>

<div>
	<p>Welcome to OCDLA's Job Board. Job postings are removed three days after the Closing Date. Postings that are marked as "Open Until Filled" are removed six weeks after the Posting Date.</p>
	<p>You may also email your posting description to <a href="mailto:tmay@ocdla.org">tmay@ocdla.org</a> and we will post the job. Include the job title, salary, location, and closing date.</p>
</div>

</br>

<div class="item-list">

			
	<?php if(!isset($jobs) || (isset($jobs) && count($jobs) < 1)): ?>
		<ul class="table-row">
			<li>There are no current job postings.</li>
		</ul>
	<?php else: ?>
		
		<?php foreach($jobs as $job):

			$attachedSObject = $job["Attachments"]["records"][0];
			$docName = $attachedSObject["Name"];
			$hasAttachment = $attachedSObject != null;

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
		<div class="list-item">
			<?php if($user->isAdmin() || $userCreatedJob): ?>
				<div class="item-element admin-item">
					<a href="/job/edit/<?php print $job["Id"]; ?>"><i class="fas fa-edit" style="color:blue;"></i></a>
					<a href="/job/delete/Job__c/<?php print $job["Id"]; ?>"><i class="fas fa-trash-alt" style="color:blue;"></i></a>
				</div>
			<?php endif ?>
				<p><strong>Title: </strong><?php print $job["Name"]; ?></p>
				<p><strong>Opening Date: </strong><?php print $job["PostingDate__c"]; ?></p>
				<p><strong>Closing Date: </strong>
					<?php if($job["OpenUntilFilled__c"]): ?>
						Open until filled
					<?php else: ?>
						<?php print $job["ClosingDate__c"]; ?>
					<?php endif; ?>
				</p>
				<p><strong>Location: </strong><?php print $job["Location__c"]; ?></p>
				<p><strong>Salary: </strong><?php print $job["Salary__c"]; ?></p>


				<div class="download-area">
					<?php if($hasAttachment): ?>
						<a title="<?php print $docName; ?>" target="_blank" href="/attachment/<?php print $attachedSObject["Id"]; ?>">
							<?php print $filename; ?>
						</a>
					<?php elseif($hasContentDocument): ?>
						<a title="<?php print $contentDocument["Title"]; ?>" target="_blank" href="/contentdocument/<?php print $contentDocument["Id"]; ?>">
							<?php print $contentDocument["Title"]; ?>
						</a>
					<?php endif; ?>
				</div>

			
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>