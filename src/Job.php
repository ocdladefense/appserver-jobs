<?php


class Job extends SObject {

	public function __construct($id = null) {
		parent::__construct("job",$id);
	}
	
	public function home() {
		return "View and post OCDLA jobs.";
	}
}
