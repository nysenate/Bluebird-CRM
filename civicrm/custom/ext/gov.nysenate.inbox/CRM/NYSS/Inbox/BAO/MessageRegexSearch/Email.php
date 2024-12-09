<?php

class CRM_NYSS_Inbox_BAO_MessageRegexSearch_Email extends CRM_NYSS_Inbox_BAO_MessageRegexSearch {

  protected bool $is_aggregator = false;
  protected array $aggregator_list = [];

  public function __construct(string $regex, string $subject, int $flags, float $score, $on_match = null) {
    return parent::__construct(
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_EMAIL,
      $regex,$subject,$flags,$score,$on_match);
  }

  protected function createToken(): CRM_NYSS_Inbox_BAO_MessageToken_Interface {

    $match = $this->pluckMatch();
    $offset = ($this->pluckOffset()) ? $this->pluckOffset() : strpos($this->subject,$match);

    return (! $this->inAggregatorList($match)) ?
      new CRM_NYSS_Inbox_BAO_MessageToken_EmailToken($match, $offset) :
      new CRM_NYSS_Inbox_BAO_MessageToken_AggregatorToken($match, $offset);
  }

  public function setAggregatorList(array $aggregator_list): CRM_NYSS_Inbox_BAO_MessageRegexSearch_Email {
    $this->aggregator_list = $aggregator_list;
    return $this;
  }

  public function inAggregatorList(string $str): bool {
    return in_array($str,$this->aggregator_list);
  }


}