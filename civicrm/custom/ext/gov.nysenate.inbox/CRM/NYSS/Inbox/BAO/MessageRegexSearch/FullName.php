<?php

class CRM_NYSS_Inbox_BAO_MessageRegexSearch_FullName extends CRM_NYSS_Inbox_BAO_MessageRegexSearch_Complex {

  public array $named_subpatterns = [
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_HONOR,
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME,
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_MNAME,
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_LNAME,
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_SUFFIX
  ];

  public function __construct(string $regex, string $subject, int $flags, float $score, $on_match = null) {
    return parent::__construct(
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME,
      $regex,$subject,$flags,$score,$on_match);
  }

}