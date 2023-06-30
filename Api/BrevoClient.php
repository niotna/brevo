<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Brevo\Api;

use GuzzleHttp\Client;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\CreateContact;
use SendinBlue\Client\Model\RemoveContactFromList;
use SendinBlue\Client\Model\UpdateContact;
use Brevo\Model\BrevoNewsletterQuery;
use Thelia\Core\Event\Newsletter\NewsletterEvent;

/**
 * Class BrevoClient
 * @package Brevo\Api
 * @author Chabreuil Antoine <achabreuil@openstudio.com>
 */
class BrevoClient
{
    protected ContactsApi $contactApi;
    private mixed $newsletterId;


    public function __construct($apiKey, $newsletterId)
    {
        $this->newsletterId = (int)$newsletterId;
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $this->contactApi = new ContactsApi(new Client(), $config);
    }

    /**
     * @throws ApiException
     */
    public function subscribe(NewsletterEvent $event)
    {
        try {
            $contact = $this->contactApi->getContactInfoWithHttpInfo($event->getEmail());
        } catch (ApiException $apiException) {
            if ($apiException->getCode() !== 404) {
                throw $apiException;
            }
            $createContact = new CreateContact();
            $createContact['email'] = $event->getEmail();
            $createContact['attributes'] = ['PRENOM' => $event->getFirstname(), "NOM" => $event->getLastname()];
            $this->contactApi->createContactWithHttpInfo($createContact);
            $contact = $this->contactApi->getContactInfoWithHttpInfo($event->getEmail());
        }

        $this->update($event, $contact);

        return $contact;
    }

    public function update(NewsletterEvent $event, $contact = null)
    {
        $updateContact = new UpdateContact();
        $previousEmail = $contact ? $contact[0]['email'] : $event->getEmail();

        if (!$contact){
            $sibObject = BrevoNewsletterQuery::create()->findPk($event->getId());
            if (null === $sibObject) {
                $sibObject = BrevoNewsletterQuery::create()->findOneByEmail($previousEmail);
            }
            $previousEmail = $sibObject->getEmail();
            $contact = $this->contactApi->getContactInfoWithHttpInfo($previousEmail);

            $updateContact['email'] = $event->getEmail();
            $updateContact['attributes'] = ['PRENOM' => $event->getFirstname(), "NOM" => $event->getLastname()];
        }

        $updateContact['listIds'] = [$this->newsletterId];
        $this->contactApi->updateContactWithHttpInfo($contact[0]['id'], $updateContact);

        return $this->contactApi->getContactInfoWithHttpInfo($previousEmail);
    }

    public function unsubscribe(NewsletterEvent $event)
    {
        $contact = $this->contactApi->getContactInfoWithHttpInfo($event->getEmail());
        $change = false;

        if (in_array($this->newsletterId, $contact[0]['listIds'], true)) {
            $contactIdentifier = new RemoveContactFromList();
            $contactIdentifier['emails'] = [$event->getEmail()];
            $this->contactApi->removeContactFromList($this->newsletterId, $contactIdentifier);
            $change = true;
        }

        return $change ? $this->contactApi->getContactInfoWithHttpInfo($event->getEmail()) : $contact;
    }
}
