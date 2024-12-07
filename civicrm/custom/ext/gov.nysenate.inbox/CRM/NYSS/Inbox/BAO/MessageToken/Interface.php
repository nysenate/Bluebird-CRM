<?php

interface CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  public static function create(string $token, ?int $offset = null): self;

  public function getLineNumber() : ?int;

  public function setLineNumber(int $line) : static;

  public function getData();

  public function getDataAsString() : string;

  public function getRelevancyScore(): ?float;

  public function setRelevancyScore($score): static;

}