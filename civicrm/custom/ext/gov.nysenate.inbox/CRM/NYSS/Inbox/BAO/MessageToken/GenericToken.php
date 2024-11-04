<?php

class CRM_NYSS_Inbox_BAO_MessageToken_GenericToken
  implements \CRM_NYSS_Inbox_BAO_MessageToken_Interface {

  /**
   * @var string a matched token
   */
  protected string $token;

  /**
   * @var int|null the line number where the match was found in a multi-line message
   */
  public int|null $line_num;

  /**
   * @var int the position / offset of the match within the context of the line
   */
  public int $offset;

  /**
   * @var string the type of pattern that was matched (Email, Name, City, etc)
   */
  protected string $type;

  /**
   * @var float|null a relevancy score between 0 and 1 that indicates the likelihood
   * that this is a relevant match 0 = unlikely, 1 = likely.
   */
  public float|null $relevancy_score; // 0 to 1

  public function __construct(string $type, string $token, ?int $offset = null) {
    $this->token = $token;
    $this->offset = $offset;
    $this->type = $type;
    return $this;
  }

  /** Factory Method */
  public static function create(string $type, string $token, ?int $offset = null) : CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    return new static($type, $token,$offset);
  }

  /** Factory Method */
  public static function createFromPregMatches(array $matches) : CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    $parts = new CRM_NYSS_Inbox_BAO_MessageTokenArray();

    if (isset($matches[0][0])) {
      $me = new static($matches[0][0],$matches[0][1]);
    } elseif (isset($matches[0])) {
      $me = new static($matches[0]);
    } else {
      throw new Exception('Can not create Message Token without match');
    }

    return $me;
  }

  /**
   * Wraps a token in HTML
   * @note This should really be a function of the front-end, but leaving here
   * for the sake of not over-engineering until/unless necessary.
   * @param string $element_type An HTML element type, eg. span, div, p
   * Expects an "enclosing element"
   * @param array $attrs An associative array of attribute names to values. These
   * will be formatted and added to the HTML element, eg. class="round tall"
   * @return string
   */
  public function getHighlightedHTML(string $tag_type, array $attrs): string {
    $html_template =  "<:tag:attrs>:token</:tag>";
    $attr_str = '';
    foreach($attrs as $attr => $val) {
      $attr_str .= " $attr=\"".htmlspecialchars($val, ENT_QUOTES).'"';
    }
    return strtr($html_template, [':attrs' => $attr_str,
                                  ':tag' => $tag_type,
                                  ':token' => $this->token]);
    //data-search="'.self::TOKEN_TEXT.'"
  }

  public function getData() {
    return $this->token;
  }

  public function getDataAsString() : string {
    return $this->getData();
  }

  public function getType(): string {
    return $this->type;
  }

  public function setType(string $type): CRM_NYSS_Inbox_BAO_MessageToken_GenericToken {
    $this->type = $type;
    return $this;
  }

  public function setToken(string $token): CRM_NYSS_Inbox_BAO_MessageToken_GenericToken {
    $this->token = $token;
    return $this;
  }

  public function getToken(): string {
    return $this->token;
  }

  public function getRelevancyScore(): ?float {
    return $this->relevancy_score;
  }

}