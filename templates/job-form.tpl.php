<?php

?>

<form method="post" action="/jobs/create">

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

	<label for="ClosingDate__c">Closing Date</label><br />
	<input type="date" name="ClosingDate__c" id="ClosingDate__c" value="<?php print $job["ClosingDate__c"]; ?>" placeholder="Enter the closing date." />
	<br /><br />

	<label for="OpenUntilFilled__c">Open Until Filled?</label>&nbsp&nbsp
	<input type="checkbox" name="OpenUntilFilled__c" id="OpenUntilFilled__c" value="<?php print $job["OpenUntilFilled__c"]; ?>" />
	<br /><br />

	<input type="submit" value="Save" />
</form>
