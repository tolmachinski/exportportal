<?php

namespace App\ZohoCRM;

use zcrmsdk\crm\crud\ZCRMInventoryLineItem;
use zcrmsdk\crm\crud\ZCRMJunctionRecord;
use zcrmsdk\crm\crud\ZCRMNote;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\oauth\ZohoOAuth;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use App\ZohoCRM\Constants;
use zcrmsdk\crm\api\APIRequest;
use zcrmsdk\crm\api\handler\APIHandler;
use zcrmsdk\crm\api\response\APIResponse;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\crud\ZCRMModule;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class Record
{
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        string $userEmail,
        ?string $accessType = 'offline',
        ?string $cacheDir = null
    ) {
        $configuration = array(
            'client_id'              => $clientId,
            'client_secret'          => $clientSecret,
            'redirect_uri'           => $redirectUrl,
            'currentUserEmail'       => $userEmail,
            'token_persistence_path' => $cacheDir ?? dirname(dirname(__DIR__)),
            'access_type'            => $accessType ?? 'offline',
        );

        ZCRMRestClient::initialize($configuration);
    }


    /**
     * Returns the list of the Zoho records for provided email and module.
     *
     * @param string $email
     * @param string $module
     *
     * @return ZCRMRecord[]
     *
     * @link https://www.zoho.com/crm/developer/docs/api/search-records.html
     */
    public function get_records_by_email(string $email, string $module): array
    {
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);

        try {
            return $moduleIns->searchRecordsByEmail($email, 1, 200)->getData();
        } catch (ZCRMException $exception) {
            return array();
        }
    }

    /**
     * @param int $id_entity
     * @param string $module
     *
     * @return APIResponse
     *
     * @link https://www.zoho.com/crm/developer/docs/api/delete-specific-record.html
     */
    public function delete_record_by_id_entity($id_entity, $module)
    {
        $zcrmRecordIns = ZCRMRecord::getInstance($module, $id_entity);
        $apiResponse = $zcrmRecordIns->delete();

        return $apiResponse;
    }

    public function create($user_data = array(), $module)
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance($module, null); // To get record instance

        $this->set_contact_fields($record, $user_data);

        return $record->create();
    }

    public function createBulk($users = array(), $module)
    {
        $module_instance = ZCRMRestClient::getInstance()->getModuleInstance($module);
        $records = array();

        foreach ($users as $user) {
            $record = ZCRMRecord::getInstance($module, null);

            $this->set_contact_fields($record, $user);

            $records[] = $record;
        }

        return $module_instance->createRecords($records);
    }

    public function update($id_contact, $contact_data)
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('contacts', $id_contact);

        $this->set_contact_fields($record, $contact_data);

        return $record->update();
    }

    public function update_records($records, $module)
    {
        $moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);

        $updates = array();

        foreach ($records as $id_entity => $entity_data) {
            $record = ZCRMRestClient::getInstance()->getRecordInstance($module, $id_entity);

            $this->set_contact_fields($record, $entity_data);

            $updates[] = $record;
        }


        return $moduleIns->updateRecords($updates);
    }

    public function convert_lead($id_lead, $preserve_owner = false)
    {
        try {
            $record = ZCRMRecord::getInstance(Constants::MODULE_LEADS, $id_lead);

            $details = null;

            if ($preserve_owner) {
                $lead = ZCRMModule::getInstance(Constants::MODULE_LEADS)->getRecord($id_lead)->getData();
                $lead_owner_id = $lead->getOwner()->getId();

                $details = array('assign_to' => $lead_owner_id);
            }

            $response = $record->convert(null, $details);

            return array('status' => 'success', 'contact_id' => (int) $response['Contacts']);
        } catch (ZCRMException $e) {
            return array('status' => 'error', 'Details: ' => $e->getExceptionDetails(), 'message' => $e->getMessage(), 'code' => $e->getExceptionCode());
        }
    }

    public function delete()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $responseIns = $record->delete();

        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . json_encode($responseIns->getDetails());
    }

    public function convert()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('Leads', '3524033000001192011'); // To get record instance
        $deal = ZCRMRecord::getInstance('deals', null); // to get the record of deal in form of ZCRMRecord insatnce
        $deal->setFieldValue('Deal_Name', 'test3'); // to set the deal name
        $deal->setFieldValue('Stage', 'Qualification'); // to set the stage
        $deal->setFieldValue('Closing_Date', '2016-03-30'); // to set the closing date
        $details = array('overwrite'=>true, 'notify_lead_owner'=>true, 'notify_new_entity_owner'=>true, 'Accounts'=>'3524033000001055001', 'Contacts'=>'3524033000001248867', 'assign_to'=>'3524033000000191017');
        $responseIn = $record->convert($deal, $details); // to convert record
        echo 'HTTP Status Code:' . $responseIn->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIn->getStatus(); // To get response status
        echo 'Message:' . $responseIn->getMessage(); // To get response message
        echo 'Code:' . $responseIn->getCode(); // To get status code
        echo 'Details:' . json_encode($responseIn->getDetails());
    }

    public function getRelatedListRecords()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $relatedlistrecords = $record->getRelatedListRecords('Attachments')->getData(); // to get the related list records in form of ZCRMRecord instance

        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('File_Name'); // to get the file name
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
        }
        $relatedlistrecords = $record->getRelatedListRecords('Products')->getData(); // to get the related list record inform of ZCRMRecord instance
        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
            echo $relatedlistrecord->getFieldValue('Product_Name'); // to get the product name
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('Product_Code'); // to get the product code
        }
        $relatedlistrecords = $record->getRelatedListRecords('Activities')->getData(); // to get the related list record inform of ZCRMRecord instance
        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('Subject'); // to get the subject of the activity
            echo $relatedlistrecord->getFieldValue('Due_Date'); // to get the due date of the activity
            echo $relatedlistrecord->getFieldValue('Billable'); // to get the billable value
            echo $relatedlistrecord->getFieldValue('Activity_Type'); // to get the activity type
        }
        $relatedlistrecords = $record->getRelatedListRecords('Campaigns')->getData(); // to get the related list record inform of ZCRMRecord instance
        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('Campaign_Name'); // to get the campaigns name
            echo $relatedlistrecord->getFieldValue('Description'); // to get the campaign's description
            echo $relatedlistrecord->getFieldValue('Member_Status'); // to get the member status
        }
        $relatedlistrecords = $record->getRelatedListRecords('Quotes')->getData(); // to get the related list record inform of ZCRMRecord instance

        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('Carrier'); // to get the carrier
            echo $relatedlistrecord->getFieldValue('Quote_Stage'); // to get the quote stage
            echo $relatedlistrecord->getFieldValue('Subject'); // to get the quote subject
            echo $relatedlistrecord->getFieldValue('Quote_Number'); // to get the quote number
            echo $relatedlistrecord->getFieldValue('currency_symbol'); // to get the currency symbol
        }
        $relatedlistrecords = $record->getRelatedListRecords('SalesOrders')->getData(); // to get the related list record inform of ZCRMRecord instance

        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('Carrier'); // to get the carrier
            echo $relatedlistrecord->getFieldValue('Status'); // to get the status of the sales order
            echo $relatedlistrecord->getFieldValue('Billing_Street'); // to get the billing street
            echo $relatedlistrecord->getFieldValue('Billing_Code'); // to get the billing code
            echo $relatedlistrecord->getFieldValue('Subject'); // to get the subject
            echo $relatedlistrecord->getFieldValue('Billing_City'); // to get the billing city
            echo $relatedlistrecord->getFieldValue('SO_Number'); // to get the sales order number
            echo $relatedlistrecord->getFieldValue('Billing_State'); // to get the billing state
        }
        $relatedlistrecords = $record->getRelatedListRecords('Cases')->getData(); // to get the related list record inform of ZCRMRecord instance
        foreach ($relatedlistrecords as $relatedlistrecord) {
            echo $relatedlistrecord->getModuleApiName(); // to get the api name of the module
            echo $relatedlistrecord->getEntityId(); // to get the entity id
            echo $relatedlistrecord->getFieldValue('Status'); // to get the status of the case
            echo $relatedlistrecord->getFieldValue('Email'); // to get the email id
            echo $relatedlistrecord->getFieldValue('Case_Origin'); // to get the case origin
            echo $relatedlistrecord->getFieldValue('Case_Number'); // to get the case number
        }
    }

    public function addlineitemtoexistingrecord()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance

        $lineItem = ZCRMInventoryLineItem::getInstance(null); // To get ZCRMInventoryLineItem instance
        $lineItem->setDescription('Product_description'); // To set line item description
        $lineItem->setDiscount(5); // To set line item discount
        $lineItem->setListPrice(100); // To set line item list price
        $lineItem->setProduct(ZCRMRecord::getInstance('Products', '{record_id}')); // To set product to line item
        $lineItem->setQuantity(100);
        $responseIns = $record->addLineItemtoExistingRecord($lineItem);
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function updatelineitemofexistingrecord()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance

        $lineItem = ZCRMInventoryLineItem::getInstance(null); // To get ZCRMInventoryLineItem instance
        $lineItem->setId('{line_item_id}');
        $lineItem->setDescription('Product_scription'); // To set line item description
        $lineItem->setDiscount(5); // To set line item discount
        $lineItem->setListPrice(12312); // To set line item list price
        $lineItem->setProduct(ZCRMRecord::getInstance('Products', '{record_id}')); // To set product to line item
        $lineItem->setQuantity(100);
        $responseIns = $record->updateLineItemofTheExistingRecord($lineItem);
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function deletelineitemfromexistingrecord()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $responseIns = $record->deleteLineItemFromTheExistingRecord('{line_item_id}');
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function getNotes()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $notes = $record->getNotes()->getData(); // to get the notes in form of ZCRMNote instances array
        foreach ($notes as $note) {
            echo "\n";
            echo $note->getId(); // To get note id
            echo $note->getTitle(); // To get note title
            echo $note->getContent(); // To get note content
            $parentRecord = $note->getParentRecord(); // To get note's parent record
            echo $parentRecord->getEntityId(); // To get note's parent record id
            echo $note->getParentName(); // To get note's parent name
            echo $note->getParentId(); // To get note's parent id
            $createdBy = $note->getCreatedBy();
            echo $createdBy->getId(); // To get user_id who created the note
            echo $createdBy->getName(); // To get user name who created the note
            $modifiedBy = $note->getModifiedBy();
            echo $modifiedBy->getId(); // To get user_id who modified the note
            echo $modifiedBy->getName(); // To get user name who modified the note
            $owner = $note->getOwner();
            echo $owner->getId(); // To get note_record owner id
            echo $owner->getName(); // To get note_record Owner name
            echo $note->getCreatedTime(); // To get created time of the note
            echo $note->getModifiedTime(); // To get modified time of the note
            echo $note->isVoiceNote(); // Check if the note is voice_note or not
            echo $note->getSize(); // To get note_record size
            $attchments = $note->getAttachments(); // To get attachments of the note_record
            if (null != $attchments) { // check If attachments is empty/not
                foreach ($attchments as $attchmentIns) {
                    echo $attchmentIns->getId(); // To get the note's attachment id
                    echo $attchmentIns->getFileName(); // To get the note's attachment file name
                    echo $attchmentIns->getFileType(); // To get the note's attachment file type
                    echo $attchmentIns->getSize(); // To get the note's attachment file size
                    echo $attchmentIns->getParentModule(); // To get the note's attachment parent module name
                    $parentRecord = $attchmentIns->getParentRecord();
                    echo $parentRecord->getEntityId(); // To get the note's parent record id
                    echo $attchmentIns->getParentName(); // To get the note name
                    echo $attchmentIns->getParentId(); // To get the note id
                    $createdBy = $attchmentIns->getCreatedBy();
                    echo $createdBy->getId(); // To get user_id who created the note's attachment
                    echo $createdBy->getName(); // To get user name who created the note's attachment
                    $modifiedBy = $attchmentIns->getModifiedBy();
                    echo $modifiedBy->getId(); // To get user_id who modified the note's attachment
                    echo $modifiedBy->getName(); // To get user name who modified the note's attachment
                    $owner = $attchmentIns->getOwner();
                    echo $owner->getId(); // To get the note's attachment owner id
                    echo $owner->getName(); // To get the note's attachment owner name
                    echo $attchmentIns->getCreatedTime(); // To get attachment created time
                    echo $attchmentIns->getModifiedTime(); // To get attachment modified time
                }
            }
        }
    }

    public function addNote()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $noteIns = ZCRMNote::getInstance($record, null); // to get the note instance
        $noteIns->setTitle('Title_API1'); // to set the note title
        $noteIns->setContent('This is test content'); // to set the note content
        $responseIns = $record->addNote($noteIns); // to add the note
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function updateNote()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $noteIns = ZCRMNote::getInstance($record, '{note_id}'); // to get the note instance
        $noteIns->setTitle('Title_API1'); // to set the title of the note
        $noteIns->setContent('This is test cooontent'); // to set the content of the note
        $responseIns = $record->updateNote($noteIns); // to update the note
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function deleteNote()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $noteIns = ZCRMNote::getInstance($record, '{note_id}'); // to get the note instance
        $responseIns = $record->deleteNote($noteIns); // to delete the note

        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function getAttachments()
    {
        $records = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $responseIns = $records->getAttachments(1, 50); // to get the attachments
        $attachments = $responseIns->getData(); // to get the attachments in form of ZCRMAttachment instance array
        foreach ($attachments as $attchmentIns) {
            echo $attchmentIns->getId(); // To get the note's attachment id
            echo $attchmentIns->getFileName(); // To get the note's attachment file name
            echo $attchmentIns->getFileType(); // To get the note's attachment file type
            echo $attchmentIns->getSize(); // To get the note's attachment file size
            echo $attchmentIns->getParentModule(); // To get the note's attachment parent module name
            $parentRecord = $attchmentIns->getParentRecord();
            echo $parentRecord->getEntityId(); // To get the note's parent record id
            echo $attchmentIns->getParentName(); // To get the note name
            echo $attchmentIns->getParentId(); // To get the note id
            $createdBy = $attchmentIns->getCreatedBy();
            echo $createdBy->getId(); // To get user_id who created the note's attachment
            echo $createdBy->getName(); // To get user name who created the note's attachment
            $modifiedBy = $attchmentIns->getModifiedBy();
            echo $modifiedBy->getId(); // To get user_id who modified the note's attachment
            echo $modifiedBy->getName(); // To get user name who modified the note's attachment
            $owner = $attchmentIns->getOwner();
            echo $owner->getId(); // To get the note's attachment owner id
            echo $owner->getName(); // To get the note's attachment owner name
            echo $attchmentIns->getCreatedTime(); // To get attachment created time
            echo $attchmentIns->getModifiedTime(); // To get attachment modified time
        }
    }

    public function uploadAttachment()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $responseIns = $record->uploadAttachment('/path/to/file'); // $filePath - absolute path of the attachment to be uploaded.
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function uploadLinkAsAttachment()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $responseIns = $record->uploadLinkAsAttachment('https://www.google.com/url?sa=i&source=images&cd=&cad=rja&uact=8&ved=2ahUKEwiBw56T19vfAhVIfisKHRNrDH4QjRx6BAgBEAU&url=https%3A%2F%2Fwww.pexels.com%2Fsearch%2Fnature%2F&psig=AOvVaw3CtMR6IfHNO2ArtwV_BIGq&ust=1546950855212495'); // $filePath - absolute path of the attachment to be uploaded.
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function downloadAttachment()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $fileResponseIns = $record->downloadAttachment('{attachment_id}');

        $filePath = '/path/to/file';
        $fp = fopen($filePath . $fileResponseIns->getFileName(), 'w'); // $filePath - absolute path where downloaded file has to be stored.
        echo 'HTTP Status Code:' . $fileResponseIns->getHttpStatusCode();
        echo 'File Name:' . $fileResponseIns->getFileName();
        $stream = $fileResponseIns->getFileContent();
        fputs($fp, $stream);
        fclose($fp);
    }

    public function deleteAttachment()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $fileResponseIns = $record->downloadAttachment('{attachment_id}');
        echo 'HTTP Status Code:' . $fileResponseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $fileResponseIns->getStatus(); // To get response status
        echo 'Message:' . $fileResponseIns->getMessage(); // To get response message
        echo 'Code:' . $fileResponseIns->getCode(); // To get status code
        echo 'Details:' . $fileResponseIns->getDetails()['id'];
    }

    /**
     * @param string $path_to_image
     * @param string $record_id
     * @param string $module
     */
    public function uploadPhoto(string $path_to_image, string $record_id, string $module = Constants::MODULE_CONTACTS)
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance($module, $record_id); // To get record instance
        $responseIns = $record->uploadPhoto($path_to_image); // $photoPath - absolute path of the photo to be uploaded.
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function downloadPhoto()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $fileResponseIns = $record->downloadPhoto(); // to download the photo
        echo 'HTTP Status Code:' . $fileResponseIns->getHttpStatusCode();
        echo 'File Name:' . $fileResponseIns->getFileName();
        $filePath = '/path/to/file';
        $fp = fopen($filePath . $fileResponseIns->getFileName(), 'w'); // $filePath - absolute path where the downloaded photo is stored.
        $stream = $fileResponseIns->getFileContent();
        fputs($fp, $stream);
        fclose($fp);
    }

    public function deletePhoto()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $responseIns = $record->deletePhoto(); // $photoPath - absolute path of the photo to be uploaded.
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function addRelation()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $junctionrecord = ZCRMJunctionRecord::getInstance('{module_api_name}', '{record_id}'); // to get the junction record instance
        $responseIns = $record->addRelation($junctionrecord); // to add a relation between the record and the junction record
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function removeRelation()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $junctionrecord = ZCRMJunctionRecord::getInstance('{module_api_name}', '{record_id}'); // to get the junction record instance
        $responseIns = $record->removeRelation($junctionrecord); // to add a relation between the record and the junction record
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . $responseIns->getDetails()['id'];
    }

    public function addTags()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $tagNames = array(
            'test1',
            'test2',
        ); // to create array of tag names
        $responseIns = $record->addTags($tagNames); // to add tags
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . json_encode($responseIns->getDetails());
    }

    public function removeTags()
    {
        $record = ZCRMRestClient::getInstance()->getRecordInstance('{module_api_name}', '{record_id}'); // To get record instance
        $tagNames = array(
            'test1',
            'test2',
        ); // to create array of tag names
        $responseIns = $record->removeTags($tagNames); // to remove tags
        echo 'HTTP Status Code:' . $responseIns->getHttpStatusCode(); // To get http response code
        echo 'Status:' . $responseIns->getStatus(); // To get response status
        echo 'Message:' . $responseIns->getMessage(); // To get response message
        echo 'Code:' . $responseIns->getCode(); // To get status code
        echo 'Details:' . json_encode($responseIns->getDetails());
    }

    private function set_contact_fields($record, $contact_data)
    {
        if (!empty($contact_data['idu'])) {
            $record->setFieldValue('EP_USER_ID', $contact_data['idu']);
        }

        if (!empty($contact_data['first_name'])) {
            $record->setFieldValue('First_Name', $contact_data['first_name']);
        }

        if (!empty($contact_data['last_name'])) {
            $record->setFieldValue('Last_Name', $contact_data['last_name']);
        }

        if (!empty($contact_data['full_name'])) {
            $record->setFieldValue('Name', $contact_data['full_name']);
        }

        if (!empty($contact_data['email'])) {
            $record->setFieldValue('Email', $contact_data['email']);
        }

        if (!empty($contact_data['secondary_email'])) {
            $record->setFieldValue('Secondary_Email', $contact_data['secondary_email']);
        }

        if (!empty($contact_data['lead_type'])) {
            $record->setFieldValue('Lead_Type', $contact_data['lead_type']);
        }

        // if (!empty($contact_data['photo']) && !empty($contact_data['idu'])) {
        //     $record->uploadPhoto(realpath('public/img/users/' . $contact_data['idu'] . '/' . $contact_data['photo']));
        // }

        if (!empty($contact_data['registration_date'])) {
            $record->setFieldValue('Registration_Date', \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $contact_data['registration_date'])->format(DATE_ATOM));
        }

        if (!empty($contact_data['phone'])) {
            $utils = PhoneNumberUtil::getInstance();

            try {
                $phoneNumber = $utils->parse($contact_data['phone']);
                $internationalFormat = $utils->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
            } catch (NumberParseException $e) {
                $internationalFormat = $contact_data['phone'];
            }

            $record->setFieldValue('Phone', preg_replace('/[^+0-9]/', '', $internationalFormat));
        }

        if (!empty($contact_data['home_phone'])) {
            $record->setFieldValue('Home_Phone', $contact_data['home_phone']);
        }

        if (!empty($contact_data['zip'])) {
            $record->setFieldValue('Zip_Code', $contact_data['zip']);
        }

        if (!empty($contact_data['description'])) {
            $record->setFieldValue('Description', $contact_data['description']);
        }

        if (!empty($contact_data['fax'])) {
            $record->setFieldValue('Fax', $contact_data['fax']);
        }

        if (!empty($contact_data['group_type'])) {
            $record->setFieldValue('User_Type', $contact_data['group_type']);
        }

        if (!empty($contact_data['group_name'])) {
            $record->setFieldValue('User_Group', $contact_data['group_name']);
        }

        if (!empty($contact_data['profile_completion'])) {
            $record->setFieldValue('Profile_Completion', $contact_data['profile_completion']);
        }

        if (!empty($contact_data['industries'])) {
            $record->setFieldValue('Industries', $contact_data['industries']);
        }

        if (isset($contact_data['is_verified'])) {
            $record->setFieldValue('Verified', (bool) (int) $contact_data['is_verified']);
        }

        if (isset($contact_data['is_certified'])) {
            $record->setFieldValue('Certified', (bool) (int) $contact_data['is_certified']);
        }

        if (!empty($contact_data['status'])) {
            $record->setFieldValue('Status', $contact_data['status']);
        }

        if (!empty($contact_data['profile_completion_detail'])) {
            $record->setFieldValue('Profile_Completion_Detail', $contact_data['profile_completion_detail']);
        }

        if (isset($contact_data['company_annual_revenue'])) {
            $record->setFieldValue('Annual_Revenue', $contact_data['company_annual_revenue']);
        }

        if (isset($contact_data['number_of_employees'])) {
            $record->setFieldValue('No_of_Employees', $contact_data['number_of_employees']);
        }

        if (isset($contact_data['left_product_request'])) {
            $record->setFieldValue('Left_Product_Requests', (bool) $contact_data['left_product_request']);
        }

        //region address
        if (!empty($contact_data['country'])) {
            $record->setFieldValue('Country', $contact_data['country']);
        }

        if (!empty($contact_data['state'])) {
            $record->setFieldValue('State', $contact_data['state']);
        }

        if (!empty($contact_data['city'])) {
            $record->setFieldValue('City', $contact_data['city']);
        }

        if (!empty($contact_data['address'])) {
            $record->setFieldValue('Street', $contact_data['address']);
        }
        //endregion address

        //region socials
        if (!empty($contact_data['skype'])) {
            $record->setFieldValue('Skype_ID', $contact_data['skype']);
        }

        if (!empty($contact_data['twitter'])) {
            $record->setFieldValue('Twitter_URL', $contact_data['twitter']);
        }

        if (!empty($contact_data['website'])) {
            $record->setFieldValue('Website_URL', $contact_data['website']);
        }

        if (!empty($contact_data['facebook'])) {
            $record->setFieldValue('Facebook_URL', $contact_data['facebook']);
        }

        if (!empty($contact_data['instagram'])) {
            $record->setFieldValue('Instagram_URL', $contact_data['instagram']);
        }

        if (!empty($contact_data['linkedin'])) {
            $record->setFieldValue('Linkedin_URL', $contact_data['linkedin']);
        }

        if (!empty($contact_data['youtube'])) {
            $record->setFieldValue('Youtube_URL', $contact_data['youtube']);
        }
        //endregion socials

        //region company
        if (!empty($contact_data['original_company_name'])) {
            $record->setFieldValue('Original_Company_Name', decodeCleanInput($contact_data['original_company_name']));
        }

        if (!empty($contact_data['displayed_company_name'])) {
            $record->setFieldValue('Displayed_Company_Name', decodeCleanInput($contact_data['displayed_company_name']));
        }
        //endregion company

        //region items statistics
        if (isset($contact_data['draft_items'])) {
            $record->setFieldValue('Count_Of_Draft_Items', (int) $contact_data['draft_items']);
        }

        if (isset($contact_data['not_visible_items'])) {
            $record->setFieldValue('Count_Of_Not_Visible_Items', (int) $contact_data['not_visible_items']);
        }

        if (isset($contact_data['blocked_items'])) {
            $record->setFieldValue('Count_Of_Blocked_Items', (int) $contact_data['blocked_items']);
        }

        if (isset($contact_data['active_items'])) {
            $record->setFieldValue('Count_Of_Active_Items', (int) $contact_data['active_items']);
        }
        //endregion items statistics

        if (isset($contact_data['lead_source'])) {
            $record->setFieldValue('Lead_Source', $contact_data['lead_source']);
        }

        if (isset($contact_data['registration_status'])) {
            $record->setFieldValue('Registration_Status', $contact_data['registration_status']);
        }

        if (isset($contact_data['timezone'])) {
            $record->setFieldValue('Timezone', $contact_data['timezone']);
        }

        if (isset($contact_data['utcOffset'])) {
            $record->setFieldValue('UTC_offset', $contact_data['utcOffset']);
        }
    }
}
