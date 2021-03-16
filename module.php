<?php
phpinfo();
// use Salesforce\Database as Database;
use \File\File as File;

class JobsModule extends Module
{
	/*TEST TRASH to help debug*/
	//var_dump($obj);
	//ini_set('display_errors', 1);
	//echo "Hello World!";
	//phpinfo();
	//exit;
	/*END TEST TRASH*/

	public function __construct()
	{
		parent::__construct();
	}


	public function home()
	{
		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");

		$force = $this->loadForceApi();
		//var_dump($force);
		//exit;
		//query for job records//
		$results = $force->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c ORDER BY PostingDate__c DESC");
		//var_dump($results);
		//exit;
		//creates an array containing each job record//
		$records = $results["records"];
		
		//instantiates an empty array to put the $recordIds in//
		$jobs = array(); 
		
		//puts records Id's into $recordsIds array//
		for($i = 0; $i < count($records); $i++) {
			$jobId = $records[$i]["Id"];
			$jobs[$jobId] = $records[$i];
		}
		//uses implode to put the id's in a string the seperator goes first//
		$Ids = implode("', '", array_keys($jobs)); 

		//saves-casts the $ids variable as a string in single quotes// 
		$Ids = "'$Ids'";

		//queries for documents//
		$docResults = $force->query("SELECT Id, LinkedEntityId, LinkedEntity.Name, ContentDocumentId, ContentDocument.Title, ContentDocument.OwnerId, ContentDocument.LatestPublishedVersionId, ContentDocument.FileExtension, ContentDocument.FileType FROM ContentDocumentLink WHERE LinkedEntityId IN ($Ids)");

		//creates an array holding each document//
		$documents = $docResults["records"];

		foreach($documents as $document) {
			$jobId = $document["LinkedEntityId"]; //puts each document linked idenity id into a single variable
			$job = &$jobs[$jobId]; //puts a job record and attached document by reference using $jobId as a key
			$job["Document"] = $document; //creates the document key and adds a document(if exists) to a job in the jobs array
		}
		
		return $tpl->render(array(
			"jobs" => $jobs
		));
	}


	/**
	 * Return an HTML form for creating a new Job posting.
	 */
	public function edit($Id = null)
	{
		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");


		$force = $this->loadForceApi();

		$Id = "'$Id'";

		//queries the datbase for selected record by Id//
		$result = $force->query("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c FROM Job__c WHERE Id = $Id");
		
		$record = $result["records"][0];


		//render job selected to edit//
		return $tpl->render(array("job" => $record));
	}


	public function deletePosting($Id)
	{
		$force = $this->loadForceApi();
		// Represents data submitted to endpoint, i.e., from an HTML form.
		
		//"Job__c is the name of the object I created in Salesforce//
		$obj = $force->delete("Job__c", $Id);

		//returning http response status 302 returns to homepage 
		header('Location: /jobs', true, 302);

		return $obj["records"][0]["Id"];
	}


	public function createPosting()
	{
		$force = $this->loadForceApi();

		// Represents data submitted to endpoint, i.e., from an HTML form.
		$req = $this->getRequest();
		$body = $req->getBody();
		$files = $req->getFiles();
		//var_dump($body);
		//exit;
		//"Job__c is the name of the Job sObject I created in Salesforce//
		if ($body->Id == "") {
			unset($body->Id);
			$obj = $force->insert("Job__c", $body);
		} else {
			$obj = $force->update("Job__c", $body);
		}
		
		//returning http response status 302 returns to homepage//
		header('Location: /jobs', true, 302);

		return $obj["records"][0]["Id"];
	}


	public function getAttachment($ContentVersionId, $filename = null)
	{
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

	public function showSubjects()
	{

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


	public function showDocuments($catId)
	{


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
}