<?php

class CRM_NYSS_BAO_Integration_WebsiteEvent_SurveyEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent_PollEvent implements CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  const ACTIVITY_TYPE = 'Survey';

  const ACTION_WEBFORM = 'webform';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {

    parent::__construct($event_data);

    if (empty($this->getFormId())) {
      throw new InvalidArgumentException("form ID must be in event info.");
    }

    // Archive Table Name
    $this->setArchiveTableName('archive_poll');
    $this->archiveFields = ['form_id'];

    return $this;
  }

  public function process(int $contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to process event.");
    }

    parent::process($contact_id);

    // Get Tag
    //$this->setTagName($this->getPetitionName());
    //$this->setTag($this->findTag($this->getTagName(), $this->getParentTagId(), true));

    // Process Specific Action
    switch ($this->getEventAction()) {
      case self::ACTION_WEBFORM:
        $this->processForm($contact_id);
        break;
      default:
        throw new CRM_Core_Exception("Unable to determine webform/survey action");
    }

    return $this;
  }

  protected function processForm(int $contact_id): void {
    // form_id, form_values, form_title, detail??
    $params = new stdClass();
    $params->form_id = $this->getFormId();
    $params->form_values = $this->getFormValues();
    $params->form_title = $this->getFormTitle();
    $params->detail = $this->getEventDetails();

    $result = CRM_NYSS_BAO_Integration_Website::processSurvey($contact_id, $this->getEventAction(), $params);
    if ($result['is_error'] == 1) {
      throw new Exception("Error processing webform/survey: " . $result['error_message']);
    }
  }

  public function getFormId(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->form_id ?? NULL;
  }

  public function getFormTitle(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->form_title ?? NULL;
  }

  public function getFormValues() {
    $event_info = $this->getEventInfo();
    return $event_info->form_values ?? NULL;
  }

  function getParentTagName(): string {
    return ''; // No tagging done with Survey/Webform actions
  }

  // No Parent Tag Involved with Webform actions
  public function findParentTagId(): int {
    return 0;
  }

  public function getEventDetails(): string {
    return $this->getEventAction() . ' :: ' . $this->getFormTitle();
  }

  public function getEventDescription(): string {
    return self::ACTIVITY_TYPE;
  }

  public function getArchiveValues(): ?array {
    return [
      $this->getFormId(),
    ];
  }




}