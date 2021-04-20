<?PHP

use Salesforce\ContentDocument;

class JobList extends Module
{

    //member variables// 
    private $attachments = array();
    //private $contentDocuments = array();
    private $jobs;
    //private $count;

    //constructor//
    public function __construct($records)
    {
        for ($i = 0; $i < count($records); $i++) {
            $Id = $records[$i]["Id"];
            $attachments = $records[$i]["Attachments"];
            if ($attachments != null) {
                $this->attachments[$Id] = $attachments["records"];
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

    public function getAllAttachments(){
        return $this->attachments;
    }

    /*type = attachment*/
    public function loadContentDocuments()
    {
        $api = $this->loadForceApi();

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
            

            //compares $jobId to $linkedEntityId if they are equal enter $contentDocument record in that job record//
            // for ($i = 0; $i < count($this->jobs); $i++) {
            //     $jobId = $this->jobs[$i]["Id"];
            //     if ($jobId == $linkedEntityId) {
            //         $this->jobs[$i]["ContentDocument"] = $document;
            //     }
            // }
            //adds each document to $contentDocuments array//
            $this->attachments[$linkedEntityId] = $document;
        }
    }
}
