<?php

namespace NYSS\BAO\Integration\WebsiteEvent;

class CRM_NYSS_BAO_Integration_WebsiteEvent_PollEvent extends \CRM_NYSS_BAO_Integration_WebsiteEvent implements \CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  function getParentTagName(): string {
    // TODO: Implement getParentTagName() method.
  }

  public function getEventDetails(): string {
    // TODO: Implement getEventDetails() method.
  }

  public function getEventDescription(): string {
    // TODO: Implement getEventDescription() method.
  }

  public function getActivityData(): ?string {
    // TODO: Implement getActivityData() method.
  }

  public function getArchiveValues(): ?array {
    // TODO: Implement getArchiveValues() method.
  }

  public function getArchiveSQL(int $archive_id, ?string $prefix = NULL): string {
    // TODO: Implement getArchiveSQL() method.
  }

}