<?php

use File\File;
use Salesforce\Attachment;
use Http\HttpResponse;
use Http\HttpHeader;
use Http\Http;
use Http\HttpRequest;
use Salesforce\ContentDocument;


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

		$updatedJobRecords = $this->getContentDocument($jobRecords);

		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");

		return $tpl->render(array(
			"jobs" => $updatedJobRecords,
			"isAdmin" => true,
			"isMember" => false // is_authenticated()
		));
	}

	public function getContentDocument($jobRecords){

		$updatedJobRecords = array();

		foreach($jobRecords as $job){

			$jobId = $job["Id"];
			
			// Get the contentDocumentId
			$api = $this->loadForceApi();
			$query = "SELECT ContentDocumentId FROM ContentDocumentLink WHERE LinkedEntityId = '$jobId' LIMIT 1";
			$contentDocumentId = $api->query($query)->getRecord()["ContentDocumentId"];

			// Get the contentDocument
			$api = $this->loadForceApi();
			$query = "SELECT Id, Title FROM ContentDocument WHERE Id = '$contentDocumentId'";
			$contentDocument = $api->query($query)->getRecord();

			$job["ContentDocument"] = $contentDocument;

			$updatedJobRecords[] = $job;
		}


		return $updatedJobRecords;
	}
	

	// Return an HTML form for creating or updating a new Job posting.
	public function postingForm($job = null) {

		$isEdit = $job == null ? false : true;
		$attachments = $this->getAttachments($job["Id"]);
		$updatedJob = $this->getContentDocument(array($job))[0];

		$attachment = $attachments[0];

		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");
		
		return $tpl->render(array(
			"job" => $updatedJob,
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
		$existingContentDocumentId = $record->ContentDocumentId;
		unset($record->ContentDocumentId);
		unset($record->attachmentId);
		
		$record->OpenUntilFilled__c = $record->OpenUntilFilled__c == "" ? False : True;
		$record->IsActive__c = True;
		$recordId = $record->Id;

		$api = $this->loadForceApi();
		$resp = $api->upsert($sobjectName, $record);

		if(!$resp->isSuccess()) throw new Exception($resp->getErrorMessage());

		$jobId = $resp->getBody()["id"] != null ? $resp->getBody()["id"] : $recordId;

		$contentDocumentLinkId = $this->uploadContentDocument($jobId, $existingContentDocumentId, $files->getFirst());

		$resp = new HttpResponse();
		$resp->addHeader(new HttpHeader("Location", "/jobs"));

		return $resp;
	}

	public function uploadContentDocument($linkedEntityId, $contentDocumentId, $file) {

		$title = $file->getName();

		if($contentDocumentId == null){

			//  Create a new custom "ContentDocument" object by passing in the file, and setting the id's
			$doc = ContentDocument::fromFile($file);
			$doc->setLinkedEntityId($linkedEntityId);

			// Handles inserts and updates, for now only uploading one file.
			$contentDocumentLinkId = $this->insertContentDocument($doc);

		} else if($contentDocumentId != null){

			//  Create a new custom "ContentDocument" object by passing in the file, and setting the id's
			$doc = ContentDocument::fromFile($file);
			$doc->setContentDocumentId($contentDocumentId);

			$contentDocumentLinkId = $this->updateContentDocument($doc);
		}

		return $contentDocumentLinkId;
	}

	public function insertContentDocument($doc){

		$api = $this->loadForceApi();

		// Use "uploadFile" to upload a file as a Salesforce "ContentVersion" object.  A successful response contains the Id of the "ContentVersion" that was inserted.
		$resp = $api->uploadFile($doc);
		$contentVersionId = $resp->getBody()["id"];

		// Use the Id of the response to query for the "ContentVersion" object.  Then get the "ContentDocumentID" from the version.
		$api = $this->loadForceApi(); // For some reason the request method was stuck on "POST".  I will come back to this.
		$contentDocumentId = $api->query("SELECT ContentDocumentId FROM ContentVersion WHERE Id = '{$contentVersionId}'")->getRecords()[0]["ContentDocumentId"];
		
		// Create a standard class representing a Salesforce "ContentDocumentLink" object setting the "ContentDocumentId" to the Id of the "ContentDocument" that
		// was created when you inserted the "ContentVersion". 

		// Watch out for duplicates on the link object, because you dont have an Id field
		$link = new StdClass();
		$link->contentDocumentId = $contentDocumentId;
		$link->linkedEntityId = $doc->getLinkedEntityId();

		$resp = $api->upsert("ContentDocumentLink", $link);

		if(!$resp->isSuccess()){

			$message = $resp->getErrorMessage();
			throw new Exception($message);
		}

		return $resp->getBody()["id"];
	}

	public function updateContentDocument($doc){

		$api = $this->loadForceApi();

		// Use "uploadFile" to upload a file as a Salesforce "ContentVersion" object.  A successful response contains the Id of the "ContentVersion" that was inserted.
		$resp = $api->uploadFile($doc);
		$contentVersionId = $resp->getBody()["id"];

		if(!$resp->isSuccess()){

			$message = $resp->getErrorMessage();
			throw new Exception($message);
		}

		return $resp->getBody()["id"];
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

	public function downloadContentDocument($id){

		$api = $this->loadForceApi();

		$veriondataQuery = "SELECT Versiondata from ContentVersion WHERE ContentDocumentId = '$id' AND IsLatest = true";

		$versionData = $api->query($veriondataQuery)->getRecord()["VersionData"];

		$api2 = $this->loadForceApi();
		$resp = $api2->send($versionData);

		var_dump($api2, $resp);exit;

	}

	/////////////////////////	ATTACHMENT STUFF	////////////////////////////////////////////////////////////////////////

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

	public function getAttachments($jobId) {

		$api = $this->loadForceApi();
		
		$attResults = $api->query("SELECT Id, Name FROM Attachment WHERE ParentId = '{$jobId}'");

		return $attResults->getRecords();
	}

	public function getAttachment($id) {

		// Get the attachment object.
		$api = $this->loadForceApi();
		$results = $api->query("SELECT Id, Name, Body FROM Attachment WHERE Id = '{$id}'");
		$attachment = $results->getRecord();

		// Request the file content of the attachment using the blobfield endpoint returned in the "Body" field of the attachment.
		$endpoint = $attachment["Body"];
		$req = $this->loadForceApi();
		$resp = $req->send($endpoint);

		$file = new File($attachment["Name"]);
		$file->setContent($resp->getBody());
		$file->setType($resp->getHeader("Content-Type"));

		var_dump($req, $resp);

		exit;

		return $file;
	}
}