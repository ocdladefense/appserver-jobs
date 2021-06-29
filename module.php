<?php

use File\File;
use Salesforce\Attachment;
use Http\HttpResponse;
use Http\HttpHeader;


class JobsModule extends Module
{

	public function __construct() {
		
		parent::__construct();
	}

	public function home() {

		$api = $this->loadForceApi();
		
		$query = "SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c, (SELECT Id, Name FROM Attachments) FROM Job__c ORDER BY PostingDate__c DESC";
		
		$resp = $api->query($query);

		if(!$resp->isSuccess()) throw new Exception($resp->getErrorMessage());

		$jobRecords = $resp->getRecords();

		$jobRecords = $this->includeRecordAttachments($jobRecords);

		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");

		return $tpl->render(array(
			"jobs" => $jobRecords,
			"isAdmin" => false,
			"isMember" => true // is_authenticated()
		));
	}

	public function includeRecordAttachments($jobRecords){

		$api = $this->loadForceApi();

		$relatedSObjectName = "Attachment"; // Will come from configuration.
		$fKeyFieldName = $relatedSObjectName == "Attachment" || $relatedSObjectName == "ContentDocument" ? "ParentId" : "FolderId";

		$jobs = array();
		foreach($jobRecords as $record){

			$recordId = $record["Id"];
			$attResults = $api->query("SELECT Id, Name FROM {$relatedSObjectName} WHERE {$fKeyFieldName} = '{$recordId}'");
			$record["attachments"] = $attResults->getRecords();

			$jobs[] = $record;
		}

		return $jobs;
	}


	// Return an HTML form for creating or updating a new Job posting.
	public function postingForm($job = null) {

		$isEdit = $job == null ? false : true;
		$attachments = $this->getAttachments($job["Id"]);
		$attachment = $attachments[0];

		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");

		return $tpl->render(array(
			"job" => $job,
			"isEdit" => $isEdit,
			"attachment" => $attachment
		));
	}


	// Gets form data from the request, inserts or updates a "Job__c" object, and returns the Id of the object.
	public function createPosting() {

		$sobjectName = "Job__c";
		$req = $this->getRequest();

		$files = $req->getFiles();
		$numberOfFiles = $files->size();

		$record = $req->getBody();
		$existingAttachmentId = $record->attachmentId;
		unset($record->attachmentId);
		
		$record->OpenUntilFilled__c = $record->OpenUntilFilled__c == "" ? False : True;
		$record->IsActive__c = True;
		$recordId = $record->Id;

		$api = $this->loadForceApi();
		$resp = $api->upsert($sobjectName, $record);

		if(!$resp->isSuccess()) throw new Exception($resp->getErrorMessage());

		$jobId = $resp->getBody()["id"] != null ? $resp->getBody()["id"] : $recordId;

		if($numberOfFiles > 0){
			
			if($existingAttachmentId != null){
				
				$this->delete("Attachment", $existingAttachmentId);
			}

			$attachmentId = $this->insertAttachment($jobId, $files->getFirst());
		}

		$resp = new HttpResponse();
		$resp->addHeader(new HttpHeader("Location", "/jobs"));

		return $resp;
	}

	// Get the FileList" object from the request, use the first file to build an "Attachment/File" object,
	// insert the Attachment, and return the id.
	public function insertAttachment($jobId, $file){

		if($jobId == null) throw new Exception("ERROR_ADDING_ATTACHMENT:  The job id can not be null when adding attachments.");

		$fileClass = "Salesforce\Attachment";

		$file = $fileClass::fromFile($file);
		$file->setParentId($jobId);

		$api = $this->loadForceApi();

		$resp = $api->uploadFile($file);

		if(!$resp->isSuccess()) throw new Exception($resp->getErrorMessage());

		$attachment = $fileClass::fromArray($resp->getBody());

		return $attachment->Id;
	}

	public function edit($id){

		$api = $this->loadForceApi();

		$resp = $api->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c WHERE Id = '{$id}'");
		
		return $this->postingForm($resp->getRecord());
	}

	public function delete($sobjectType, $id) {

		$api = $this->loadForceApi();

		$obj = $api->delete($sobjectType, $id);

		$resp = new HttpResponse();
		$resp->addHeader(new HttpHeader("Location", "/jobs"));

		return $resp;
	}

	public function getAttachments($jobId) {

		$api = $this->loadForceApi();
		
		$attResults = $api->query("SELECT Id, Name FROM Attachment WHERE ParentId = '{$jobId}'");

		return $attResults->getRecords();
	}

	public function getAttachment($id) {

		$api = $this->loadForceApi();

		$results = $api->query("SELECT Id, Name FROM Attachment WHERE Id = '{$id}'");

		$attachment = $results->getRecord();

		$resp = $api->getAttachment($id);

		$file = new File($attachment["Name"]);
		$file->setContent($resp->getBody());
		$file->setType($resp->getHeader("Content-Type"));

		return $file;
	}
}