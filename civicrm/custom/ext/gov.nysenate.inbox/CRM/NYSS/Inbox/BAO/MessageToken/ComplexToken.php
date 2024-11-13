<?php

/**
 * There are some cases where a token might be made up of parts. In this case,
 * each part is another token. For example, CityStateZipToken is a complex
 * token that can exist in and of itself, but also contains a city, a state
 * and a zip. Each of these needs to be tracked separately for the sake of
 * marking up / highlighting the match. This class facilitates these
 * situations where we have tokens within tokens. A complex token must define
 * its expected (valid) parts prior to creation.
 */
abstract class CRM_NYSS_Inbox_BAO_MessageToken_ComplexToken
  extends CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  /**
   * @var \CRM_NYSS_Inbox_BAO_MessageTokenArray smaller tokens contained within
   * this bigger token. eg. city, state, zip are all parts of "Albany, NY 12207"
   */
  public CRM_NYSS_Inbox_BAO_MessageTokenArray $parts;

  /** Factory Method -- logic moved to MessageRegexSearch
  public static function createFromPregMatches(array $matches) : CRM_NYSS_Inbox_BAO_MessageToken_ComplexToken {
    if (isset($matches[0][0])) {
      $me = new static($matches[0][0],$matches[0][1]);
    } elseif (isset($matches[0])) {
      $me = new static($matches[0]);
    } else {
      throw new Exception('Can not create Message Token without match');
    }

    foreach(static::listValidParts() as $type) {
      if (isset($matches[$type][0]) && isset($matches[$type][1])) {
        $part = CRM_NYSS_Inbox_BAO_MessageToken_Factory::create($type,
          $matches[$type][0],
          $matches[$type][1]);
        $me->addPart($part);
      } elseif (isset($matches[$type])) {
        $part = CRM_NYSS_Inbox_BAO_MessageToken_Factory::create($type,
          $matches[$type]);
        $me->addPart($part);
      }
    }
    return $me;
  }
*/

  public static function listValidParts(): array {
    return [];
  }

  public function addPart(CRM_NYSS_INBOX_BAO_MessageToken_Interface $token): self {
    if (empty($this->parts)) {
      $this->parts =  new CRM_NYSS_Inbox_BAO_MessageTokenArray();
    }
    $this->parts->append($token);
    return $this;
  }

  public function hasPart(string $type) : bool {
    foreach($this->parts->getArrayCopy() as $part) {
      if (isset($part->type) && $part->type == $type) {
        return TRUE;
      }
    }
    return false;
  }

  public function getData(array $field_map = []) {
    $data = [];
    foreach($this->parts as $part) {
      if (array_key_exists($part->type,$field_map)) {
        $data[$field_map[$part->type]] = $part->getData();
      } else {
        $data[$part->type] = $part->getData();
      }
    }
    return $data;
  }

  public function getDataAsString(array $field_map = []) : string {
    return json_encode($this->getData($field_map));
  }

}
