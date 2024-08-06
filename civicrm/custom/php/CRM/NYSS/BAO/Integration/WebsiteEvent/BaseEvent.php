<?php

namespace CRM_NYSS_BAO_Integration_WebsiteEvent;

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_DAO;
use CRM_NYSS_BAO_Integration\WebsiteEventInterface;
use InvalidArgumentException;
use PhpParser\Node\Expr\Cast\Object_;

abstract class BaseEvent implements WebsiteEventInterface
{
    private int $contact_id;
    private string $action;
    private array $event_info;
    private int $parent_tag_id;
    private string $tag_name;
    private array $tag;


    public function __construct(int $contact_id, string $action, array $event_info) {

        if (empty($contact_id)) {
            throw new InvalidArgumentException("contact_id parameter cannot be empty.");
        }
        if (empty($action)) {
            throw new InvalidArgumentException("action parameter cannot be empty.");
        }
        if (empty($event_info)) {
            throw new InvalidArgumentException("event_info parameter cannot be empty.");
        }

        $this->setContactId($contact_id);
        $this->setAction($action);
        $this->setEventInfo($event_info);

        // Load Parent Tag ID
        $this->setParentTagId($this->findParentTagId());

        return $this;
    }

    /**
     * @throws UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    public function findParentTagId(): int
    {
        $tags = \Civi\Api4\Tag::get(TRUE)
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

    /**
     * @param string $tag_name
     * @param string|null $parent_id
     * @param bool $create Specify whether to create the tag if it doesn't already exist
     * @return array The tag data
     * @throws UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    protected function findTag(string $tag_name, ?string $parent_id = null, bool $create = false): array
    {
        $tag = [];

        // Query for tag
        $q = \Civi\Api4\Tag::get(TRUE)
            ->selectRowCount()
            ->addSelect('id')
            ->addWhere('name', '=', $tag_name)
            ->setLimit(1);

        if ($parent_id) {
            $q->addWhere('parent_id', '=', $parent_id);
        }

        $results = $q->execute();

        if (count($results) > 0) {
            // If tag exists, then return it
            $tag = $results[0];
        } elseif ($create) {
            // If $create option is specified then create it.
            return $this->createTag($tag_name,$parent_id);

        } else {
            return [];
        }
        return $tag;
    }

    protected function createTag(string $tag_name, int $parent_id): array {

        $results = \Civi\Api4\Tag::create(TRUE)
            ->addValue('name', $tag_name)
            ->addValue('parent_id', $parent_id)
            ->addValue('is_selectable', FALSE)
            ->addValue('is_reserved', TRUE)
            ->addValue('used_for', [
                'civicrm_contact',
            ])
            ->execute();

        if ((count($results)  < 1) || (isset($results[0]['error_code']))) {
            throw new \CRM_Core_Exception('Failed to create tag with error: ' . $results[0]['error_message']);
        }

        // force cache clear. The original code was doing this, to solve a caching problem
        // I don't know if the caching problem still exists.
        civicrm_api3('Tag', 'getfields', array('cache_clear' => 1));

        return $results[0];

    }

    protected function hasEntityTag(int $contact_id, int $tag_id): bool {

        $entityTags = \Civi\Api4\EntityTag::get(TRUE)
            ->addSelect('id')
            ->addWhere('entity_table', '=', 'civicrm_contact')
            ->addWhere('entity_id', '=', $contact_id)
            ->addWhere('tag_id', '=', $tag_id)
            ->setLimit(1)
            ->execute();

        return count($entityTags) > 0;

    }

    /**
     * @param int $contact_id
     * @param int $tag_id
     * @return void
     * @throws UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    protected function createEntityTag(int $contact_id, int $tag_id): void {

        $results = \Civi\Api4\EntityTag::create(TRUE)
            ->addValue('entity_table', 'civicrm_contact')
            ->addValue('entity_id', $contact_id)
            ->addValue('tag_id', $tag_id)
            ->execute();

        if ((count($results)  < 1) || (isset($results[0]['error_code']))) {
            throw new \CRM_Core_Exception('Failed to create tag with error: ' . $results[0]['error_message']);
        }

    }

    /**
     * @throws \CRM_Core_Exception
     * @throws UnauthorizedException
     */
    protected function deleteEntityTag(int $contact_id, int $tag_id): void {

        $results = \Civi\Api4\EntityTag::delete(TRUE)
            ->addWhere('entity_table', '=', 'civicrm_contact')
            ->addWhere('entity_id', '=', $contact_id)
            ->addWhere('tag_id', '=', $tag_id)
            ->execute();

        if (count($results)  < 1) {
            throw new \CRM_Core_Exception('Failed to delete entity tag');
        }

    }


    protected function archiveSuccess() : void {

    }

    protected function archiveFailure() : void {

    }

    protected function setContactId(int $contact_id): static
    {
        $this->contact_id = $contact_id;
        return $this;
    }

    public function getContactId(): int
    {
        return $this->contact_id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    protected function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getEventInfo(): array
    {
        return $this->event_info;
    }

    protected function setEventInfo(array $event_info): BaseEvent
    {
        $this->event_info = $event_info;
        return $this;
    }

    protected function setEventInfoAttribute($name, $value): static {
        $this->event_info[$name] = $value;
        return $this;
    }

    public function setParentTagId(int $parent_tag_id): BaseEvent
    {
        $this->parent_tag_id = $parent_tag_id;
        return $this;
    }

    public function getParentTagId(): int
    {
        return $this->parent_tag_id;
    }

    protected function setTagName(string $tag_name) : static {
        $this->tag_name = $tag_name;
        return $this;
    }

    public function getTagName(): string
    {
        return $this->tag_name;
    }

    public function setTag(array $tag): BaseEvent
    {
        $this->tag = $tag;
        return $this;
    }

    public function getTag(): array
    {
        return $this->tag;
    }

}