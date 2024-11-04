<?php

class CRM_NYSS_Inbox_BAO_MessageToken_CityStateZipToken
  extends CRM_NYSS_Inbox_BAO_MessageToken_ComplexToken
  implements \CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ,
                        $token, $offset);
    return $this;
  }

  public static function listValidParts(): array {
    return [
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY,
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE,
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP
    ];
  }

}