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
    $params->form_values = $this->getFormFields();
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

  /**
   * $event_info->form_values is an array of objects (field, value), but
   * each value potentially contains complex data such as another array or
   * object. The heavy lifting is offloaded to inspectField(), which is a
   * recursive function that inspects each value, iterates and recurses as
   * needed to pull out as many fields as possible. This structure is
   * flattened into a simple array of objects for compatibility with
   * CRM_NYSS_BAO_Integration_Website::processSurvey() and with CiviCRM's
   * custom_field structure.
   * @return array of objects (field name, field value)
   */
  public function getFormFields() : array {
    $event_info = $this->getEventInfo();
    $fields = [];
    // $used_labels tracks used labels to facilitate label uniqueness
    $used_labels = [];
    foreach($event_info->form_values as $fv) {
      $used_labels[] = $field_label = self::ensureUnique($fv->field,$used_labels);
      $this->inspectField($fv->value,$fields, $field_label);
    }
    return $fields;
  }

  /**
   * Inspects each value and recurses or iterates as necessary, creates unique
   * field names/labels and adds each field to $field for the sake of
   * flattening a bigger data structure to accommodate
   * CRM_NYSS_BAO_Integration_Website::processSurvey()
   */
  protected function inspectField(mixed $value, array &$fields, string $key = '', string $start_label = '') : void {
    // $used_labels tracks used labels to facilitate label uniqueness
    $used_labels = [];
    $base_label = ($start_label !== '' ? $start_label . '_' : '') . $key;
    $used_labels[] = $field_label = self::ensureUnique($base_label,$used_labels);
    // Scalar values and arrays of scalar values are treated as single fields
    if (is_scalar($value) or is_null($value)) {
      // It's a "simple value" field. Just add the field
      $fields[] = $this->getFieldDef($field_label, $value ?? '');
      return; // don't recurse
    } else if (is_array($value)) {
      if (array_reduce($value, fn($c, $v) => $c && (is_scalar($v) || is_null($v)),
        TRUE)) {
        // It's an array of "simple values"... join them into a single field
        $fields[] = $this->getFieldDef($field_label, join(', ', (array) $value));
        return; // don't recurse
      }
    }
    // Complex values require some recursion to break them into additional fields.
    if (is_array($value) or is_object($value)) {
      foreach($value as $child_key => $child_value) {
        $this->inspectField($child_value, $fields, $child_key, $field_label);
      }
    }
  }

  /**
   * @param string $str
   * @param array $existing
   * @param int $max_length
   * Takes the given string ($str) and makes sure that it is unique among
   * ($existing) strings. If not unique, appends a number to make it unique.
   * @return string
   */
  public static function ensureUnique(string $str, array $existing, int $max_length = 1020) : string {
    $cnt = 1;
    // This probably belongs somewhere else, or the function should be renamed
    // because it does more than just "ensure uniqueness"
    $str = preg_replace('/[^a-zA-Z0-9]+/', '_', substr($str,0,$max_length));
    while (in_array($str,$existing)) {
      $suffix = "_" . $cnt++;
      $str = substr($str,0,$max_length - strlen($suffix)) . $suffix;
    }
    return $str;
  }

  /**
   * @param string $label
   * @param string $value
   * Create a field in the proper format for
   * CRM_NYSS_BAO_Integration_Website::processSurvey()
   * @return object
   */
  protected function getFieldDef(string $label, string $value) : object {
    return (object) [
      "field" => $label,
      "value" => $this->cleanValue($value)
    ];
  }

  protected function cleanValue(string $string): string {
    // keep newlines
    $string = str_replace(array("<p>", "</p>", "<br>", "<br/>"), "\n", $string);
    // remove HTML (probably unnecessary, but also, civicrm will convert to character
    // entities, which makes the string longer than expected e.g. "<" becomes
    // &lt; (4 characters)
    $string = strip_tags($string);
    return substr($string, 0, 255);
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