<?php

class CRM_NYSS_Inbox_BAO_MessageToken_Factory {

  public final const TYPE_FULLNAME = 'name';
  public final const TYPE_FNAME = 'firstname';
  public final const TYPE_LNAME = 'lastname';
  public final const TYPE_MNAME = 'middlename';
  public final const TYPE_HONOR = 'honorific';
  public final const TYPE_SUFFIX = 'suffix';
  public final const TYPE_EMAIL = 'email';
  public final const TYPE_STREET = 'street';
  public final const TYPE_CSZ = 'csz'; // city, state zip combined
  public final const TYPE_CITY = 'city';
  public final const TYPE_STATE = 'state';
  public final const TYPE_ZIP = 'zip';
  public final const TYPE_COMP_CLOSE = 'complimentaryclosing';
  public final const TYPE_PHONE = 'phone';
  public final const TYPE_AGGREGATOR = 'aggregator';

  public static function getClassName(string $type): string {
    return match ($type) {
      self::TYPE_FULLNAME => '\CRM_NYSS_Inbox_BAO_MessageToken_FullName',
      self::TYPE_CSZ => '\CRM_NYSS_Inbox_BAO_MessageToken_CityStateZipToken',
      self::TYPE_STREET => '\CRM_NYSS_Inbox_BAO_MessageToken_StreetAddressToken',
      self::TYPE_EMAIL => '\CRM_NYSS_Inbox_BAO_MessageToken_EmailToken',
      self::TYPE_AGGREGATOR => '\CRM_NYSS_Inbox_BAO_MessageToken_AggregatorToken',
      self::TYPE_PHONE => '\CRM_NYSS_Inbox_BAO_MessageToken_PHoneToken',
      default => '\CRM_NYSS_Inbox_BAO_MessageToken_GenericToken',
    };
  }

  public static function create(string $type, string $token, ?int $offset = null): CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    $class = self::getClassName($type);
    return $class::create($type, $token,$offset);
  }

  public static function createFromPregMatch(string $type, array $matches): CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    // I want to pass this function the results
    // of preg_match and generate necessary token(s) here.
    $class = self::getClassName($type);
    try {
      return $class::createFromPregMatches($matches);
    } catch(Throwable $e) {
     throw new Exception('$class is not an instance of MessageToken');
    }
  }

}
