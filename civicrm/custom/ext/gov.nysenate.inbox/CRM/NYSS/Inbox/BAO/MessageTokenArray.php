<?php

class CRM_NYSS_Inbox_BAO_MessageTokenArray extends ArrayObject {

  /**
   * @var array Tokens indexed by line number
   */
  protected $idx_by_line = [];

  /**
   * @param $value
   * Overrides parent's append() to create $idx_by_line as tokens are added.
   * @return void
   */
  public function append($value): void {
    if ($value instanceof CRM_NYSS_Inbox_BAO_MessageToken_Interface &&
        $value->getLineNumber() > 0) {
      $this->idx_by_line[$value->getLineNumber()][] = $value;
    }
    parent::append($value);
  }

  /**
   * @param int $line
   * convenience method for getting all tokens found in a particular line --
   * based on line number
   * @return array
   */
  public function getTokensByLine(int $line): array {
    return $this->idx_by_line[$line] ?? [];
  }

  /**
   * @param string $line
   * @param int $line_num
   * Returns a fraction between 0 and 1 that indicates the percentage of the
   * line that was matched. Eg. 1 (100%) indicates that the entire line was
   * matched and included in a matched token.
   * @return float
   */
  public function fractionMatched(string $line, int $line_num) : float {
    $tokens = $this->getTokensByLine($line_num);
    $token_total = array_reduce($tokens, function ($carry, $item) {
      return $carry + strlen($item->getToken());
    });
    return  $token_total / strlen($line);
  }

}