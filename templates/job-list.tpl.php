<?php

?>

  

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
				<p>Welcome to OCDLA's Job Board. Log-in to post a job.  Job postings are removed three days after the Closing Date. Postings that are marked as "Open Until Filled" are removed six weeks after the Posting Date.</p>
		
				<p>You may also email your posting description to <a href="mailto:tmay@ocdla.org">tmay@ocdla.org</a> and we will post the job. Include the job title, salary, location, and closing date.</p>
			</div>


<div class="table" id="job-postings">
        
	<tbody>
  

		<ul class="table-row">
		<?php if($isAdmin || $isMember): ?>
			<li class="table-header">Actions</li>
		<?php else: ?>
			<li class="table-header"></li>
		<?php endif; ?>
			<li class="table-header">Title</li>
			<li class="table-header">Posted</li>
			<li class="table-header">Closes</li>
			<li class="table-header">Location</li>
			<li class="table-header">Salary</li>
			<li class="table-header">Other</li>
		</ul>
	
		
		<!--
		<ul class="table-row"> 
			<li class="table-cell cart-first">Investigator</li>
			<li class="table-cell cart-middle">September 18, 2020</li>
			<li class="table-cell cart-middle">Open until filled.</li>
			<li class="table-cell cart-middle">Multnomah Defenders, Portland</li>
			<li class="table-cell cart-middle">DOE</li>
			<li class="table-cell cart-last"><a target="_blank" href="https://www.ocdla.org/employment/JobFiles/ACF463F.pdf">Job Description</a></li>
		</ul>
		-->
			
			
		<?php if(!isset($jobs) || (isset($jobs) && count($jobs) < 1)): ?>
			<ul class="table-row">
				<li>There are no current job postings.</li>
			</ul>
			
		<?php else: ?>
		
			<?php foreach($jobs as $job):
				$attachments = isset($job["Attachments"]) ? $job["Attachments"]["records"] : null;
				$attachment = isset($attachments) && count($attachments) ? $attachments[0] : null;
				$resource = isset($attachment) ? "/attachment/{$attachment['Id']}/{$attachment['Name']}" : null;
			?>
				<ul class="table-row"> 
					<?php if($isAdmin || $isMember): ?>
						<li class="table-cell cart-first"><a target="_blank" href="/job/<?php print $job["Id"]; ?>/edit">Edit
						</a><a target="_blank" href="/job/<?php print $job["Id"]; ?>/delete">Delete
						</a></li> 
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
					<li class="table-cell cart-last">
						<?php if($resource): ?>
							<a target="_blank" href="<?php print $resource; ?>">Job Description</a>
						<?php endif; ?>
					</li>
				</ul>
			<?php endforeach; ?>
		
		<?php endif; ?>
	
	</tbody>
	
</table>

