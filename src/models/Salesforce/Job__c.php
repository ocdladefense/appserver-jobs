<?php
namespace Salesforce;

class Job__c extends SObject {

    public $Location__c;

    public $Salary__c;



    public function __construct($id) {

        $this->Id = $id;
    }


    // Always produce an object that is compatible with the salesforce simple object endpoint.
    public function getSObject(){ 

        return array(
            "Name" => $this->getName(),
            "ParentId" => $this->ParentId
        );
    }
}
