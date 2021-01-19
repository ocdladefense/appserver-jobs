<?php

// use Salesforce\Database as Database;
use \File\File as File;

class JobsModule extends Module
{
	/*TEST TRASH to help degub*/
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
		global $oauth_config;

		// Prepare data for the template.
		// $builder = QueryBuilder::fromJson($json);

		$tpl = new ListTemplate("job-list");
		$tpl->addPath(__DIR__ . "/templates");

		//$results = MysqlDatabase::query($builder->compile());
		$force = new Salesforce($oauth_config);

		$records = $force->createQueryFromSession("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c, (SELECT Id, Name FROM Attachments) FROM Job__c ORDER BY PostingDate__c DESC");

		//(SELECT Id, Title FROM Notes)
		$attachments = $records["records"];

		///////////////
		/*TEST TRASH*/
		//////////////
		$admin = new StdClass();
		$admin->Id = 1;
		$admin->name = "";
		$admin->password = "";
		$admin->profileId = 1;

		$member = new StdClass();
		$member->Id = 2;
		$member->name = "";
		$member->password = "";
		$member->profileId = 2;

		//change profileId to determine if actions is accessible//
		$user = new StdClass();
		$user->Id = 3;
		$user->name = "";
		$user->password = "";
		$user->profileId = 1;

		function isAdminUser($user, $admin)
		{
			return $user->profileId === $admin->profileId;
		}

		function isMemberUser($user, $member)
		{
			return $user->profileId === $member->profileId;
		}
		///////////////////
		/*END TEST TRASH*/
		//////////////////

		return $tpl->render(array(
			"jobs" => $records["records"], "isAdmin" => isAdminUser($user, $admin),
			"isMember" => isMemberUser($user, $member)
		));
	}


	/**
	 * Return an HTML form for creating a new Job posting.
	 */
	public function edit($Id = null)
	{
		global $oauth_config;

		$tpl = new Template("job-form");
		$tpl->addPath(__DIR__ . "/templates");

		///////////////
		/*TEST TRASH*/
		//GET USERS BY NAME AND PASSWORD GROUP BY ACCESS ~ eventually//
		/////////////
		$admin = new StdClass();
		$admin->Id = 1;
		$admin->name = "admin";
		$admin->password = "pass";
		$admin->profileId = 1;

		$member = new StdClass();
		$member->Id = 2;
		$member->name = "member";
		$member->password = "word";
		$member->profileId = 2;

		$user = new StdClass();
		$user->Id = 3;
		$user->name = "user";
		$user->password = "none";
		//change profileId to test edit access//
		$user->profileId = 1;

		function isAdmin($user, $admin)
		{
			return $user->profileId === $admin->profileId;
		}

		function isMember($user, $member)
		{
			return $user->profileId === $member->profileId;
		}

		if (!isAdmin($user, $admin) && !isMember($user, $member)) {
			throw new exception("Authorization not granted");
		}
		///////////////////
		/*END TEST TRASH*/
		//////////////////


		$force = new Salesforce($oauth_config);

		//queries the datbase for selected record by Id//
		$result = $force->createQueryFromSession("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c, (SELECT Id, Name FROM Attachments) FROM Job__c WHERE Id = '" . $Id . "'");
		$record = $result["records"][0];
		//var_dump($record);
		//exit;

		//render job selected to edit//
		return $tpl->render(array("job" => $record));
	}


	public function deletePosting($Id)
	{
		global $oauth_config;
		// Represents data submitted to endpoint, i.e., from an HTML form.

		$req = $this->getRequest();

		$force = new Salesforce($oauth_config);

		//"Job__c is the name of the object I created in Salesforce//
		$obj = $force->deleteRecordFromSession("Job__c", $Id);

		//returning http response status 302 returns to homepage 
		header('Location: /jobs', true, 302);

		return $obj["records"][0]["Id"];
	}


	public function createPosting()
	{
		global $oauth_config;

		// Represents data submitted to endpoint, i.e., from an HTML form.
		$req = $this->getRequest();
		$body = $req->getBody();
		$force = new Salesforce($oauth_config);

		//"Job__c is the name of the Job sObject I created in Salesforce//
		if ($body->Id == "") {
			unset($body->Id);
			$obj = $force->createRecordsFromSession("Job__c", $body);
		} else {
			$obj = $force->updateRecordFromSession("Job__c", $body);
		}
		//var_dump($body);
		//exit;
		//returning http response status 302 returns to homepage//
		header('Location: /jobs', true, 302);

		return $obj["records"][0]["Id"];
	}


	public function getAttachment($id, $filename = null)
	{
		global $oauth_config;

		// $url = "/services/data/v49.0/sobjects/Attachment/00P5b00000rk39CEAQ";
		$force = new Salesforce($oauth_config);

		$resp = $force->getAttachment($id);


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
