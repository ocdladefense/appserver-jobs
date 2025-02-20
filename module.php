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


	const UPLOAD_RELATED_FILES = false;


	public function __construct() {
		
		parent::__construct();
	}



	public function list($list = null) {

		$user = current_user();

		$api = loadApi();

		$today = new DateTime();
		$removalDate = $today->modify("-3 days");
		$removalDate = $removalDate->format("Y-m-d");

		$today = new DateTime();
		$openUntilFilledDeadline = $today->modify("-42 days");
		$openUntilFilledDeadline = $openUntilFilledDeadline->format("Y-m-d");

		
		$query = "SELECT Id, Name, Salary__c, CreatedById, PostingDate__c, Organization__c, ClosingDate__c, Location__c, OpenUntilFilled__c, AttachmentUrl__c, (SELECT Id, Name FROM Attachments) FROM Job__c";
		
		if(!$user->isAdmin()) $query .= " WHERE IsActive__c = True AND ((OpenUntilFilled__c = False AND ClosingDate__c >= $removalDate) OR (OpenUntilFilled__c = true AND postingDate__c >= $openUntilFilledDeadline))";

		$query .= " ORDER BY PostingDate__c DESC";

		
		$resp = $api->query($query);

		if(!$resp->isSuccess()) {
			throw new Exception($resp->getErrorMessage());
		}

		$jobRecords = $resp->getRecords();

		$service = new FileServiceModule();
		$updatedJobRecords = $service->getContentDocument($jobRecords);

/*
			
		<?php if(!isset($jobs) || (isset($jobs) && count($jobs) < 1)): ?>
			<ul class="table-row">
				<li>There are no current job postings.</li>
			</ul>
			
		<?php else: ?>
*/


		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");



		return $tpl->render(array(
			"jobs" => $updatedJobRecords,
			"user" => $user
		));
	}


	
	

	// Return an HTML form for creating or updating a new Job posting.
	public function postingForm($job = null) {

		$service = new FileServiceModule();

		$isEdit = $job == null ? false : true;
		$attachments = $service->getAttachments($job["Id"]);

		
		$updatedJob = $service->getContentDocument(array($job))[0];

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

		$api = $this->loadForceApi();
		$req = $this->getRequest();

		$files = $req->getFiles();
		$numberOfFiles = $files->size();

		$record = $req->getBody();
		$existingAttachmentId = $record->attachmentId;
		$existingContentDocumentId = $record->ContentDocumentId;
		unset($record->ContentDocumentId);
		unset($record->attachmentId);
		
		$record->OpenUntilFilled__c = $record->OpenUntilFilled__c == "" ? False : True;

		if($record->OpenUntilFilled__c == true || empty($record->ClosingDate__c)) {

			unset($record->ClosingDate__c);
			$record->OpenUntilFilled__c = true;
		}

		$record->IsActive__c = True;
		$recordId = $record->Id;

		
		$resp = $api->upsert("Job__c", $record);
		$body = $resp->getBody();

		if(!$resp->isSuccess()) {
			throw new Exception($resp->getErrorMessage());
		}

		$jobId = $body["id"] ?? $recordId;

		if(self::UPLOAD_RELATED_FILES && $numberOfFiles > 0) {

			$contentDocumentLinkId = $this->uploadContentDocument($jobId, $existingContentDocumentId, $files->getFirst());
		}

		$resp = new HttpResponse();
		$resp->addHeader(new HttpHeader("Location", "/jobs"));

		return $resp;
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



	


	/**
	 * These function should be moved to file-upload module.
	 */
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




	public function insertContentDocument($doc) {

		
		// Pass true as the second parameter to force the usernamepassword flow.	
		$instanceUrl = cache_get("instance_url");
		$accessToken = cache_get("access_token");
		$client = new Salesforce\HttpClient($instanceUrl,$accessToken);



		$req = new Salesforce\FileUploadRequest($doc);

		

		$resp = $client->send($req);
		$body = $resp->getBody();

		// Use "uploadFile" to upload a file as a Salesforce "ContentVersion" object.  A successful response contains the Id of the "ContentVersion" that was inserted.
		// $resp = $api->uploadFile($doc);
		$contentVersionId = $body["id"];

		// Use the Id of the response to query for the "ContentVersion" object.  Then get the "ContentDocumentID" from the version.
		// $api = $this->loadForceApiFromFlow("usernamepassword"); // This code should be removed
		// in favor of instantiating the HttpClient class.

		// TODO: figure out why this code worked;
		// Specifically, find the loadForceApiFromFlow.
		// $api = $this->loadForceApiFromFlow("usernamepassword");
		$req = new SOQLQueryRequest("SELECT ContentDocumentId FROM ContentVersion WHERE Id = '{$contentVersionId}'");
		$resp = $client->send($req);
		$record = $resp->getRecord();
		// $records = $resp->getRecords()[0]["ContentDocumentId");
		$contentDocumentId = $record["ContentDocumentId"];
		
		// Create a standard class representing a Salesforce "ContentDocumentLink" object setting the "ContentDocumentId" to the Id of the "ContentDocument" that
		// was created when you inserted the "ContentVersion". 

		// Watch out for duplicates on the link object, because you dont have an Id field
		$link = new StdClass();
		$link->contentDocumentId = $contentDocumentId;
		$link->linkedEntityId = $doc->getLinkedEntityId();
		$link->visibility = "AllUsers";

		$req = SObjectRequest("ContentDocumentLink");
		$req->upsert($link);
		$resp = $client->send($req);
		$body = $resp->getBody();
		// $resp = $api->upsert("ContentDocumentLink", $link);

		
		

		return !$resp->isSuccess() ? null : $body["id"];
	}




	public function updateContentDocument($doc){

		$api = loadApi();

		// Use "uploadFile" to upload a file as a Salesforce "ContentVersion" object.  A successful response contains the Id of the "ContentVersion" that was inserted.
		$resp = $api->uploadFile($doc);

		$contentVersionId = $resp->getBody()["id"];

		if(!$resp->isSuccess()){

			$message = $resp->getErrorMessage();
			throw new Exception($message);
		}

		return $resp->getBody()["id"];
	}


}