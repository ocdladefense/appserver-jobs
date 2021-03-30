<?php
// Figure out the relationship between 'Database\SObject' and 'Salesforce\SObject'.
use Database\SObject as SObject;


class Job extends SObject {

	public function __construct($id = null) {
		parent::__construct("job",$id);
	}
	

}
