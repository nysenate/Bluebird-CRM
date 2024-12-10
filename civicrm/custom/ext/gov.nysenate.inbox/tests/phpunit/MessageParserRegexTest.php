<?php

use PHPUnit\Framework\TestCase;

class MessageParserRegexTest extends TestCase {
  public function testPhoneMatch() {
    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegex();
    $parser->parse("+1 (555) 534-1234\n");
    $parser->setMatchFormFill(true);
    $parser->parse("\nPhone: (555) 555-1234\n");
    $parser->parse("\nPhone Number: (555) 555-1234\n");
    $parser->parse("\nPhone: 555.444.6666\n");
    $parser->setMatchFormFill(false);
    $parser->parse("Please call me at 333-222-1236.\n");
    $tokens = $parser->getTokens();
    $this->assertCount(5,$tokens);
    foreach($tokens as $t) {
      $this->assertInstanceOf('CRM_NYSS_Inbox_BAO_MessageToken_PhoneToken',$t);
    }
  }

  public function testEmailMatch() {
    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegex();
    $parser->parse("myemail@adomain.com\n");
    $parser->setMatchFormFill(true);
    $parser->parse("\nE-mail: myemail@adomain.com\n");
    $parser->parse("\nEmail: myemail@adomain.com\n");
    $parser->parse("\nEmail Address: myemail@adomain.com\n");
    $parser->setMatchFormFill(false);
    $parser->parse("My email address is myemail@adomain.com.\n");
    $tokens = $parser->getTokens();
    $this->assertCount(5,$tokens);
    foreach($tokens as $t) {
      $this->assertInstanceOf('CRM_NYSS_Inbox_BAO_MessageToken_EmailToken',$t);
    }
  }

  public function testStreetAddressMatch() {
    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegex();
    $parser->parse("125 Sherman Road\n");
    $parser->setMatchFormFill(true);
    $parser->parse("\nStreet Address: 8745 Orchard Lane\n");
    $parser->parse("\nAddress: 315-F Westfield Blvd\n");
    $parser->setMatchFormFill(false);
    $parser->parse("1608 Jefferson Court, White Plains, NY 10606\n");
    $parser->parse("7529 Pinecrest Terrace\n");
    $tokens = $parser->getTokens();
    $this->assertCount(6,$tokens);
  }

  public function testCityStateZipMatch() {
    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegex();
    $parser->parse("St. Regis Falls, New York 12980\n");
    $parser->setMatchFormFill(true);
    $parser->parse("\nCity: Great Neck\n");
    $parser->parse("\nZip: 12946\n");
    $parser->setMatchFormFill(false);
    $parser->parse("Saint James, NY 11780\n");
    $parser->parse("Saratoga Springs New York 12866\n");
    $tokens = $parser->getTokens();
    $this->assertCount(5,$tokens);
  }

  public function testNameMatch() {
    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegex();
    $parser->parse("Dr. Sarah Louise Turner-Jones\n");
    $parser->setMatchFormFill(true);
    $parser->parse("\nName: James O'Hara Sr.\n");
    $parser->parse("\nFirst Name: Emily Grace\n");
    $parser->parse("\nLast Name:  Clark-Hughes\n");
    $parser->setMatchFormFill(false);
    $parser->parse("Mr. Jonathan A. Smith III\n");
    $parser->parse("Rachel Bennet\n");
    $tokens = $parser->getTokens();
    $this->assertCount(6,$tokens);
  }

  /** Personally identifiable information has been replaced. */
  public function testFullMessage1() {
    $text = <<< EOL
To: Ortt@nysenate.gov
From: do-not-reply@nysenate.gov
Date: 03/01/2021 06:39PM
Subject: [NYSenate.gov] Ezpass

Sen. Ortt has received a new direct message via NYSenate.gov.

Reply here: /user/145001/dashboard/inbox/reply/325083

Jim Madeupname 
112 Madeupstreet Dr
North Tonawanda, NY 14120
jmadeupname@roadrunner.com

Ezpass

The content of this direct message will be automatically added to Senator Ortt's Bluebird account shortly.

If you are unable to reply to this message using NYSenate.gov, please have your Chief of Staff or Senator call the STS help line at extension 2011 and request that you be added as a "Legislative Correspondent".

Please respect this user's preferences and refrain from contacting them directly through email.

EOL;

    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegEx();
    $parser->parse($text);
    $tokens = $parser->getTokens()->getArrayCopy();
    $this->assertCount(6,$tokens);
    $this->assertCount(1,array_filter($tokens, fn($i) => $i->getToken() == 'Jim Madeupname'));
    $this->assertCount(1,array_filter($tokens, fn($i) => $i->getToken() == '112 Madeupstreet Dr'));
    $this->assertCount(1,array_filter($tokens, fn($i) => $i->getToken() == 'North Tonawanda, NY 14120'));
    $this->assertCount(1,array_filter($tokens, fn($i) => $i->getToken() == 'jmadeupname@roadrunner.com'));
  }

/** Personally identifiable information has been replaced.
 * Should match name and email in From line.
 * Would be nice if it matched "I live in Lockport, NY. But, doesn't */
public function testFullMessage2() {
  $text = <<< EOL
To: ortt@nysenate.gov
From: "Ulala Utatt" ulala@utatt.com
Date: 08/24/2024 12:48PM
Subject: Vehicle Title Issue - NYS DMV Case# 8973-123456

Hi Senator Ortt, 

My name's Ulala Utatt, and my family & I live in Glenport, NY. 

I need some help with a title issue for a vehicle I purchased (not financed) on Somemonth 18th 2024.

I submitted a note online (reference # is 897348-123456) on August 12th & also repeatedly called NYS DMV with no luck (518-486-4714).
  
Thanks, Ulala
EOL;

    $parser = new CRM_NYSS_Inbox_BAO_MessageParserRegex();
    $parser->parse($text);
    $tokens = $parser->getTokens()->getArrayCopy();
    $this->assertCount(3, $tokens);
    $this->assertCount(1, array_filter($tokens, fn($i) => $i->getToken() == 'Ulala Utatt'));
    $this->assertCount(1, array_filter($tokens, fn($i) => $i->getToken() == 'ulala@utatt.com'));
  }
}



