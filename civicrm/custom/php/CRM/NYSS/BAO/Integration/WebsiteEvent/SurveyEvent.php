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

  /**
   * @param int $contact_id
   *
   * @return void
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function processForm(int $contact_id): void {

    // NYSS 16799 -- No more custom field groups for surveys. Now, we just add
    // to Activity.details and Website_Survey.survey_data
    // $result = CRM_NYSS_BAO_Integration_Website::processSurvey($contact_id, $this->getEventAction(), $params);

    // create activity
    $activity_type_id = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Website Survey');
    $event_info = $this->getEventInfo();
    $form_fields = $event_info->form_values;
    $fields_as_text = array_reduce($form_fields, function ($carry, $field) {
      return $carry . self::formatFieldsAsText($field->field, $field->value);
    });

    $results = \Civi\Api4\Activity::create($this->getCiviPermissionCheck())
      ->addValue('activity_type_id', $activity_type_id)
      ->addValue('target_contact_id', $contact_id)
      ->addValue('activity_date_time', $this->getEventData()->getCreatedAtAsDateTime()->format('Y-m-d H:i:s'))
      ->addValue('details', '<pre>'.$fields_as_text.'</pre>')
      ->addValue('subject', $this->getFormTitle())
      // based on logic from CRM_NYSS_BAO_Integration_Website::processSurvey()
      ->addValue('source_contact_id', civicrm_api3('uf_match', 'getvalue', [
        'uf_id' => 1,
        'return' => 'contact_id',
      ]))
      // create custom data
      ->addValue('Website_Survey.Survey_Name', $this->getFormTitle())
      ->addValue('Website_Survey.Survey_ID', $this->getFormId())
      ->addValue('Website_Survey.survey_data', json_encode($form_fields))
      ->execute();

    // verification check only. Throws Exception if there's not 1 result
    $results->single();
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
   * @deprecated
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
   * @deprecated This was previously used to flatten a multi-level data structure
   * into a flat structure for the sake of saving to fields in a single database table.
   * No longer necessary since data is now being saved as text
   * @note formatFieldsAsText() is kind-of the replacement for this.
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
   * @deprecated Was previously needed for Civi Custom Fields.
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
   * Turns a Survey field data structure into reasonably readable text that
   * can be added to the Activity details field. It will return a simple
   * label / value pair as:
   * Label: Value
   * or it recurses into a more complex data structure to come up with something
   * reasonable.
   * Address:
   *   Address 1: 1 Main St
   *   Address 2: Suite 12
   *   City: Albany
   *   etc...
   * @param string $label The field label
   * @param mixed $value The field value, which could contain a deeper structure
   * @param int $level The current level in the structure. Mostly for indentation purposes.
   * @param string $carry_text Allows recursive calls to add to the resulting string.
   *
   * @return string
   */
  public static function formatFieldsAsText(string $label, mixed $value, int $level = 0, string &$carry_text = '') : string {
    if (is_scalar($value) or is_null($value)) {
      // It's a "simple value" field. Just add the field
      $carry_text .= str_repeat('  ', $level) . self::formatFieldForText($label, $value ?? '');
      return $carry_text; // don't recurse
    } else if (is_array($value)) {
      if (array_reduce($value, fn($c, $v) => $c && (is_scalar($v) || is_null($v)),
        TRUE)) {
        // It's an array of "simple values"... join them into a single field
        $carry_text .= str_repeat('  ', $level) .  self::formatFieldForText($label, join(', ', (array) $value));
        return $carry_text; // don't recurse
      }
    }
    // Complex values require some recursion to break them into additional fields.
    if (is_array($value) or is_object($value)) {
      $carry_text .= str_repeat('  ', $level) . self::formatFieldForText($label,'');
      ++$level;
      foreach($value as $child_key => $child_value) {
        self::formatFieldsAsText($child_key, $child_value, $level, $carry_text);
      }
    }
    return $carry_text;
  }

  /**
   * For consistent formating of webform/survey fields to be added to Activity.details.
   * @param string $label
   * @param string $value
   * @return string
   */
  public static function formatFieldForText(string $label,string $value) {
    // preg_replace removes trailing : and whitespace
    // strtr replaces _ with space
    // ucwords will tranform each word to have a capital first letter
    $clean_label = ucwords(preg_replace('/[\s:]*$/', '',strtr($label,['_'=>' '])));
    return $clean_label . ': ' . self::cleanFieldValue($value ?? '') . "\n";
  }

  /**
   * Create a field in the proper format for
   * CRM_NYSS_BAO_Integration_Website::processSurvey()
   * @param string $label
   * @param string $value
   * @return object
   * @deprecated No longer using processSurvey()
   */
  protected function getFieldDef(string $label, string $value) : object {
    return (object) [
      "field" => $label,
      "value" => $this->cleanFieldValue($value ?? '')
    ];
  }

  protected static function cleanFieldValue(string $string): string {
    // keep newlines
    $string = str_replace(array("<p>", "</p>", "<br>", "<br/>"), "\n", $string);
    // remove HTML (probably unnecessary, but also, civicrm will convert to character
    // entities, which makes the string longer than expected e.g. "<" becomes
    // &lt; (4 characters)
    $string = strip_tags($string);
    return $string;
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