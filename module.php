<?php

use \File\File as File;
use \Salesforce\Attachment as Attachment;

class JobsModule extends Module
{

	public function __construct() {

		parent::__construct();
	}

	// Queries salesforce for all "Job__c" objects and related docs/attachments, and renders the objects in a template.
	public function home() {

		$relatedSObjectName = "Attachment"; // Will come from configuration.
		$fKeyFieldName = $relatedSObjectName == "Attachment" || $relatedSObjectName == "ContentDocument" ? "ParentId" : "FolderId";

		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");

		$api = $this->loadForceApi();
		
		// Query for job records
		$jobResults = $api->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c WHERE IsActive__c = True ORDER BY PostingDate__c DESC");

		// Creates an array for holding "Job__c" objects.
		$jobRecords = $jobResults["records"];


		// What if there is more than one type of related sobjects for a job.  Do you want to show all attached sobjects?
		$jobs = array();
		foreach($jobRecords as $record){

			$recordId = $record["Id"];
			$attResults = $api->query("SELECT Id, Name FROM {$relatedSObjectName} Where {$fKeyFieldName} = '{$recordId}'");
			$record["attachments"] = $attResults["records"];

			$jobs[] = $record;
		}
		
		return $tpl->render(array(
			"jobs" => $jobs,
			"isAdmin" => true
		));
	}


	// Return an HTML form for creating or updating a new Job posting.
	public function postingForm($job = null) {

		$isEdit = $job == null ? false : true;
		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");

		return $tpl->render(array(
			"job" => $job,
			"isEdit" => $isEdit,
			"attachments" => $this->getAttachments($job["Id"])
		));
	}

	public function createPosting() {
		
		$req = $this->getRequest();

		$jobId = $this->upsertJob();

		if($req->getFiles()->size() > 0){

			$attachmentId = $this->insertAttachment($jobId);
		}

		header('Location: /jobs', true, 302);
	}

	// Gets form data from the request, inserts or updates a "Job__c" object, and returns the Id of the object.
	public function upsertJob() {

		$sobjectName = "Job__c";
		$api = $this->loadForceApi();

		$req = $this->getRequest();
		$record = $req->getBody();

		$resp = $api->upsert($sobjectName, $record);

		if(!$resp->isSuccess()){

			throw new Exception($resp->getErrorMessage());
		}

		$resp = json_decode($resp->getBody());

		return $resp->id;
	}

	// Get the FileList" object from the request, use the first file to build an "Attachment/File" object,
	// insert the Attachment, and return the id.
	public function insertAttachment($jobId){

		$fileClass = "Salesforce\Attachment"; // Will come from a configuration.

		$req = $this->getRequest();

		$file = $req->getFiles()->getFirst();
		$file = $fileClass::fromFile($file);
		$file->setParentId($jobId);

		$api = $this->loadForceApi();

		$resp = json_decode($api->uploadFile($file));
		return $resp->id;
	}

	public function edit($id){

		$api = $this->loadForceApi();

		$job = $api->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c WHERE Id = '{$id}'");
		
		return $this->postingForm($job["records"][0]);
	}

	public function delete($sobjectType, $id) {

		$api = $this->loadForceApi();

		$obj = $api->delete($sobjectType, $id);

		//returning http response status 302 returns to homepage 
		header('Location: /jobs', true, 302);
	}

	public function getAttachments($jobId) {

		$api = $this->loadForceApi();
		
		$attResults = $api->query("SELECT Id, Name FROM Attachment Where ParentId = '{$jobId}'");

		return $attResults["records"];
	}
}
