<?php

class CRM_NYSS_BAO_Integration_SenateDistrict
{
    protected int $number;
    protected string $shortname; // Senator last name

    public function __construct(int $number, string $shortname)
    {
        $this->number = $number;
        $this->shortname = $shortname;
        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): CRM_NYSS_BAO_Integration_SenateDistrict
    {
        $this->number = $number;
        return $this;
    }

    public function getShortname(): string
    {
        return $this->shortname;
    }

    public function setShortname(string $shortname): CRM_NYSS_BAO_Integration_SenateDistrict
    {
        $this->shortname = $shortname;
        return $this;
    }
}