<?php


class JobsModule extends Module {


    public function __construct() {
        parent::__construct();
    }

		public function home() {
			return "<h2>OCDLA jobs app.</h2>";
		}
}
