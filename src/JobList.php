<?php

use Salesforce\ContentDocument;
use Salesforce\Attachment;
class JobList extends Module
{

    //member variables// 
    private $attachments = array();
    private $jobs;

    //constructor//
    public function __construct($records)
    {
        for ($i = 0; $i < count($records); $i++) {
            $Id = $records[$i]["Id"];
            $attachments = $records[$i]["Attachments"];
            if ($attachments != null) {
                $this->attachments[$Id] = new Attachment($attachments["records"][0]);
            }
        }
        $this->jobs = $records;
        parent::__construct();
    }


    public function getRecords()
    {
        return $this->jobs;
    }

    public function getAttachments($recordId)
    {
        return $this->attachments[$recordId];
    }

    public function getAllAttachments()
    {
        return $this->attachments;
    }

    /*type = attachment*/
    public function loadContentDocuments()
    {
        $api = $this->loadForceApi();
        $this->attachments = array(); //temporary?
        for ($i = 0; $i < count($this->jobs); $i++) {
            $jobId = $this->jobs[$i]["Id"];
            $jobs[$jobId] = $this->jobs[$i];
        }

        //uses implode to put the id's in a string the seperator goes first//
        $Ids = implode("', '", array_keys($jobs));

        //saves-casts the $ids variable as a string in single quotes// 
        $Ids = "'$Ids'";

        //queries for documents//
        $docResults = $api->query("SELECT Id, LinkedEntityId, LinkedEntity.Name, ContentDocumentId, ContentDocument.Title, ContentDocument.OwnerId, ContentDocument.LatestPublishedVersionId, ContentDocument.FileExtension, ContentDocument.FileType FROM ContentDocumentLink WHERE LinkedEntityId IN ($Ids)");

        $documents = $docResults->getRecords();

        //retrieves a documents "LinkedEntityId"//
        foreach ($documents as $document) {
            $linkedEntityId = $document["LinkedEntityId"];

            
            //replaces the current attachment with the content document
            $this->attachments[$linkedEntityId] = ContentDocument::newFromSalesforceRecord($document);
        }

    }
}