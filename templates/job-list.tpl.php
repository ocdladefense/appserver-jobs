<link rel="stylesheet" type="text/css" href="<?php print module_path(); ?>/assets/css/jobs.css"></link>

<div>
	<h2>OCDLA Jobs</h2>
</div>

<div>
	<h5>
		<a href="/jobs/new">
			<img src="/content/images/icon-postit.jpg" alt="login" width="25" height="25" border="0" style="border:none;" />
			Create a Job Posting
		</a>
	</h5>
</div>

<div>
	<p>Welcome to OCDLA's Job Board. Job postings are removed three days after the Closing Date. Postings that are marked as "Open Until Filled" are removed six weeks after the Posting Date.</p>
	<p>You may also email your posting description to <a href="mailto:tmay@ocdla.org">tmay@ocdla.org</a> and we will post the job. Include the job title, salary, location, and closing date.</p>
</div>

<div class="table" id="job-postings">
	<tbody>

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
				<?php if($user->isAdmin() || $user->isOwner($job)): ?>
					<li class="table-cell cart-first">
						<a href="/job/edit/<?php print $job["Id"]; ?>">Edit</a>
						<a href="/job/delete/Job__c/<?php print $job["Id"]; ?>">Delete</a>
					</li>
				<?php endif ?>
				<?php if((!$user->isAdmin() && $user->isMember()) && !$user->isOwner($job)): ?>
					<li class="table-cell cart-first"></li>
				<?php endif ?>
				<li class="table-cell cart-middle"><?php print $job["Name"]; ?></li>
				<li class="table-cell cart-middle"><?php print $job["PostingDate__c"]; ?></li>
				<li class="table-cell cart-middle">
					<?php if($job["OpenUntilFilled__c"]): ?>
					<!--not showing up in form? -->
						Open until filled
						<!--END-->
					<?php else: ?>
						<?php print $job["ClosingDate__c"]; ?>
					<?php endif; ?>
				</li>
				<li class="table-cell cart-middle"><?php print $job["Location__c"]; ?></li>
				<li class="table-cell cart-middle"><?php print $job["Salary__c"]; ?></li>


				<li class="table-cell cart-middle">
					<?php if($hasAttachment): ?>
						<a title="<?php print $docName; ?>" target="_blank" href="/attachment/<?php print $attachedSObject["Id"]; ?>">
							<?php print $filename; ?>
						</a>
					<?php elseif($hasContentDocument): ?>
						<a title="<?php print $contentDocument["Title"]; ?>" target="_blank" href="/contentdocument/<?php print $contentDocument["Id"]; ?>">
							<?php print $contentDocument["Title"]; ?>
						</a>
					<?php endif; ?>
				</li>

				
			</ul>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>