<?php
namespace Salesforce;


class Job__c extends SObject {

    public $Id;

    public $hasDocument;

    public $Name;

    public $Salary__c;

    public $PostingingDate__c;

    public $ClosingDate__c;

    public $Location__c;

    public $OpenUntilFilled__c;

    public $Attachments;

    
    public function __construct($id) {

        $this->Id = $id;
    }

    public static function fromJson($json){

        $obj = json_decode($json);

        $job = new Job__c($obj->id);

        return $job;
    }

    public static function fromArray($array){

        $job = new Job__c($array["Id"], $array["Name"], $array["Salary__c"], $array["PostingDate__c"], $array["ClosingDate__c"], $array["OpenUntilFilled__c"], $array["Attachments"]);

        return $job;
    }

    //method for adding a file, a method for getting a file should be able to get from job list module.php shold return an arrray of Jobb__c attachments not just basic attachments

    // Always produce an object that is compatible with the salesforce simple object endpoint.
    public function getSObject(){ 

        return array(
            "Name" => "some name"
        );
    }
}
