<?php

?>


<style type="text/css">

.table-headers {
	display: none;
}

li.table-cell {
	list-style: none;
}

@media screen and (min-width: 800px) {
	.table-headers {
		display: table-row;
	}
}

</style>
  

			<div>
				<h2>OCDLA Jobs</h2>
			<div>
			
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
  


		<ul class="table-row table-headers">
		<?php if($isAdmin || $isMember): ?>
			<li class="table-header">Actions</li>
		<?php else: ?>

		<ul class="table-row">
			<!--<li class="table-header">Actions</li>-->

			<li class="table-header"></li>
			<li class="table-header">Title</li>
			<li class="table-header">Posted</li>
			<li class="table-header">Closes</li>
			<li class="table-header">Location</li>
			<li class="table-header">Salary</li>
			<li class="table-header">Documents</li>
		</ul>
	<?php endif; ?>

			
		<?php if(!isset($jobs) || (isset($jobs) && count($jobs) < 1)): ?>
			<ul class="table-row">
				<li>There are no current job postings.</li>
			</ul>
			
		<?php else: ?>
		
			<?php foreach($jobs as $job):

				$attachment = $job["attachments"][0];
				
			?>
				<ul class="table-row"> 
					<?php if($isAdmin || $isMember): ?>
						<li class="table-cell cart-first"><a href="/job/<?php print $job["Id"]; ?>/edit">Edit
						</a><!--<a href="/job/<?php print $job["Id"]; ?>/delete">Delete
						</a>--></li> 
					<?php else: ?>
						<li class="table-cell cart-middle"></li>
					<?php endif; ?>
					<li class="table-cell cart-middle"><?php print $job["Name"]; ?></li>
					<li class="table-cell cart-middle"><?php print $job["PostingDate__c"]; ?></li>
					<li class="table-cell cart-middle">
						<?php if($job["OpenUntilFilled__c"]): ?>
							Open until filled.
						<?php else: ?>
							<?php print $job["ClosingDate__c"]; ?>
						<?php endif; ?>
					</li>
					<li class="table-cell cart-middle"><?php print $job["Location__c"]; ?></li>
					<li class="table-cell cart-middle"><?php print $job["Salary__c"]; ?></li>
					<li class="table-cell cart-middle"><?php print $attachment["Name"]; ?></li>
					<li class="table-cell cart-last">
						<?php if($resource): ?>
							<a target="_blank" href="/content/modules/jobs/<?php print $resource; ?>">Job Description</a>
						<?php endif; ?>
					</li>
				</ul>
			<?php endforeach; ?>
		
		<?php endif; ?>
	
	</tbody>
	
</table>

