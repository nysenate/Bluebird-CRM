<?php

namespace CRM_NYSS_BAO_Integration_WebsiteEvent;

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_Exception;
use CRM_NYSS_BAO_Integration\WebsiteEventInterface;
use CRM_NYSS_BAO_Integration_OpenLegislation;
use CRM_NYSS_BAO_Integration_WebsiteEvent\BaseEvent;
use InvalidArgumentException;

class BillEvent extends BaseEvent implements WebsiteEventInterface
{
    use FollowableEvent;

    const ACTION_SUPPORT = 'aye';
    const ACTION_OPPOSE = 'nay';

    const PARENT_TAG_NAME = 'Website Bills';
    const TAG_SUFFIX_SUPPORT = 'SUPPORT';
    const TAG_SUFFIX_OPPOSE = 'OPPOSE';

    protected string $bill_name;

    protected array $base_tag = [];

    public function __construct(int $contact_id, string $action, array $event_info)
    {

        parent::__construct($contact_id, $action, $event_info);

        if (empty($this->getBillNum())) {
            throw new InvalidArgumentException("bill_number must be in event info.");
        }
        if (empty($this->getBillYear())) {
            throw new InvalidArgumentException("bill_year must be in event info.");
        }

        // If no sponsor, then query Open Leg and try to get a value for it.
        if (empty($this->getBillSponsor())) {
            $this->setBillSponsor(CRM_NYSS_BAO_Integration_OpenLegislation::getBillSponsor($this->getBillNum().'-'.$this->getBillYear()));
        }

        // Build Bill Name
        $this->bill_name = $this->buildBillName($this->getBillNum(), $this->getBillYear(), $this->getBillSponsor());
        // Get Tag
        $this->setTagName($this->bill_name);
        $this->setTag($this->findTag($this->getTagName(),$this->getParentTagId()));

        return $this;
    }

    /**
     * @throws UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    public function process(): static {

        // Process Specific Action
        switch ($this->getAction()) {
            case self::ACTION_FOLLOW:
                if (! $this->isFollowing()) {
                    $this->follow();
                }
                break;
            case self::ACTION_UNFOLLOW:
                if ($this->isFollowing()) {
                    $this->unfollow();
                }
                break;
            case self::ACTION_SUPPORT:
                $this->supportBill();
                break;
            case self::ACTION_OPPOSE:
                $this->opposeBill();
                break;
            default:
                throw new CRM_Core_Exception("Unable to determine bill action");
        }

        return $this;
    }

    protected function buildBillName(string $bill_num, string $bill_year, ?string $sponsor = NULL): string
    {
        //build bill value text
        $bill_name = $bill_num.'-'.$bill_year;

        // If there's a sponsor, then include it in the bill name
        if (! empty($sponsor)) {
            $bill_name .= "($sponsor)";
        }

        return strtoupper($bill_name);
    }

    protected function supportBill(): static {
        $support_tag = $this->findTag($this->getTagNameSupport(), $this->getParentTagId(), true);
        $this->createEntityTag($this->getContactId(),$support_tag['id']);

        // remove oppose tag, if contact is now in support of bill
        $oppose_tag = $this->findTag($this->getTagNameOppose(), $this->getParentTagId(), false);
        if (! empty($oppose_tag) && $this->hasEntityTag($this->getContactId(),$oppose_tag['id'])) {
            $this->deleteEntityTag($this->getContactId(),$oppose_tag['id']);
        }
        return $this;
    }

    protected function opposeBill(): static {
        $oppose_tag = $this->findTag($this->getTagNameOppose(), $this->getParentTagId(), true);
        $this->createEntityTag($this->getContactId(),$oppose_tag['id']);

        // remove support tag, if contact is now in support of bill
        $support_tag = $this->findTag($this->getTagNameSupport(), $this->getParentTagId(), false);
        if (! empty($support_tag) && $this->hasEntityTag($this->getContactId(),$support_tag['id'])) {
            $this->deleteEntityTag($this->getContactId(),$support_tag['id']);
        }
        return $this;
    }

    protected function getTagNameSupport(): string {
        if (empty($this->base_tag)) {
            throw new \CRM_Core_Exception('No base tag set.');
        }
        return $this->base_tag['name'].': '. self::TAG_SUFFIX_SUPPORT;
    }

    protected function getTagNameOppose(): string {
        if (empty($this->base_tag)) {
            throw new \CRM_Core_Exception('No base tag set.');
        }
        return $this->base_tag['name'].': '. self::TAG_SUFFIX_OPPOSE;
    }

    public function getParentTagName(): string
    {
        return self::PARENT_TAG_NAME;
    }

    public function getBillNum(): ?string {
        $event_info = $this->getEventInfo();
        return $event_info['bill_number'] ?? null;
    }

    public function getBillYear(): ?string {
        $event_info = $this->getEventInfo();
        return $event_info['bill_year'] ?? null;
    }

    public function getBillSponsor(): ?string {
        $event_info = $this->getEventInfo();
        return $event_info['sponsors'] ?? null;
    }

    private function setBillSponsor(string $sponsor): static {
        $this->setEventInfoAttribute('sponsors',$sponsor);
        return $this;
    }

    public function getBillName(): string
    {
        return $this->bill_name;
    }


}