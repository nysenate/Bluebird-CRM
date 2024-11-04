<?php

class CRM_NYSS_Inbox_BAO_MessageToken_StreetAddressToken
  extends \CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STREET,
                        $token, $offset);
    return $this;
  }

}