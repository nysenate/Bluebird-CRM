<?php

class CRM_NYSS_Inbox_BAO_MessageToken_EmailToken
  extends \CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct($token, $offset);
    $this->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_EMAIL);
    return $this;
  }

}