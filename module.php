<?php

use \File\File as File;

class JobsModule extends Module
{

	public function __construct() {

		parent::__construct();
	}


	public function home() {

		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");

		$force = $this->loadForceApi();
		
		//query for job records//
		$results = $force->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c ORDER BY PostingDate__c DESC");

		//creates an array containing each job record//
		$records = $results["records"];
		
		return $tpl->render(array(
			"jobs" => $records
		));
	}


	// Return an HTML form for creating a new Job posting.
	public function postingForm() {

		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");

		return $tpl->render();
	}

	public function createPosting() {

		var_dump($this->getRequest());exit;

		$jobId = $this->insertJob($body);

		header('Location: /jobs', true, 302);
	}

	public function insertJob() {

		$force = $this->loadForceApi();

		$req = $this->getRequest();
		$req->setFormRequest(true);
		$body = $req->getBody();

		if($body->Id == "") {
			
			unset($body->Id);
			$resp = json_decode($force->insert("Job__c", $body));

		} else {

			$resp = json_decode($force->updateRecordFromSession("Job__c", $body));
		}

		return $resp->id;
	}

	public function addAttachments(){

		print "hello from addAttachments"; exit;
	}


	public function deletePosting($Id) {

		$force = $this->loadForceApi();
		// Represents data submitted to endpoint, i.e., from an HTML form.
		
		//"Job__c is the name of the object I created in Salesforce//
		$obj = $force->deleteRecordFromSession("Job__c", $Id);

		//returning http response status 302 returns to homepage 
		header('Location: /jobs', true, 302);

		return $obj["records"][0]["Id"];
	}





	public function getAttachment($ContentVersionId, $filename = null) {

		$force = $this->loadForceApi();
		
		$resp = $force->getAttachment($ContentVersionId);
		

		$file = new File($filename);
		$file->setContent($resp->getBody());
		$file->setType($resp->getHeader("Content-Type"));

		
		// print get_class($file);
		// exit;


		return $file;

		// print $resp->getHeader("Content-Type");
		// exit;

		// print_r($resp->getHeaderCollection());
		// exit;


		// print $resp->getBody();
		// exit;

		return $resp;
		
	}


	public function showSubjects() {

		$results = MysqlDatabase::query("SELECT * FROM LibraryCategories");


		/*
			$array = (
				"<li class='document'>
						<a href='https://www.ocdla.org/members_only/motions/{$item["BaseFileName"]}'>
							{$item["Description
			);
			*/

		return implode("\n", $results->each(function ($item) {
			return "<li class='document'><a href='/documents/{$item["LibraryCategoryID"]}'>{$item["LibraryCategoryName"]}</a></li>";
		}));
	}


	public function showDocuments($catId) {

		$results = MysqlDatabase::query("SELECT * FROM Documents WHERE LibraryCategoryID = {$catId}");

		return "<div class='table'>" . implode("\n", $results->each(function ($doc) {
			return "<ul class='table-row'>
									<li class='table-cell'>
										<a target='_new' href='https://www.ocdla.org/members_only/motions/{$doc["BaseFileName"]}'>{$doc["BaseFileName"]}</a>
									</li>
									<li class='table-cell'>{$doc["Abstract"]}</li>
							</ul>";
		})) . "</div>";
	}

// 	public function login(){

// 		user_require_auth();
// 	}
}
