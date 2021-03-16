<?php

use \File\File as File;
use \Salesforce\Attachment;

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
		$jobResults = $force->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c ORDER BY PostingDate__c DESC");

		//creates an array containing each job record//
		$jobRecords = $jobResults["records"];

		$jobs = array();
		foreach($jobRecords as $record){

			$recordId = $record["Id"];
			$attResults = $force->query("SELECT Id, Name FROM Attachment Where ParentId = '{$recordId}'");
			$record["attachments"] = $attResults["records"];

			$jobs[] = $record;
		}
		
		return $tpl->render(array(
			"jobs" => $jobs
		));
	}


	// Return an HTML form for creating a new Job posting.
	public function postingForm() {

		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");

		return $tpl->render();
	}

	public function createPosting() {
		
		$req = $this->getRequest();

		$jobId = $this->upsertJob();

		if($req->getFiles()->size() > 0){

			$attachmentId = $this->insertAttachment($jobId);
		}

		header('Location: /jobs', true, 302);
	}

	public function upsertJob() {

		$force = $this->loadForceApi();

		$req = $this->getRequest();
		$body = $req->getBody();

		if($body->Id == "") {
			
			unset($body->Id);
			$resp = json_decode($force->insert("Job__c", $body));

		} else {

			$resp = json_decode($force->updateRecordFromSession("Job__c", $body));
		}

		return $resp->id;
	}

	public function insertAttachment($jobId){

		$req = $this->getRequest();
		$file = $req->getFiles()->getFirst();

		$file = Attachment::fromFile($file);
		$file->setParentId($jobId);

		$sfRequest = $this->loadForceApi();

		$resp = json_decode($sfRequest->uploadFile($file));

		return $resp->id;
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
