<?php

class ListTemplate extends Template {

		// Component styles.
		private $css = array(
			array(
				"active" => true,
				"href" => "/modules/car/assets/css/styles.css",
			)
		);
		

		// Component scripts.
		private $core = array(
			"/content/libraries/component/BaseComponent.js"
		);


		private $module = array(
			"module.js"
		);



		public function __construct() {
			parent::__construct("job-list");
			
			$this->addStyles($this->css);

			$scripts = array();
			
			foreach($this->core as $name) {
				$scripts [] = array("src" => $name);			
			}
			foreach($this->module as $name) {
				$scripts [] = array("src" => "/modules/jobs/assets/js/".$name);			
			}
			
			
			$this->addScripts($scripts);
		}
	

		
		public function formatResults($results, $config) {
			
			
			
		}
}