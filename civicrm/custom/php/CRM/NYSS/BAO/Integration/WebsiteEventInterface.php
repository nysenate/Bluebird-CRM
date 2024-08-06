<?php

namespace CRM_NYSS_BAO_Integration;

interface WebsiteEventInterface
{
    public const EVENT_TYPE_BILL = 'BILL';
    public const EVENT_TYPE_ISSUE = 'ISSUE';
    public const EVENT_TYPE_COMMITTEE = 'COMMITTEE';
    public const EVENT_TYPE_DIRECTMSG = 'DIRECTMSG';
    public const EVENT_TYPE_CONTEXTMSG = 'CONTEXTMSG';
    public const EVENT_TYPE_PETITION = 'PETITION';
    public const EVENT_TYPE_ACCOUNT = 'ACCOUNT';
    public const EVENT_TYPE_PROFILE = 'PROFILE';


    /**
     * @return $this
     * Processes Website Event.
     */
    public function process(): static;
    function getParentTagName(): string;
    function findParentTagId(): int;
    //public function archiveRecord() : void;

}