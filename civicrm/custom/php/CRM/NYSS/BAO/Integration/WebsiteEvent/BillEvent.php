<?php

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_Exception;
use CRM_NYSS_BAO_Integration_OpenLegislation;
use InvalidArgumentException;
use CRM_NYSS_BAO_Integration_WebsiteEvent;
use CRM_NYSS_BAO_Integration_WebsiteEventInteface;

class CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent implements CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  use CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent;

  const ACTIVITY_TYPE = 'Bill';

  const ACTION_SUPPORT = 'aye';

  const ACTION_OPPOSE = 'nay';

  const PARENT_TAG_NAME = 'Website Bills';

  const TAG_SUFFIX_SUPPORT = 'SUPPORT';

  const TAG_SUFFIX_OPPOSE = 'OPPOSE';

  protected string $bill_name = '';

  //protected array $base_tag = [];

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    parent::__construct($event_data);

    if (empty($this->getBillNum())) {
      throw new InvalidArgumentException("bill_number must be in event info.");
    }
    if (empty($this->getBillYear())) {
      throw new InvalidArgumentException("bill_year must be in event info.");
    }

    // If no sponsor, then query Open Leg and try to get a value for it.
    if (empty($this->getBillSponsor())) {
      $this->setBillSponsor(CRM_NYSS_BAO_Integration_OpenLegislation::getBillSponsor($this->getBillNum() . '-' . $this->getBillYear()));
    }

    // Archive Table Name
    $this->setArchiveTableName('archive_bill');
    $this->archiveFields = ['bill_number', 'bill_year', 'bill_sponsor'];

    return $this;
  }

  /**
   * @throws UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function process(int $contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to process event.");
    }

    parent::process($contact_id);

    // Build Bill Name
    $this->bill_name = $this->buildBillName($this->getBillNum(), $this->getBillYear(), $this->getBillSponsor());
    // Get Tag
    $this->setTagName($this->getBillName());
    $this->setTag($this->findTag($this->getTagName(), $this->getParentTagId(), TRUE));

    // Process Specific Action
    switch ($this->getEventAction()) {
      case self::ACTION_FOLLOW:
        if (!$this->isFollowing($contact_id)) {
          $this->follow($contact_id);
        }
        break;
      case self::ACTION_UNFOLLOW:
        if ($this->isFollowing($contact_id)) {
          $this->unfollow($contact_id);
        }
        break;
      case self::ACTION_SUPPORT:
        $this->supportBill($contact_id);
        break;
      case self::ACTION_OPPOSE:
        $this->opposeBill($contact_id);
        break;
      default:
        throw new CRM_Core_Exception("Unable to determine bill action");
    }

    return $this;
  }

  public function getEventDetails(): string {
    return $this->getEventAction() . ' :: ' . $this->getBillName();
  }

  public function getEventDescription(): string {
    return self::ACTIVITY_TYPE;
  }

  public function getArchiveValues(): ?array {
    return [
      $this->getBillNum(),
      $this->getBillYear(),
      $this->getBillSponsor(),
    ];
  }

  protected function buildBillName(string $bill_num, string $bill_year, ?string $sponsor = NULL): string {
    //build bill value text
    $bill_name = $bill_num . '-' . $bill_year;

    // If there's a sponsor, then include it in the bill name
    if (!empty($sponsor)) {
      $bill_name .= " ($sponsor)";
    }

    return strtoupper($bill_name);
  }

  protected function supportBill($contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to support bill.");
    }

    $support_tag = $this->findTag($this->getTagNameSupport(), $this->getParentTagId(), TRUE);
    $this->createEntityTag($contact_id, $support_tag['id']);

    // remove oppose tag, if contact is now in support of bill
    $oppose_tag = $this->findTag($this->getTagNameOppose(), $this->getParentTagId(), FALSE);
    if (!empty($oppose_tag) && $this->hasEntityTag($contact_id, $oppose_tag['id'])) {
      $this->deleteEntityTag($contact_id, $oppose_tag['id']);
    }
    return $this;
  }

  protected function opposeBill($contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to oppose bill.");
    }

    $oppose_tag = $this->findTag($this->getTagNameOppose(), $this->getParentTagId(), TRUE);
    $this->createEntityTag($contact_id, $oppose_tag['id']);

    // remove support tag, if contact is now in support of bill
    $support_tag = $this->findTag($this->getTagNameSupport(), $this->getParentTagId(), FALSE);
    if (!empty($support_tag) && $this->hasEntityTag($contact_id, $support_tag['id'])) {
      $this->deleteEntityTag($contact_id, $support_tag['id']);
    }
    return $this;
  }

  protected function getTagNameSupport(): string {
    if (empty($this->getTag())) {
      throw new \CRM_Core_Exception('No base tag set.');
    }
    $tag = $this->getTag();
    return $tag['name'] . ': ' . self::TAG_SUFFIX_SUPPORT;
  }

  protected function getTagNameOppose(): string {
    if (empty($this->getTag())) {
      throw new \CRM_Core_Exception('No base tag set.');
    }
    return $this->getTag()['name'] . ': ' . self::TAG_SUFFIX_OPPOSE;
  }

  public function getParentTagName(): string {
    return self::PARENT_TAG_NAME;
  }

  public function getBillNum(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->bill_number ?? NULL;
  }

  public function getBillYear(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->bill_year ?? NULL;
  }

  public function getBillSponsor(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->sponsors ?? NULL;
  }

  private function setBillSponsor(string $sponsor): static {
    $this->getEventInfo()->setEventInfoAttribute('sponsors', $sponsor);
    return $this;
  }

  public function getBillName(): string {
    return $this->bill_name;
  }

}