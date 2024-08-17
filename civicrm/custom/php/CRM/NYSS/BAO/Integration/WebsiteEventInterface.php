<?php

interface CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  /**
   * @return $this
   * Processes Website Event.
   */
  public function process(int $contact_id): static;

  function getParentTagName(): string;

  //function findParentTagId(): int;
  public function getEventDetails(): string;

  public function getEventDescription(): string;

  public function getActivityData(): ?string;

  public function hasArchiveTable(): bool;

  public function getArchiveTableName(): string;

  public function getArchiveFields(): ?array;

  public function getArchiveValues(): ?array;

  public function getArchiveSQL(int $archive_id, ?string $prefix = NULL): string;

}