<?php

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_DAO;
use InvalidArgumentException;
use PhpParser\Node\Expr\Cast\Object_;

abstract class CRM_NYSS_BAO_Integration_WebsiteEvent implements CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  /**
   * Stores data needed to process an event
   *
   * @var \CRM_NYSS_BAO_Integration_WebsiteEventData
   */
  protected CRM_NYSS_BAO_Integration_WebsiteEventData $event_data;

  /**
   * @var int a civicrm_tag ID that identifies the parent
   * tag used for child tags associated with the event type.
   */
  protected int $parent_tag_id = 0;

  /**
   * @var string|null Most Event types are recorded with a tag.
   * This is the name of the tag associated with the event instance
   */
  protected ?string $tag_name = NULL;

  /**
   * @var array|null associative array with keys: id, name
   */
  protected ?array $tag = NULL;

  /**
   * @var string|null specifies the name of an event type specific archive
   * table such as archive_issue or archive_bill.
   */
  protected ?string $archiveTableName = '';

  /**
   * @var array|null specifies specific database table field names to be
   * included in the results of getArchiveSQL()
   */
  protected ?array $archiveFields = NULL;

  /** @var bool whether to check civicrm v4 API user permissions. Defaults to true for security,
   *            but should be set to false when run from the command line
   * because there is no active user.
   */
  protected bool $civi_api4_permission_check = TRUE;

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    $this->event_data = $event_data;

    return $this;
  }

  /**
   * Main entry point/method for processing an event type.
   *
   * @param int $contact_id
   *
   * @return $this
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function process(int $contact_id): static {
    // Load Parent Tag ID
    $this->setParentTagId($this->findParentTagId());
    return $this;
  }

  protected function setArchiveTableName(string $archive_table_name): void {
    $this->archiveTableName = $archive_table_name;
  }

  public function getArchiveTableName(): string {
    return $this->archiveTableName;
  }

  public function hasArchiveTable(): bool {
    return !empty($this->archiveTableName);
  }

  public function getArchiveFields(): ?array {
    return $this->archiveFields;
  }

  public function findParentTagId(): int {
    $tags = \Civi\Api4\Tag::get($this->getCiviPermissionCheck())
      ->selectRowCount()
      ->addSelect('id')
      ->addWhere('name', '=', $this->getParentTagName())
      ->addWhere('is_tagset', '=', TRUE)
      ->setLimit(1)
      ->execute();

    if (count($tags) > 1) {
      throw new \Exception("Multiple tag matches for '{$this->getParentTagName()}'");
    }
    if (count($tags) !== 1) {
      throw new \Exception("No tag match for '{$this->getParentTagName()}'");
    }
    return $tags[0]['id'];
  }

  public function setCiviPermissionCheck(bool $civi_api4_permission_check): CRM_NYSS_BAO_Integration_WebsiteEvent {
    $this->civi_api4_permission_check = $civi_api4_permission_check;
    return $this;
  }

  public function getCiviPermissionCheck(): bool {
    return $this->civi_api4_permission_check;
  }

  /**
   * Checks to see if a tag exists. If $create is set to true, then the tag
   * will be created if it doesn't already exist.
   *
   * @param string $tag_name
   * @param string|null $parent_id
   * @param bool $create Specify whether to create the tag if it doesn't
   *   already exist
   *
   * @return array The tag data
   * @throws UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  protected function findTag(string $tag_name, ?string $parent_id = NULL, bool $create = FALSE): array {
    $tag = [];

    // Query for tag
    $q = \Civi\Api4\Tag::get($this->getCiviPermissionCheck())
      ->selectRowCount()
      ->addSelect('id')
      ->addSelect('name')
      ->addWhere('name', '=', $tag_name)
      ->setLimit(1);

    if ($parent_id) {
      $q->addWhere('parent_id', '=', $parent_id);
    }

    $results = $q->execute();

    if (count($results) > 0) {
      // If tag exists, then return it
      $tag = $results[0];
    }
    elseif ($create) {
      // If $create option is specified then create it.
      return $this->createTag($tag_name, $parent_id);
    }
    else {
      return [];
    }
    return $tag;
  }

  /**
   * Creates a new tag in civicrm_tag with the given name and associated with
   * the given parent.
   *
   * @param string $tag_name
   * @param int $parent_id
   *
   * @return array
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function createTag(string $tag_name, int $parent_id): array {
    $results = \Civi\Api4\Tag::create($this->getCiviPermissionCheck())
      ->addValue('name', $tag_name)
      ->addValue('parent_id', $parent_id)
      ->addValue('is_selectable', FALSE)
      ->addValue('is_reserved', TRUE)
      ->addValue('used_for', [
        'civicrm_contact',
      ])
      ->setCheckPermissions($this->getCiviPermissionCheck())
      ->execute();

    if ((count($results) < 1) || (isset($results[0]['error_code']))) {
      throw new \CRM_Core_Exception('Failed to create tag with error: ' . $results[0]['error_message']);
    }

    // force cache clear. The original code was doing this, to solve a caching problem
    // I don't know if the caching problem still exists.
    civicrm_api3('Tag', 'getfields', ['cache_clear' => 1]);

    return $results[0];
  }

  /**
   * Checks to see if a relationship exists between a contact and a tag.
   *
   * @param int $contact_id unique id from civicrm_contact
   * @param int $tag_id unique id from civicrm tag
   *
   * @return bool true for "relationship exists". False otherwise.
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function hasEntityTag(int $contact_id, int $tag_id): bool {
    $entityTags = \Civi\Api4\EntityTag::get($this->getCiviPermissionCheck())
      ->addSelect('id')
      ->addWhere('entity_table', '=', 'civicrm_contact')
      ->addWhere('entity_id', '=', $contact_id)
      ->addWhere('tag_id', '=', $tag_id)
      ->setLimit(1)
      ->setCheckPermissions($this->getCiviPermissionCheck())
      ->execute();

    return count($entityTags) > 0;
  }

  /**
   * Creates a relationship between the specified contact and tag.
   *
   * @param int $contact_id unique id from civicrm_contact
   * @param int $tag_id unique id from civicrm tag
   *
   * @return void
   * @throws UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  protected function createEntityTag(int $contact_id, int $tag_id): void {
    $results = \Civi\Api4\EntityTag::create($this->getCiviPermissionCheck())
      ->addValue('entity_table', 'civicrm_contact')
      ->addValue('entity_id', $contact_id)
      ->addValue('tag_id', $tag_id)
      ->setCheckPermissions($this->getCiviPermissionCheck())
      ->execute();

    if ((count($results) < 1) || (isset($results[0]['error_code']))) {
      throw new \CRM_Core_Exception('Failed to create tag with error: ' . $results[0]['error_message']);
    }
  }

  /**
   * Removes the relationship between a contact and a tag
   *
   * @param int $contact_id unique id from civicrm_contact
   * @param int $tag_id unique id from civicrm_tag
   *
   * @return void
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function deleteEntityTag(int $contact_id, int $tag_id): void {
    $results = \Civi\Api4\EntityTag::delete($this->getCiviPermissionCheck())
      ->addWhere('entity_table', '=', 'civicrm_contact')
      ->addWhere('entity_id', '=', $contact_id)
      ->addWhere('tag_id', '=', $tag_id)
      ->setCheckPermissions($this->getCiviPermissionCheck())
      ->execute();

    if (count($results) < 1) {
      throw new \CRM_Core_Exception('Failed to delete entity tag');
    }
  }

  public function getEventInfo(): object {
    return $this->event_data->getEventInfo();
  }

  public function getEventAction(): string {
    return $this->event_data->getEventAction();
  }

  public function getActivityData(): ?string {
    // Historically, most event types don't contain any activity data.
    // Will override as needed
    return '';
  }

  public function setParentTagId(int $parent_tag_id): CRM_NYSS_BAO_Integration_WebsiteEvent {
    $this->parent_tag_id = $parent_tag_id;
    return $this;
  }

  public function getParentTagId(): int {
    return $this->parent_tag_id;
  }

  protected function setTagName(string $tag_name): static {
    $this->tag_name = $tag_name;
    return $this;
  }

  public function getTagName(): string {
    return $this->tag_name;
  }

  public function setTag(array $tag): CRM_NYSS_BAO_Integration_WebsiteEvent {
    $this->tag = $tag;
    return $this;
  }

  public function getTag(): array {
    return $this->tag;
  }

  /**
   * Generates an SQL statement that can be used to archive event type specific
   * values to an event type specific table.
   *
   * @param int $archive_id
   * @param string|null $prefix
   *
   * @return string
   */
  public function getArchiveSQL(int $archive_id, ?string $prefix = NULL): string {
    if (empty($archive_id)) {
      throw new InvalidArgumentException("Archive ID required for building archival SQL command.");
    }

    $template = "INSERT INTO :prefix:table (archive_id, :fields) VALUES ('" . $archive_id . "', :values)";

    // Escape and Quote Values
    $data = array_map(function($value) {
      return "'" . CRM_Core_DAO::escapeString($value) . "'";
    }, $this->getArchiveValues());

    $replacements = [
      ':prefix' => empty($prefix) ? '' : $prefix . '.',
      ':table' => $this->getArchiveTableName(),
      ':fields' => implode(", ", $this->getArchiveFields()),
      ':values' => implode(", ", $data),
    ];

    return strtr($template, $replacements);
  }

}