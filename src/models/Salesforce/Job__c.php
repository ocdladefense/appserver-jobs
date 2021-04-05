<?php
namespace Salesforce;


class Job__c extends SObject {

    public $Id;

    public function __construct($id) {

        $this->Id = $id;
    }

    public static function fromArray($objArray){

        $job = new Job__c($objArray["id"]);

        return $job;
    }


    // Always produce an object that is compatible with the salesforce simple object endpoint.
    public function getSObject(){ 

        return array(
            "Name" => "some name"
        );
    }
}
