<?php

interface CRM_NYSS_Inbox_BAO_MessageParserInterface {
  public function parse(string $text): void;
  public function getTokens(): CRM_NYSS_Inbox_BAO_MessageTokenArray;
  public function highlight(): string;
}