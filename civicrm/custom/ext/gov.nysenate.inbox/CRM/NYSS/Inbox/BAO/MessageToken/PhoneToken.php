<?php

class CRM_NYSS_Inbox_BAO_MessageToken_PhoneToken
  extends \CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_PHONE,
                        $token, $offset);
    return $this;
  }

}