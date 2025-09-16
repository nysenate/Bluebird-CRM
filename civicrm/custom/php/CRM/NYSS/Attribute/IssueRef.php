<?php

/** PHP Attribute used to track Issue / Ticket numbers
 *
 *  Why do this?
 *
 *  Historically, we have added comments to the code to denote the
 *  issue/ticket associated with a customization. Eg... // NYSS 12345
 *  This serves the purpose of helping us programmers identify customizations
 *  and points us to the reason for the customization.
 *
 *  But comments are not a perfect solution. Comments can be misformatted,
 *  thereby preventing search tools from finding all the comments.
 *  Comments are also limited to manual / visual inspection of code.
 *  A programmer needs to be looking at it for it to do any good.
 *  (Let's set aside how AI code inspection might change this.)
 *
 *  PHP 8 now gives us support for Attributes. PHP Attributes is a way of attaching
 *  structured, machine-readable metadata to bits of code. This machine-readability
 *  opens up a lot of possibility for code maintenance, change tracking, and
 *  regression testing. With the use of Attributes, these tasks can now be
 *  automated by scripts and built into automated workflows.
 *
 *  How to use?
 *  For each issue being addressed, try to self contain the solution in
 *  it's own function or class. Then, add an attribute to the function or class.
 *
 *  Example 1: Add multiple refs to same issue, first as an issue number and
 *  second as a URL linking to the issue
 *
 *  #[CRM_NYSS_Attribute_IssueRef(12345, 'https://dev.nysenate.gov/issues/12345')]
 *  function _solution_to_issue($vars...) {
 *
 *  }
 *
 *  Example 2: Add multiple refs to different issue numbers. Note that
 *  numeric values are assumed to be issue numbers.
 *
 *  #[CRM_NYSS_Attribute_IssueRefs('12345', 67890)]
 *  function _references_multiple_issues($vars...) {
 *
 *  }
 */
use Attribute;

#[Attribute(Attribute::TARGET_ALL|Attribute::IS_REPEATABLE)]
class CRM_NYSS_Attribute_IssueRef {

  /** @var string specifies a reference as
   *  type "number" (ticket/issue reference number)
   */
  const T_NUM = 'number';
  /** @var string specifies a reference as a URL  */
  const T_URL = 'url';

  /** list of references to ticket/issues.
   *  [
   *    [type=>[T_NUM|T_URL], value=>VALUE],
   *    ...
   *  ]
   */
  public $refs = [];

  public function __construct(int|string ...$refs)
  {
    foreach($refs as $r) {
      if (is_numeric($r)) {
        // if it looks numeric, classify it as 'number' type
        $this->add_ref((string)$r, self::T_NUM, );
      }
      else if (is_string($r) && filter_var($r, FILTER_VALIDATE_URL)) {
        // if it looks like a URL, classify it as 'url' type
        $this->add_ref((string)$r, self::T_URL);
      }
      else if (is_string($r)) {
        // All other strings are assumed to be ticket numbers for now
        $this->add_ref((string)$r, self::T_NUM);
      }
    }
  }

  protected function add_ref(string $value, string $type) {
    $this->refs[] = ['type'=>$type, 'value'=>$value];
    return $this;
  }

  public function __toString(): string {
    // TODO: Implement __toString() method.
    $str = '';
    foreach($this->refs as $r) {
      $str .= "TYPE: " . $r['type'] . "; VALUE: " . $r['value'] . "\n";
    }

    return $str;
  }

}