<?php

use File\File as File;
use Salesforce\Attachment as Attachment;
use Salesforce\Job__c as Job__c;

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
			$attResults = $api->query("SELECT Id, Name FROM {$relatedSObjectName} WHERE {$fKeyFieldName} = '{$recordId}'");
			$record["attachments"] = $attResults["records"];

			$jobs[] = $record;
		}
		
		return $tpl->render(array(
			"jobs" => $jobs,
			"isAdmin" => false,
			"isMember" => is_authenticated()
		));
	}


	// Return an HTML form for creating or updating a new Job posting.
	public function postingForm($job = null) {

		$isEdit = $job == null ? false : true;
		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");
		$attachments = $this->getAttachments($job["Id"]);
		$attachment = $attachments[0];

		return $tpl->render(array(
			"job" => $job,
			"isEdit" => $isEdit,
			"attachment" => $attachment
		));
	}


	// Gets form data from the request, inserts or updates a "Job__c" object, and returns the Id of the object.
	public function createPosting() {

		$sobjectName = "Job__c";
		$api = $this->loadForceApi();

		$req = $this->getRequest();
		$fileList = $req->getFiles();
		$numberOfFiles = $fileList->size();
		$record = $req->getBody();
		$existingAttachmentId = $record->attachmentId;
		unset($record->attachmentId);
		
		$record->OpenUntilFilled__c = $record->OpenUntilFilled__c == "" ? False : True;
		$record->IsActive__c = True;
		$jobId = $record->Id;

		$resp = $api->upsert($sobjectName, $record);

		if(!$resp->isSuccess()){

			$message = $resp->getErrorMessage();
			throw new Exception($message);
		}

		$job = null != $jobId ? new Job__c($jobId) : Job__c::fromJson($resp->getBody());

		$jobId = $job->Id;

		if($numberOfFiles > 0){
			
			if($existingAttachmentId != null){
				
				$this->delete("Attachment", $existingAttachmentId);
			}

			$attachmentId = $this->insertAttachment($jobId, $fileList->getFirst());
		}

		header('Location: /jobs', true, 302);
	}

	// Get the FileList" object from the request, use the first file to build an "Attachment/File" object,
	// insert the Attachment, and return the id.
	public function insertAttachment($jobId, $file){

		$fileClass = "Salesforce\Attachment"; // Will come from a configuration.

		$file = $fileClass::fromFile($file);
		$file->setParentId($jobId);

		$api = $this->loadForceApi();

		$resp = $api->uploadFile($file);

		if(!$resp->isSuccess()){

			$message = $resp->getErrorMessage();
			throw new Exception($message);
		}

		$attachment = $fileClass::fromJson($resp->getBody());

		return $attachment->Id;
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
		
		$attResults = $api->query("SELECT Id, Name FROM Attachment WHERE ParentId = '{$jobId}'");

		return $attResults["records"];
	}

	public function getAttachment($id) {

		$api = $this->loadForceApi();

		$results = $api->query("SELECT Id, Name FROM Attachment WHERE Id = '{$id}'");

		$attachment = $results["records"][0];

		$resp = $api->getAttachment($id);

		$file = new File($attachment["Name"]);
		$file->setContent($resp->getBody());
		$file->setType($resp->getHeader("Content-Type"));

		return $file;
	}

}
