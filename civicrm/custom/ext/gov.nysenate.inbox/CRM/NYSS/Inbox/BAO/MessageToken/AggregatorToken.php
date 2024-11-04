<?php

/**
 * Used for known political petition aggregator E-mail addresses and other
 * bulk-sending E-mail addresses that should be ignored and not used as
 * a contact's E-mail address
 * */
class CRM_NYSS_Inbox_BAO_MessageToken_AggregatorToken
  extends \CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public function __construct(string $token, ?int $offset = null) {
    parent::__construct(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_AGGREGATOR,
                        $token, $offset);
    return $this;
  }

}