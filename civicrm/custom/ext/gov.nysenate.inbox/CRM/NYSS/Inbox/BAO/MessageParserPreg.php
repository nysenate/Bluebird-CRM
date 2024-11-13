<?php

use CRM_NYSS_Inbox_BAO_MessageTokenArray as MessageTokenArray;

class CRM_NYSS_Inbox_BAO_MessageParserPreg implements CRM_NYSS_Inbox_BAO_MessageParserInterface {

  public const VL_QUIET = .1;
  public const VL_MODERATE = .4;
  public const VL_VERBOSE = .7;

  /**
   * Matches an E-mail address
   * */
  public const RE_EMAIL = '[\w\.\-\+\%]+@[\w-]+(?:\.[[:alpha:]]{2,})+';

  // Expected to start with a number, but then allows for letters
  public const RE_STREET_NUM = '\d{1,4}([[:alnum:]]{0,3})?';
  public const RE_STREET_TYPES = 'St|Av|Ro|Rd|Dr|Bou|Blv|La|Ln|Co|Ct|Pl|Ter|Way|Cir|Pa|Pk|Hi|Hwy|Cre|Al|Tr|Pl|Sq|Ci|Cr';
  // Tries to match the most common street types such as Street, Avenue, Road, St, St., Ave, Ave., etc
  public const RE_STREET_TYPE = '(?i:'.self::RE_STREET_TYPES.'){1}[[:alpha:]]{0,10}\.?';
  public const RE_STREET_NAME = '[[:alnum:]\-\.\h]{2,32}';
  // attempts to match first address line by itself, eg. 22 Main St., 3300F Queens Boulevard
  public const RE_STREET = '(?:'.self::RE_STREET_NUM.')\h+(?:'.self::RE_STREET_NAME.')\h+(?:'. self::RE_STREET_TYPE . ')?';

  //'(?<'.$city.'>[[:alpha:]. ]+[[:alpha:]]+)'
  public const RE_CITY = '(?<$c>[A-Z][[:alpha:].]+(?:\h+[[:alpha:]]+)?)';
  public const RE_NY = '(?<$s>[Nn](ew\h)?[Yy](ork)?)';
  // Should match any state in the US, but tries to narrow down the search by
  // looking for specific first words and first letters.
  public const RE_STATE = '(?<$s>((?i:New|North|South|Rhode|West|N|S|R|W)?\h+)?(?i:[ACDFGHIJKLMNOPTUVWY]([[:alpha:]]*)))';
  public const RE_ZIP = '(?<$z>\d{5}(?:-\d{4})?)';
  public const RE_CSZ = self::RE_CITY.'\h*,?\h+'.self::RE_STATE.'\h+'.self::RE_ZIP;
  // should match  +1       (  555  ) - or .    555    - or . 4444
  public const RE_PHONE = '(?:[+]1\h*)?\(?\d{3}\)?[\-\.\ ]?\h*\d{3}[\-\.\ ]?\d{4}';


  public const RE_HONORIFIC = '(?:Mr|Mrs|Ms|Mx|Miss|Dr|Prof|Rev|Hon)\.?';
  public const RE_FIRST_NAME = '[A-Z][a-z]+';
  public const RE_LAST_NAME = "[A-Z][A-Za-z\-']+(?: [A-Z][a-z]+)?";
  public const RE_MIDDLE_NAME = '[A-Z][a-z]*\.?';
  public const RE_SUFFIX_NAME = '(?:Sr|Jr|II|III|IV|V|Ph\.D|M\.D|Esq)\.?';
  public const RE_FULL_NAME = '(?:(?<$h>'.self::RE_HONORIFIC.')\h*)?(?<$f>'.self::RE_FIRST_NAME.')(?:\h*(?<$m>'.self::RE_MIDDLE_NAME.'))?\h+(?<$l>'.self::RE_LAST_NAME.')(?:\h*(?<$s>'.self::RE_SUFFIX_NAME.'))?';

  // Tries to match a professional or personal closing such as, Sincerely.
  // Includes the possibility of some other words before the more common closers.
  // eg... "With" in "With Regards"
  // This is by no means exhaustive nor perfect.
  // public const RE_CLOSE = '[\w\h]*(?i:Sincerely|Regards|Best|Yours|Respectfully|Consideration|Gratitude)\h*,?';

  protected string $raw_message = '';
  protected array $lines = [];
  protected MessageTokenArray $tokens;
  protected float $verbosity = self::VL_MODERATE;

  // a hint that we can set if it looks like the
  // message contains the results of a form fill.
  // Tells us whether to run the Regexs that
  // look for form-fills. For Example,
  // First Name: John
  // Last NameL: Smith
  // Email Address: js@gmail.com
  // etc...
  protected bool $match_form_fill = false;
  //protected int $start_end_boundary = 20;

  /**
   * list of petition aggregator platform email addresses or any
   * other email addresses that should be deprioritized.
   * @var array
   */
  protected array $aggregator_list = [];

  public function __construct(float $verbosity = self::VL_MODERATE) {
    $this->setVerbosity($verbosity);
    $this->tokens = new MessageTokenArray();
  }

  public function parse(string $text): void {
    $this->raw_message = $text;
    // Convert message body into tagless text.
    $text = preg_replace('/<(div|p|br)[^>]*>|\r\n?/', "\n", $this->raw_message);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML401, 'ISO-8859-1');

    // Determine if this message contains the results of a Web Form
    // The clues that it looks for are common web form field labels, each
    // on a line by itself, eg. First Name: John, Last Name: Smith, etc
    if (preg_match('/\v+(Name|First Name|Last Name)\W{1}\s+/', $text) &&
      preg_match('/\v+(Address|City)\W{1}\s+/', $text)) {
      $this->match_form_fill = true;
    }


    // Take it line by line -- assume that none of the entities that we're
    // looking for would span lines.
    $this->lines = preg_split('/\r\n|\r|\n/', $text);
    $line_count = count($this->lines);
    $last_addr1_line = null;
    $last_csz_line = null;
    $last_closing_line = null;
    $blank_lines = [];
    for ($i = 0; $i < count($this->lines); $i++) {

      $line = $this->lines[$i];

      $line = trim($line);

      // track blank lines for the sake of decision-making.
      // But don't process
      if (strlen($line) == 0) {
        $blank_lines[] = $i;
        continue;
      }

      // skip lines shorter than 3 (probably a reasonable cutoff point)
      if (strlen($line) < 3) continue;

      // only include search in lines longer than 80 if verbosity is high.
      // The theory is that the relevant contact information is more likely
      // to be found as part of a signature or opener rather than in
      // the text of the email. This is opinionated but worth trying.
      if (strlen($line) > 80 && (! $this->isVerbose())) {
        continue;
      }

      /* Some rules for optimization
       * ✅ Only search for email if @ in string
       * ✅ Only search for city/state/zip if matches a zip \d{5}
       * ✅ Only search for phone number if matches \d{4}
       * Special considerations
       * ✅ ^Repy-To:
       * ✅ ^From:
       * ✅ ^Name:
       * ✅ ^First Name:
       * ✅ ^Last Name:
       * ✅ ^Email (Address):
       * ✅ ^Phone (Number):
       * ✅ ^City:
       * ✅ ^State:
       * ✅ ^ZIP|Postal (Code):
       * ^Address:
       * ✅ ^Street Address:
       */
      // we could do this contingent on whether we've already found a match
      // with a high probability score and intensity level... but I won't.
      // I just saved this comment to remember my train of thought.
      //if ($this->isVerbose() && (!$this->foundLikelyEmail())) {
      if ($this->findEmail($line, $i) &&
        $this->tokens->fractionMatched($line, $i ) > .85) {
        // If a phone was found and the whole line was matched
        continue;
      }
      //}

      if ($this->findPhone($line, $i) &&
        $this->tokens->fractionMatched($line, $i ) > .85) {
        // If a phone was found and the whole line was matched
        continue;
      }

      if ($this->findStreetAddress($line, $i) &&
        $this->tokens->fractionMatched($line, $i ) == 1) {
        // If a phone was found and the whole line was matched
        continue;
      }

      if ($this->findCityStateZip($line, $i) &&
        ($this->tokens->fractionMatched($line, $i ) == 1)) {
        // If a phone was found and the whole line was matched
        continue;
      }

      /* Some other logic that we could resurrect back if needed
      if (is_numeric($last_closing_line) && (($last_closing_line - $i) < 1)) {
        // If the previous line was a closing like "Sincerely" then
        // increase the score
        $name_token->relevancy_score = max($name_token->relevancy_score,.95);
      }
      */
      if ($this->findProperName($line, $i) &&
        ($this->tokens->fractionMatched($line, $i ) == 1)) {
        // If a phone was found and the whole line was matched
        continue;
      }

      // Finding the complimentary close can help give us more certainty
      // about a person's name
      /*
      $close_token = $this->findClosing($line);
      if ($close_token) {
        $close_token->line_num = $i;
        $last_closing_line = $i; // record the line number for reference later
        $this->tokens->append($close_token);
        continue; // Move on to next line
      }
      */

      // Note for later. If nothing found in first pass, we could
      // make the intensity level higher and try again.

    }
  }

  public function getTokens(): CRM_NYSS_Inbox_BAO_MessageTokenArray {
    return $this->tokens;
  }

  public function highlight() : string {
    $highlighted_text = '';

    for ($i = 0; $i < count($this->lines); $i++) {
      $line = $this->lines[$i];
      $tokens = $this->tokens->getTokensByLine($i);
      if (sizeof($tokens)) {
        // find and replace tokens if necessary
        foreach($tokens as $t) {
          if ($this->shouldHush($t)) {
            // implement verbosity regulation
            continue;
          }
          $hl_token = $this->getHighlightedToken($t);
          $line = strtr($line, [$t->getToken() => $hl_token]);

        }
      }
      $highlighted_text .= $line . "<br/>";
    }

    return $highlighted_text;
  }

  /**
   * Judges tokens based on verbosity and the relevancy_score of the token.
   * When verbosity is high, will be less picky
   * When verbosity is low, will be more picky
   * This is a simple, opinionated algorithm that treats
   * relevancy scores and verbosity levels as an inverse-ish relationship.
   * Where it will suggest excluding tokens whos relevancy is less than
   * the distance of verbosity level to 1. For example, a verbosity level
   * of .7 has a distance of .3 from 1. So, .3 is the hushing threshold.
   * Any token with a relevancy score below .3 should be hushed.
   * It doesn't actually do any hushing, it just provides an opinion.
   * @param \CRM_NYSS_Inbox_BAO_MessageToken_Interface $token
   * @return bool
   */
  protected function shouldHush(CRM_NYSS_Inbox_BAO_MessageToken_Interface $token): bool{
    // verbosity = .7, then distance = .3, then hush anything with a
    // relevancy score less than .3.
    $distance = 1 - $this->getVerbosity();
    return $token->getRelevancyScore() < $distance;
  }

  protected function getHighlightedToken(CRM_NYSS_Inbox_BAO_MessageToken_Interface $token) : string {
    // This serves as a front-end / back-end bridge for now.
    // Should really be moved to the front-end though.
    $map = [
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_EMAIL => [
        'class' => 'found email_address',
        'title' => 'Click to use this email',
        'data_attr' => 'data-search',
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_AGGREGATOR => [
        'class' => 'found aggregator_email',
        'title' => 'Click to use this aggregator email',
        'data_attr' => 'data-search',
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME => [
        'class' => 'found name',
        'title' => 'Click to use this name',
        'data_attr' => 'data-json',
        'data_field_map' => [
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME => 'first',
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_LNAME => 'last',
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_MNAME => 'middle',
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_HONOR => 'prefix',
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_SUFFIX => 'suffix',
        ]
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_PHONE => [
        'class' => 'found phone',
        'title' => 'Click to use this phone number',
        'data_attr' => 'data-search',
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STREET => [
        'class' => 'found street_address',
        'title' => 'Click to use this street address',
        'data_attr' => 'data-search',
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY => [
        'class' => 'found city',
        'title' => 'Click to use this city',
        'data_attr' => 'data-search',
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP => [
        'class' => 'found justzip',
        'title' => 'Click to use this zip code',
        'data_attr' => 'data-search',
      ],
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ => [
        'class' => 'found zip',
        'title' => 'Click to use this city/state/zip',
        'data_attr' => 'data-json',
        'data_field_map' => [
        ]
      ],
    ];

    $attrs = [
      'class' => $map[$token->getType()]['class'],
      'title' => $map[$token->getType()]['title'],
      $map[$token->getType()]['data_attr'] => $token->getDataAsString($map[$token->getType()]['data_field_map'])
    ];
    return $token->getHighlightedHTML('span', $attrs);
  }

  /**
   * rules for scoring:
   * 1) if the entire string is an email message --> high
   * 2) if the string matches something like Email: user@domain.com --> high
   * 3) if the string is buried within other stuff --> low to medium
   * @param string $text string to be searched
   *
   * @return bool whether or not a token was found
   */
  protected function findEmail(string $text, $line_num): bool {
    $mail_re = self::RE_EMAIL;
    $flags = PREG_OFFSET_CAPTURE;

    // pre-qualifier for efficiency. Must at least contain '@'
    // in order to preform the more complex/costly searches
    if (strpos($text,'@')) {

      if ($this->match_form_fill) {
        // Look for Web-Form results first because the general search
        // will find it, but we want the higher relevancy score
        $search = new CRM_NYSS_Inbox_BAO_MessageRegexSearch_Email(
          "^E-?mail(?:\sAddress)?\W*\K($mail_re)$", $text, $flags, 1);
        $search->execute();
        if ($search->isMatch()) {
          $this->tokens->append($search->getToken()->setLineNumber($line_num));
          return true;
        }
      }
      // only if it wasn't previously found in web-form results
      $search = new CRM_NYSS_Inbox_BAO_MessageRegexSearch_Email(
        "$mail_re", $text, $flags, .7);
      // Email Regex Search Object handles aggregator services appropriately.
      $search->setAggregatorList($this->aggregator_list);
      $search->execute();
      if ($search->isMatch()) {
        $token = $search->getToken();
        if ($token->offset == 0 && strlen($token->getToken()) == strlen($text)) {
          $token->setRelevancyScore(.9);
        }
        $this->tokens->append($search->getToken()->setLineNumber($line_num));
        return true;
      }
    }
    return false;
  }

  protected function findPhone(string $text, $line_num): bool {
    $type = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_PHONE;
    $phone_re = self::RE_PHONE;
    $flags = PREG_OFFSET_CAPTURE;
    $form_fill_match = false;

    // pre-qualifier for efficiency. Must at least match last 4 digits
    // in order to preform the more complex/costly searches
    if (preg_match('/\d{4}/', $text)) {

      if ($this->match_form_fill) {
        // Look for Web-Form results
        $search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          $type, "^Phone(?:\sNumber)?\W*\K($phone_re)$", $text, $flags, 1);
        if ($search->isMatch()) {
          $this->tokens->append($search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      }

      $search_1 = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
        $type, "$phone_re", $text, $flags, .7);
      if ($search_1->isMatch()) {
        $t = $search_1->getToken()->setLineNumber($line_num);
        if ($t->offset == 0 && strlen($t->getToken()) == strlen($text)) {
          $t->setRelevancyScore(.9);
        }
        $this->tokens->append($t);
        return true; // prevent next match
      }
    }

    return false;
  }

  protected function findStreetAddress(string $text, int $line_num): bool {
    $type = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STREET;
    $addr_re = self::RE_STREET;
    $flags = PREG_OFFSET_CAPTURE;

    if ($this->match_form_fill) {
      // pre-qualifier
      if (preg_match('/^(Street|Address)/', $text)){
        // Look for Web-Form results
        $search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          $type, "^(?:Street|Street Address|Address)\W*\K$addr_re$", $text, $flags, 1);
        if ($search->isMatch()) {
          $this->tokens->append($search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      }
    }
    // pre-qualifier for efficiency. Must at least start with a number
    // in order to preform the more complex/costly searches
    if (preg_match('/^\d+/', $text)) {
      // matches if the line only contains a phone number
      $search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
        $type, "$addr_re", $text, $flags, .7);
      if ($search->isMatch()) {
        $token = $search->getToken();
        if ($token->offset == 0 && strlen($token->getToken()) == strlen($text)) {
          $token->setRelevancyScore(.9);
        }
        $this->tokens->append($token->setLineNumber($line_num));
        return true;
      }
    }
    return false;
  }

  protected function findCityStateZip(string $text, int $line_num): bool {

    $csz_re = strtr(self::RE_CSZ, [
      '$c' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY,
      '$s' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE,
      '$z' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP,
    ]);

    $city_re = str_replace('?<$c>','', self::RE_CITY);
    $state_re = str_replace('?<$s>','', self::RE_STATE);
    $zip_re = str_replace('?<$z>','', self::RE_ZIP);

    $flags = PREG_OFFSET_CAPTURE;

    if ($this->match_form_fill) {
      // Look for Web-Form results
      // pre-qualifier
      if (preg_match('/^(City|Locality|Town)/', $text)){
        $city_search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY,
          '^(?:City|Locality|Town)\W*\K'.$city_re.'$',
          $text, $flags, 1);
        if ($city_search->isMatch()) {
          $this->tokens->append($city_search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      } elseif (preg_match('/^(State)/', $text)){
        $state_search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE,
          '^(?:State)\W*\K'.$state_re.'$',
          $text, $flags, 1);
        if ($state_search->isMatch()) {
          $this->tokens->append($state_search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      } elseif (preg_match('/^(Zip|Postal)/', $text)){
        $zip_search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP,
          '^(?i:Zip|Postal)(?:\h?Code)?\W*\K'.$zip_re.'$',
          $text, $flags, 1);
        if ($zip_search->isMatch()) {
          $this->tokens->append($zip_search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      }
    }

    // pre-qualifier for efficiency. Must at least match zip code 12345
    // in order to do the more complex searches
    if (preg_match('/\d{5}/', $text)) {
      // match City, State Zip, eg. Albany New York 11111-2222 or
      // Albany, NY 22222 or some variation
      $csz_search = new CRM_NYSS_Inbox_BAO_MessageRegexSearch_Complex(
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ, "$csz_re", $text, $flags, .7);
      // Email Regex Search Object handles aggregator services appropriately.
      $csz_search->setNamedSubppaterns([
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY,
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE,
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP
      ]);
      $csz_search->execute();
      if ($csz_search->isMatch()) {
        $token = $csz_search->getToken();
        if ($token->offset == 0 && strlen($token->getToken()) == strlen($text)) {
          $token->setRelevancyScore(.9);
        }
        $this->tokens->append($token->setLineNumber($line_num));
        return true;
      }
    }
    return false;
  }

  /*
  protected function findClosing(string $text): ?CRM_NYSS_Inbox_BAO_MessageToken_Interface {
    $type = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_COMP_CLOSE;
    $close_re = self::RE_CLOSE;
    $flags = PREG_OFFSET_CAPTURE;

    $search_1 = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
      $type, "^$close_re$", $text, $flags, 1);
    if ($search_1->isMatch()) {
      return $search_1->getToken();
    }

    return null;
  }
  */

  protected function findProperName(string $text, int $line_num): bool {
    $name_re = strtr(self::RE_FULL_NAME, [
      '$h' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_HONOR,
      '$f' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME,
      '$m' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_MNAME,
      '$l' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_LNAME,
      '$s' => CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_SUFFIX
    ]);
    $flags = PREG_OFFSET_CAPTURE;

    if ($this->match_form_fill) {
      // Look for Web-Form results
      // pre-qualifier
      if (preg_match('/^First Name/', $text)){
        $fn_search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME,
          '^(?:First Name)\W*\K'.self::RE_FIRST_NAME.'$',
          $text, $flags, 1);
        if ($fn_search->isMatch()) {
          $this->tokens->append($fn_search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      } elseif (preg_match('/^Last Name/', $text)){
        $ln_search = CRM_NYSS_Inbox_BAO_MessageRegexSearch::search(
          CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE,
          '^(?:Last Name)\W*\K'.self::RE_LAST_NAME.'$',
          $text, $flags, 1);
        if ($ln_search->isMatch()) {
          $this->tokens->append($ln_search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      } elseif (preg_match('/^Name/', $text)){
        $name_search = new CRM_NYSS_Inbox_BAO_MessageRegexSearch_FullName(
          '^(?:Name)\W*\K'.$name_re.'$',
          $text, $flags, 1);
        $name_search->execute();
        if ($name_search->isMatch()) {
          $this->tokens->append($name_search->getToken()->setLineNumber($line_num));
          return true; // prevent next match
        }
      }
    }

    // cases where email headers appear in a forwarded message
    // To: Christopher M. Carmichael Jr. <cmc@gmail.com>
    if (preg_match('/^(From|Reply To):/', $text)) {
      $search = new CRM_NYSS_Inbox_BAO_MessageRegexSearch_FullName(
        "^((Reply To|From):\h*)\"?\K$name_re(?=\"?\h<?)", $text, $flags, .85);
      $search->execute();
      if ($search->isMatch()) {
        $this->tokens->append($search->getToken()->setLineNumber($line_num));
        return true;
      }
    }

    $fn_search = new CRM_NYSS_Inbox_BAO_MessageRegexSearch_FullName(
      "^$name_re$", $text, $flags, .65);
    $fn_search->execute();
    if ($fn_search->isMatch()) {
      $token = $fn_search->getToken();
      if ($token->offset == 0 && strlen($token->getToken()) == strlen($text)) {
        if (preg_match('/New York|Senate|Senator|Legislative/i', $text)) {
          // filter out some common strings that are mistaken as names
          $token->relevancy_score = .1; // very low
        } elseif ($token->hasPart(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_HONOR) ||
          $token->hasPart(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_SUFFIX)) {
          // boost even more if an honorific or suffix was found because
          // that's a better indication of a person's name.
          $token->setRelevancyScore(.95);
        } else {
          // only thing on the line and doesn't meet above conditions
          $token->setRelevancyScore(.75);
        }
        $this->tokens->append($token->setLineNumber($line_num));
      }
      return true;
    }
    return false;
  }

  protected function getVerbosity(): float {
    return $this->verbosity;
  }

  public function setVerbosity(float $level): self {
    if ($level > self::VL_VERBOSE) {
      $this->verbosity = self::VL_VERBOSE;
    } elseif ($level < self::VL_MODERATE) {
      $this->verbosity = self::VL_QUIET;
    } else {
      $this->verbosity = self::VL_MODERATE;
    }
    return $this;
  }

  public function isVerbose(): bool {
    return $this->verbosity >= self::VL_VERBOSE;
  }

  public function isQuiet(): bool {
    return $this->verbosity < self::VL_MODERATE;
  }

  public function setAggregatorList(array $aggregator_list): CRM_NYSS_Inbox_BAO_MessageParserPreg {
    $this->aggregator_list = $aggregator_list;
    return $this;
  }

  public function getAggregatorList(): array {
    return $this->aggregator_list;
  }

  public function inAggregatorList(string $str): bool {
    return in_array($str,$this->aggregator_list);
  }
}