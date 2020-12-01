<?php

use Database\SObject as SObject;


class Job extends SObject {

	public function __construct($id = null) {
		parent::__construct("job",$id);
	}
	

}
