<?php

/*-------------------------------------------------------+
| SYSTOPIA Event Invitation                              |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Zschiedrich (zschiedrich@systopia.de)       |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*/

use CRM_Eventinvitation_ExtensionUtil as E;

class CRM_Eventinvitation_Queue_Runner_EmailSender extends CRM_Eventinvitation_Queue_Runner_Job
{
    /** @var string $template */
    protected $emailSender;

    public function __construct(
        CRM_Eventinvitation_Object_RunnerData $runnerData,
        $emailSender,
        int $offset
    ) {
        parent::__construct($runnerData, $offset);
        $this->emailSender = $emailSender;
    }


    /**
     * Send an email to the given contact
     *
     * @param integer $contactId
     *   contact ID
     * @param array $templateTokens
     *   tokens
     *
     * @throws \CiviCRM_API3_Exception
     */
    protected function processContact($contactId, $templateTokens, $emailTypes)
    {

      $contact = \Civi\Api4\Contact::get(FALSE)
              ->addSelect('display_name')
              ->addWhere('id', '=', $contactId)
              ->execute()
              ->first();

      $emails = \Civi\Api4\Email::get(FALSE)
              ->addSelect('contact_id', 'location_type_id', 'email')
              ->addWhere('location_type_id', 'IN', $emailTypes)
              ->addWhere('contact_id', '=', $contactId)
              ->execute();
      foreach ($emails as $email) {
        // do something
        $emailData = [
                'id' => $this->runnerData->templateId,
                'toName' => $contact['display_name'],
                'toEmail' => $email['email'],
                'from' => $this->emailSender,
                'contactId' => $contactId,
                'tplParams' => $templateTokens,
        ];
        civicrm_api3('MessageTemplate', 'send', $emailData);
      }
    }
}
