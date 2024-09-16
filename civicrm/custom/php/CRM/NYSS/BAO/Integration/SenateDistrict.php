<?php

class CRM_NYSS_BAO_Integration_SenateDistrict {

  /**
   * @var int district number
   */
  protected int $number;

  /**
   * @var string district shortname, which is typically a Senator's last name.
   */
  protected string $shortname; // Senator last name

  public function __construct(int $number, string $shortname) {
    $this->number = $number;
    $this->shortname = $shortname;
    return $this;
  }

  public function getNumber(): int {
    return $this->number;
  }

  public function setNumber(int $number): CRM_NYSS_BAO_Integration_SenateDistrict {
    $this->number = $number;
    return $this;
  }

  public function getShortname(): string {
    return $this->shortname;
  }

  public function setShortname(string $shortname): CRM_NYSS_BAO_Integration_SenateDistrict {
    $this->shortname = $shortname;
    return $this;
  }

}