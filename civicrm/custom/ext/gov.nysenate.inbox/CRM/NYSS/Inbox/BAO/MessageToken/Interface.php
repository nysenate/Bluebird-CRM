<?php

interface CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public static function create(string $type, string $token, ?int $offset = null): self;

  public static function createFromPregMatches(array $matches): CRM_NYSS_Inbox_BAO_MessageToken_Interface;

  public function getData();

  public function getDataAsString() : string;

  public function getRelevancyScore(): ?float;

}