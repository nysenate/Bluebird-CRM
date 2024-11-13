<?php

class CRM_NYSS_Inbox_BAO_MessageToken_FullName
  extends CRM_NYSS_Inbox_BAO_MessageToken_ComplexToken
  implements \CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct($token, $offset);
    $this->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME);
    return $this;
  }

  public static function listValidParts(): array {
    return [
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_HONOR,
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME,
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_MNAME,
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_LNAME,
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_SUFFIX,
    ];
  }

}