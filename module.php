<?php

// use Salesforce\Database as Database;
use \File\File as File;

class JobsModule extends Module {


    public function __construct() {
        parent::__construct();
    }



		public function home() {
			global $oauth_config;

			// Prepare data for the template.
			// $builder = QueryBuilder::fromJson($json);
		
			$tpl = new ListTemplate("job-list");
			$tpl->addPath(__DIR__ . "/templates");

			// $results = MysqlDatabase::query($builder->compile());
			$force = new Salesforce($oauth_config);
			$records = $force->createQueryFromSession("SELECT Id, Name, Salary__c, PostingDate__c, ClosingDate__c, Location__c, OpenUntilFilled__c, (SELECT Id, Name FROM Attachments) FROM Job__c ORDER BY PostingDate__c DESC");

			
			// (SELECT Id, Title FROM Notes)
			$attachments = $records["records"];


			return $tpl->render(array("jobs" => $records["records"]));
		}
		
		
		/**
		 * Return an HTML form for creating a new Job posting.
		 */
		public function edit() {
			$tpl = new Template("job-form");
			$tpl->addPath(__DIR__ . "/templates");
			
			
			return $tpl->render();
		}
		
		
		public function updatePosting() {
		
		}
		
		
		public function deletePosting() {
		
		}
		
		
		public function createPosting() {
			/*
			// Represents data submitted to endpoint, i.e., from an HTML form.
			$req => $this->getRequest();
		
			$force = new Salesforce($oauth_config);
			
			$obj = $force->createRecordFromSession("Job__c", $req->getBody());
			
			return $obj["records"][0]["Id"];
			*/
		}
		
		
		
		public function getAttachment($id, $filename = null) {
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
		
		
		public function showSubjects() {
		
			$results = MysqlDatabase::query("SELECT * FROM LibraryCategories");
			
			
			/*
			$array = (
				"<li class='document'>
						<a href='https://www.ocdla.org/members_only/motions/{$item["BaseFileName"]}'>
							{$item["Description
			);
			*/
			
			return implode("\n",$results->each(function($item) {
				return "<li class='document'><a href='/documents/{$item["LibraryCategoryID"]}'>{$item["LibraryCategoryName"]}</a></li>";
			}));
		}
		
		
		public function showDocuments($catId) {
		
		
			$results = MysqlDatabase::query("SELECT * FROM Documents WHERE LibraryCategoryID = {$catId}");
			
			return "<div class='table'>" . implode("\n",$results->each(function($doc) {
				return "<ul class='table-row'>
									<li class='table-cell'>
										<a target='_new' href='https://www.ocdla.org/members_only/motions/{$doc["BaseFileName"]}'>{$doc["BaseFileName"]}</a>
									</li>
									<li class='table-cell'>{$doc["Abstract"]}</li>
							</ul>";
			}))."</div>";
		}
}
