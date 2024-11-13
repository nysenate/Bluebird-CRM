<?php

class CRM_NYSS_Inbox_BAO_MessageRegexSearch {

  protected string $regex;
  protected string $subject;
  protected int $flags;
  protected string $type;
  protected float $score = 1;
  protected array $matches = [];
  protected bool $is_match = false;
  protected CRM_NYSS_Inbox_BAO_MessageToken_Interface $token;
  protected mixed $on_match = ['self', 'onMatch']; // callable

  public function __construct(string $type, string $regex, string $subject, int $flags, float $score, callable $on_match = null) {
    $this->type = $type;
    $this->regex = $regex;
    $this->subject = $subject;
    $this->flags = $flags;
    $this->score = $score;
    $this->on_match = $on_match;
    return $this;
  }

  /** Factory method that also does the searching */
  public static function search(string $type, string $regex, string $subject, int $flags, float $score, callable $on_match = null): static {
    $me = new static($type, $regex, $subject, $flags, $score, $on_match);
    $me->execute($subject);
    return $me;
  }

  public function execute(): void {
    $re = $this->regex;

    if (preg_match("/$re/",$this->subject, $this->matches, $this->flags)) {
      $this->is_match = true;
      $this->token = $this->createToken($this->type, $this->matches);
      $this->token->setRelevancyScore($this->score);
      // call function to do extra work on match
      if (is_callable($this->on_match)) {
        ($this->on_match)($this);
      }

      //$this->onMatch($this->type, $this->matches);
    }
  }

  /**
   * @param mixed|NULL $key
   * grabs the string match from $matches array, whose structure is dependent
   * on preg_match flags as well as groups and named groups in the pattern.
   * Considers the PREG_OFFSET_CAPTURE flag, which will result in an array
   * that contains the match as well as the offset of the match
   * @return mixed
   * @throws \Exception
   */
  protected function pluckMatch(mixed $key = null) : string {
    if ($key && array_key_exists($key, $this->matches)) {
      return (isset($this->matches[$key][0])) ?
        $this->matches[$key][0] : $this->matches[$key];
    } else {
      return (isset($this->matches[0][0])) ?
        $this->matches[0][0] : $this->matches[0];
    }
  }

  /**
   * @param mixed|NULL $key
   * When PREG_OFFSET_CAPTURE flag is set, will return
   * the offset that corresponds to the given $key or entire match.
   * Will return null if PREG_OFFSET_CAPTURE is not set.
   * @return ?int
   */
  protected function pluckOffset(mixed $key = null) : ?int {
    if ($this->flags & PREG_OFFSET_CAPTURE) {
      if ($key && array_key_exists($key, $this->matches)) {
        return (isset($this->matches[$key][1])) ?
          $this->matches[$key][1] : $this->matches[$key];
      } else {
        return (isset($this->matches[0][1])) ?
          $this->matches[0][1] : $this->matches[1];
      }
    } else {
      return null;
    }
  }

  protected function createToken(): CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    return CRM_NYSS_Inbox_BAO_MessageToken_Factory::create(
      $this->type, $this->pluckMatch(), $this->pluckOffset());
  }

  public function getToken(): CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    return $this->token;
  }

  static protected function onMatch($type, &$matches) {

  }

  public function isMatch() : bool {
    return $this->is_match;
  }
}