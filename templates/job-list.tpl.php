<style type="text/css">
	.table-headers {
		display: none;
	}

	li.table-cell {
		list-style: none;
		padding-right: 15px;
		padding-left: 15px;
	}

	@media screen and (min-width: 800px) {
		.table-headers {
			display: table-row;
		}
	}
</style>


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
			<?php

use Salesforce\ContentDocument;

if ($isAdmin || $isMember) : ?>
				<li class='table-header'>Actions</li>
			<?php endif ?>
			<li class="table-header">Title</li>
			<li class="table-header">Posted</li>
			<li class="table-header">Closes</li>
			<li class="table-header">Location</li>
			<li class="table-header">Salary</li>
			<li class="table-header">Attachments</li>
		</ul>


		<?php if (!isset($jobs) || (isset($jobs) && count($jobs->getRecords()) < 1)) {  ?>
			<ul class="table-row">
				<li>There are no current job postings.</li>
			</ul>
			
		<?php } else {
			$docs = $jobs->getAllAttachments();
			foreach($jobs->getRecords() as $job):
				$hasAttachment = false;
				//print "<h2>"."FUBAR"."</h2>";
					

				$Id = $job["Id"];
				$doc = $docs[$Id];
				//var_dump($doc);exit;
				if($doc != null) {
					$hasAttachment = true;
				}
				/*if($hasAttachment){
					print "I have an attachment";
				}
				else{
					print "I do not have an attachment";
				}
				print "<h2>".$hasAttachment."</h2>";
				*/
				if($hasAttachment) {
					//$name = $doc->getName(); 
					$name = $doc->getName();
					//print "<h2>".$name."</h2>";
				}
				
			?>
			<ul class="table-row"> 
				<?php if($isAdmin || $isMember): ?>
					<li class="table-cell cart-first">
						<a href="/job/edit/<?php print $job["Id"]; ?>">Edit</a>
						<a href="/job/delete/Job__c/<?php print $job["Id"]; ?>">Delete</a>
					</li>
				<?php endif ?>
				<li class="table-cell cart-middle"><?php print $job["Name"]; ?></li>
				<li class="table-cell cart-middle"><?php print $job["PostingDate__c"]; ?></li>
				<li class="table-cell cart-middle">
					<?php if($job["OpenUntilFilled__c"]): ?>
					<!--message in the closing date when open until flled is selected -->
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
						<a title="<?php print $name; ?>" target="_blank" href="/attachment/<?php print $Id; ?>">
							<?php print $name; ?>
						</a>
					<?php endif; ?>
				</li>

				
			</ul>
			<?php endforeach; ?>
		<?php } ?>
	</tbody>
	</table>