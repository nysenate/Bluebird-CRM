<?php

class CRM_NYSS_Inbox_BAO_MessageTokenArray extends ArrayObject {

  protected $idx_by_line = [];

  public function append($value): void {
    if ($value instanceof CRM_NYSS_Inbox_BAO_MessageToken_Interface &&
        $value->getLineNumber() > 0) {
      $this->idx_by_line[$value->getLineNumber()][] = $value;
    }
    parent::append($value);
  }

  public function getTokensByLine(int $line): array {
    return $this->idx_by_line[$line] ?? [];
  }

  public function fractionMatched(string $line, int $line_num) : float {
    $tokens = $this->getTokensByLine($line_num);
    $token_total = array_reduce($tokens, function ($carry, $item) {
      return $carry + strlen($item->getToken());
    });
    return  $token_total / strlen($line);
  }

}