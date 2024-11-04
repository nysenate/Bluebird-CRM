<?php

use CRM_NYSS_Inbox_BAO_MessageTokenArray as MessageTokenArray;

class CRM_NYSS_Inbox_BAO_MessageParserPreg implements CRM_NYSS_Inbox_BAO_MessageParserInterface {

  public const VL_QUIET = .1;
  public const VL_MODERATE = .4;
  public const VL_VERBOSE = .7;

  protected string $raw_message = '';
  protected array $lines = [];
  protected MessageTokenArray $tokens;
  protected float $verbosity = self::VL_MODERATE;
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

      // track blank lines for the sake of decision-making.
      // But don't process
      if (ctype_space($line)) {
        $blank_lines[] = $i;
        continue;
      }

      $line = trim($line);

      // skip lines shorter than 3 (probably a reasonable cutoff point)
      if (strlen($line) < 3) continue;

      // only include search in lines longer than 80 if verbosity is high.
      // The theory is that the relevant contact information is more likely
      // to be found as part of a signature or opener rather than in
      // the text of the email. This is opinionated but worth trying.
      if (strlen($line) > 80 && (! $this->isVerbose())) {
          continue;
      }

      // we can do this contingent on whether we've already found a match
      // with a high probability score and intensity level... but I won't.
      // I just saved this comment to remember my train of thought.
      //if ($this->getIntensityLevel() < self::HIGH && (!$this->foundLikelyEmail())) {
      foreach($this->findEmail($line) as $r) {
        $r->line_num = $i;
        $this->tokens->append($r);
        if (strlen($r->getToken()) === strlen($line)) {
          continue 2; // stop processing line if the match is the whole line.
        }
      }
      //}

      foreach($this->findPhone($line) as $r) {
        $r->line_num = $i;
        $this->tokens->append($r);
        if (strlen($r->getToken()) === strlen($line)) {
          continue 2; // stop processing line if the match is the whole line.
        }
      }

      foreach($this->findStreetAddress($line) as $r) {
        $r->line_num = $i;
        $this->tokens->append($r);
        if (strlen($r->getToken()) === strlen($line)||$r->relevancy_score >= .85) {
          // track line number. Might help verify address 2 or city/state/zip
          $last_addr1_line = $i;
          continue 2; // stop processing line if the match is the whole line.
        }
      }

      foreach($this->findCityStateZip($line) as $r) {
        $r->line_num = $i;
        $this->tokens->append($r);
        if (strlen($r->getToken()) === strlen($line)||$r->relevancy_score >= .85) {
          // track line number. Might help verify address 2 or city/state/zip
          $last_csz_line = $i;
          continue 2; // stop processing line if the match is the whole line.
        }
      }

      // Finding the complimentary close can help give us more certainty
      // about a person's name
      if (count($this->findClosing($line)) == 1) {
        $last_closing_line = $i;
      }

      // Names are tricky and it's possible to pick up a lot of wrong
      // tokens because so many things can look like a name to a Regexp.
      // So, we'll try to be a little more selective.
      foreach($this->findProperName($line) as $r) {
        $r->line_num = $i;
        if (is_numeric($last_closing_line) && (($last_closing_line - $i) < 1)) {
          // If the previous line was a closing like "Sincerely" then
          // increase the score
          $r->relevancy_score = max($r->relevancy_score,.95);
        }
        $this->tokens->append($r);
      }

      // Note for later. If nothing found in first pass, we could
      // make the intensity level higher and try again.

    }
  }

  public function getTokens(): CRM_NYSS_Inbox_BAO_MessageTokenArray {
    return $this->tokens;
  }

  public function highlight() : string {
    $highlighted_text = '';
    // generate an index of tokens by line number
    $index = [];
    foreach($this->getTokens() as $t) {
      $index[$t->line_num][] = $t;
    }
    //

    for ($i = 0; $i < count($this->lines); $i++) {
      $line = $this->lines[$i];
      if (array_key_exists($i,$index)) {
        // find and replace tokens if necessary
        $tokens = $index[$i];
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
   * This is a simple yet opinionated algorithm that treats
   * relevancy scores and verbosity levels as an inverse-ish relationship.
   * Where it will suggest excluding tokens whos relevancy is less than
   * the distance of verbosity level to 1. For example, a verbosity level
   * of .7 has a distance of .3 from 1. So, .3 is the hushing threshold.
   * Any token with a relevancy score below .3 will be hushed.
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
      'title' => 'Click to use this aggregator email',
      'data_attr' => 'data-search',
    ],
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_AGGREGATOR => [
      'class' => 'found aggregator_email',
      'title' => 'Click to use this email',
      'data_attr' => 'data-search',
    ],
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME => [
      'class' => 'found name',
      'title' => 'Click to use this name',
      'data-attr' => 'data-json',
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
    CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ => [
      'class' => 'found zip',
      'title' => 'Click to use this city/state/zip',
      'data-attr' => 'data-json',
      'data_field_map' => [
      ]
    ],
  ];

  $attrs = [
    'class' => $map[$token->getType()]['class'],
    'title' => $map[$token->getType()]['title'],
    $map[$token->getType()]['data-attr'] => $token->getDataAsString($map[$token->getType()]['data_field_map'])
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
 * @return array
 */
protected function findEmail(string $text): array {
  $results = [];
  $mail_re = '[\w\.\-\+\%]+@[\w-]+(?:\.[[:alpha:]]{2,})+';

  if (preg_match("/^$mail_re$/",$text, $matches, PREG_OFFSET_CAPTURE)) {
    $mt = (! $this->inAggregatorList($matches[0][0])) ?
      CRM_NYSS_Inbox_BAO_MessageToken_EmailToken::create($matches[0][0],
        $matches[0][1]) :
      CRM_NYSS_Inbox_BAO_MessageToken_AggregatorToken::create($matches[0][0],
        $matches[0][1]);

    $mt->relevancy_score = .85;
    $results[] = $mt;
  } elseif (preg_match("/^Email(?:\sAddress)?\W*($mail_re)$/i",$text, $matches, PREG_OFFSET_CAPTURE)) {
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_EmailToken::create($matches[1][0],
      $matches[1][1]);

    $mt->relevancy_score = 1;
    $results[] = $mt;
  } else {
    $count = preg_match_all("/$mail_re/", $text, $matches);
    $score = match ($count) {
      0 => 0,
      1 => .7,
      2 => .4,
      default => .2
    };
    foreach(array_unique($matches[0]) as $match) {
      $mt = (! $this->inAggregatorList($matches[0][0])) ?
        CRM_NYSS_Inbox_BAO_MessageToken_EmailToken::create($match,
          strpos($text,$match)) :
        CRM_NYSS_Inbox_BAO_MessageToken_AggregatorToken::create($match,
          strpos($text,$match));

      $mt->relevancy_score = $score;
      $results[] = $mt;
    }
  }
  return $results;
}

protected function findPhone(string $text): array {
  $results = [];
  $phone_re = '(?:[+]1\h*)?\(?\d{3}\)?[\-\.\ ]?\h*\d{3}[\-\.\ ]?\d{4}';

  if (preg_match("/^$phone_re$/",$text, $matches, PREG_OFFSET_CAPTURE)) {
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_PhoneToken::create($matches[0][0],
      $matches[0][1]);
    $mt->relevancy_score = .85;
    $results[] = $mt;
  } elseif (preg_match("/^Phone(?:\sNumber)?\W*($phone_re)$/i",$text, $matches, PREG_OFFSET_CAPTURE)) {
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_PhoneToken::create($matches[1][0],
      $matches[1][1]);
    $mt->relevancy_score = 1;
    $results[] = $mt;
  } else {
    $count = preg_match_all("/$phone_re/", $text, $matches);
    $score = match ($count) {
      0 => 0,
      1 => .6,
      2 => .4,
      default => .2
    };
    foreach(array_unique($matches[0]) as $match) {
      $mt = CRM_NYSS_Inbox_BAO_MessageToken_EmailToken::create($match,
        strpos($text,$match));
      $mt->relevancy_score = $score;
      $results[] = $mt;
    }
  }
  return $results;
}

protected function findStreetAddress(string $text): array {
  $results = [];
  $addr_re = '(?<num>\d{1,4}[[:alnum:]]{1,3})\h+(?<street>[[:alnum:]-\.\h]{2,32})\h+(?i<suffix>St|Av|Ro|Rd|Dr|Bou|Blv|La|Ln|Co|Ct|Pl|Ter|Way|Cir|Pa|Pk|Hi|Hwy|Cre|Al|Tr|Pl|Sq|Ci|Cr[[:alpha:]]{0,10}}\.?)';

  if (preg_match("/^$addr_re$/", $text, $matches, PREG_OFFSET_CAPTURE)) {
    // matches first address line by itself, eg. 22 Main St., 3300F Queens Boulevard
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_Factory::createFromPregMatch(
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ,
      $matches);

    $mt->relevancy_score = .85;
    $results[] = $mt;
  }
  elseif (preg_match("/^(?i:(?i:Street\h+)Address)\W*$addr_re$/i",$text, $matches, PREG_OFFSET_CAPTURE)) {
    // Street Address: 12 Maiden Lane
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[0][0],
      $matches[0][1]);
    $mt->relevancy_score = 1;
    $results[] = $mt;
  }
  return $results;
}

protected function findCityStateZip(string $text): array {
  $results = [];
  $city = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY;
  $st = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE;
  $zip = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP;
  //$city_re = '(?<'.$city.'>[A-Z][[:alpha:]. ]+[[:alpha:]]+)';
  $city_re = '(?<'.$city.'>[[:alpha:]. ]+[[:alpha:]]+)';
  $ny_re = '(?<'.$st.'>[Nn](ew\h)?[Yy](ork)?)';
  $st_re = '(?<'.$st.'>((?i:New|North|South|Rhode|West|N|S|R|W)?\h+)?(?i:[ACDFGHIJKLMNOPTUVWY]([[:alpha:]]*)))';
  $zip_re = '(?<'.$zip.'>\d{5}(?:-\d{4})?)';
  $flags = PREG_OFFSET_CAPTURE;

  if (preg_match("/^$city_re\h*,?\h+$ny_re\h+$zip_re/",$text, $matches, $flags)) {
    // matches within NY state, eg. Albany New York 11111-2222 or Albany, NY 22222 or some variation
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_Factory::createFromPregMatch(
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ,
      $matches);

    $mt->relevancy_score = .85;
    $results[] = $mt;
  } elseif (preg_match("/^$city_re\h*,?\h+$st_re\h+$zip_re/",$text, $matches, $flags)) {
    // tries to match any state
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_Factory::createFromPregMatch(
      CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CSZ,
      $matches);

    $mt->relevancy_score = .75;
    $results[] = $mt;
  } elseif (preg_match("/^(?i:City|Locality|Town)\W*$city_re$/i",$text, $matches, $flags)) {
    // City: Albany, Locality: Albany
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[$city][0],
      $matches[$city][1]);
    $mt->relevancy_score = 1;
    $mt->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_CITY);
    $results[] = $mt;
  } elseif (preg_match("/^(?i:State(?:\W+Province)?)\W*$st_re$/i",$text, $matches, $flags)) {
    // State/Province: New York
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[$st][0],
      $matches[$st][1]);
    $mt->relevancy_score = 1;
    $mt->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_STATE);
    $results[] = $mt;
  } elseif (preg_match("/^(?i:(?:ZIP|Postal)(?:\h?Code)?)\W*$zip_re$/i",$text, $matches, $flags)) {
    // Zip / Postal Code: 11111
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[$zip][0],
      $matches[$zip][1]);
    $mt->relevancy_score = 1;
    $mt->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_ZIP);
    $results[] = $mt;
  }
  return $results;
}

protected function findClosing(string $text): array {
  $results = [];
  $close_re = '[\w\h]+(?i:Sincerely|Regards|Best|Yours|Respectfully|Consideration|Gratitude)\h+,?';

  if (preg_match("/^$close_re$/",$text, $matches, PREG_OFFSET_CAPTURE)) {
    $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[0][0],
      $matches[0][1]);
    $mt->relevancy_score = .85;
    $mt->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_COMP_CLOSE);
    $results[] = $mt;
  }
  return $results;
}

  protected function findProperName(string $text): array {
    $fn = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME;
    $ln = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_LNAME;
    $mn = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_MNAME;
    $hn = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_HONOR;
    $suf = CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_SUFFIX;
    $results = [];
    $hon_re = '(?:Mr|Mrs|Ms|Mx|Miss|Dr|Prof|Rev|Hon)\.?';
    $f_re = '[A-Z][a-z]+';
    $l_re = "[A-Z][A-Za-z\-']+(?: [A-Z][a-z]+)?";
    $m_re = '[A-Z][a-z]*\.?';
    $suf_re = '(?:Sr|Jr|II|III|IV|V|Ph\.D|M\.D|Esq)\.?';
    $name_re = "(?:(?<$hn>$hon_re)\h*)?(?<$fn>$f_re)(?:\h*(?<$mn>$m_re))?\h+(?<$ln>$l_re)(?:\h*(?<$suf>$suf_re))?";
    $flags = PREG_OFFSET_CAPTURE;

    if (preg_match("/^$name_re$/",$text, $matches, $flags)) {
      // should match a pattern such as Dr. Candace J. Smith or Jalon S. Singleton Jr.
      // honorific, Middle name/initial, suffix are all optional, but if found
      // should increase score.
      $score = .65;

      $mt = CRM_NYSS_Inbox_BAO_MessageToken_Factory::createFromPregMatch(
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME,
        $matches);

      // If we match an honorific, suffix or middle name, then it looks more like
      // a name and we can raise the certainty score.
      if (!empty($matches[$hn][0]) ||
          isset($matches[$suf][0]) ||
          isset($matches[$mn][0]) ) {
        $score = .85;
      }

      $mt->relevancy_score = $score;
      $results[] = $mt;

    } elseif (preg_match("/^(?i:Name)\W*$name_re$/i",$text, $matches, $flags)) {
      // Name: Christopher M. Carmichael Jr.
      $score = 1;

      $mt = CRM_NYSS_Inbox_BAO_MessageToken_Factory::createFromPregMatch(
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME,
        $matches);

      $mt->relevancy_score = $score;
      $results[] = $mt;
    } elseif (preg_match("/^((To|Reply To|From):\h*)\"?\K$name_re(?=\"?\h<?)/i",$text, $matches, $flags)) {
      // To: Christopher M. Carmichael Jr. <cmc@gmail.com>
      $score = .85;

      $mt = CRM_NYSS_Inbox_BAO_MessageToken_Factory::createFromPregMatch(
        CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FULLNAME,
        $matches);

      $mt->relevancy_score = $score;
      $results[] = $mt;

    } elseif (preg_match("/^(?i:First Name)\W*(?<$fn>[[:alpha:]- ]+)$/i",$text, $matches, $flags)) {
      // First Name: Samuel
      $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[$fn][0],
                                                             $matches[$fn][1]);
      $mt->relevancy_score = 1;
      $mt->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_FNAME);
      $results[] = $mt;
    } elseif (preg_match("/^(?i:Last Name)\W*(?<$ln>[[:alpha:]-' ]+)$/i",$text, $matches, $flags)) {
      // Last Name: Smith
      $mt = CRM_NYSS_Inbox_BAO_MessageToken_GenericToken::create($matches[$ln][0],
                                                             $matches[$ln][1]);
      $mt->relevancy_score = 1;
      $mt->setType(CRM_NYSS_Inbox_BAO_MessageToken_Factory::TYPE_LNAME);
      $results[] = $mt;
    }

    // deprioritize things that seem obviously wrong.
    if (sizeof($results)) {
      array_walk($results, function($r) {
        if (preg_match('/New York|Senate|Senator|Legislative/i', $r->getToken())) {
          $r->relevancy_score = .1;
        }
      });
    }

    return $results;
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
