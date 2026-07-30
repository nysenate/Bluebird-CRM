<?php

namespace Civi\NYSS\Mail\Listener;

use Civi\FlexMailer\Event\CheckSendableEvent;
use Civi;

class CheckSendableListener
{
    #[CRM_NYSS_Attribute_IssueRef(18424)]
    /**
     * Validate that a category exists on the mailing before approval and submission.
     * NYSS #18424 -- one-click unsubscribe depends on having a mailing category
     */
    public static function requireMailingCategory(CheckSendableEvent $event) {
        if (empty($event->getMailing()->category)) {
            $event->setError('category', ts('A Mailing Category is required before submitting.'));
        }
    }
}
