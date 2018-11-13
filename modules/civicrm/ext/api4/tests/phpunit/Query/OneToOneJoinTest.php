<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Contact;
use Civi\Api4\OptionGroup;
use Civi\Api4\OptionValue;
use Civi\Test\Api4\UnitTestCase;

/**
 * Class OneToOneJoinTest
 * @package Civi\Test\Api4\Query
 * @group headless
 */
class OneToOneJoinTest extends UnitTestCase {

  public function testOneToOneJoin() {
    $languageGroupId = OptionGroup::create()
      ->addValue('name', 'languages')
      ->execute()
      ->first()['id'];

    OptionValue::create()
      ->addValue('option_group_id', $languageGroupId)
      ->addValue('name', 'hy_AM')
      ->addValue('value', 'hy')
      ->addValue('label', 'Armenian')
      ->execute();

    OptionValue::create()
      ->addValue('option_group_id', $languageGroupId)
      ->addValue('name', 'eu_ES')
      ->addValue('value', 'eu')
      ->addValue('label', 'Basque')
      ->execute();

    $armenianContact = Contact::create()
      ->addValue('first_name', 'Contact')
      ->addValue('last_name', 'One')
      ->addValue('contact_type', 'Individual')
      ->addValue('preferred_language', 'hy_AM')
      ->execute()
      ->first();

    $basqueContact = Contact::create()
      ->addValue('first_name', 'Contact')
      ->addValue('last_name', 'Two')
      ->addValue('contact_type', 'Individual')
      ->addValue('preferred_language', 'eu_ES')
      ->execute()
      ->first();

    $contacts = Contact::get()
      ->addWhere('id', 'IN', [$armenianContact['id'], $basqueContact['id']])
      ->addSelect('preferred_language.label')
      ->addSelect('last_name')
      ->execute()
      ->indexBy('last_name')
      ->getArrayCopy();

    $this->assertEquals($contacts['One']['preferred_language']['label'], 'Armenian');
    $this->assertEquals($contacts['Two']['preferred_language']['label'], 'Basque');
  }

}
