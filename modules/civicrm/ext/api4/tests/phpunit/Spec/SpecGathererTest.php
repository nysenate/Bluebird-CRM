<?php

namespace Civi\Test\Api4\Spec;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\Provider\SpecProviderInterface;
use Civi\Api4\Service\Spec\RequestSpec;
use Civi\Api4\Service\Spec\SpecGatherer;
use Civi\Test\Api4\UnitTestCase;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Test\Api4\Traits\TableDropperTrait;
use Prophecy\Argument;

/**
 * @group headless
 */
class SpecGathererTest extends UnitTestCase {

  use TableDropperTrait;

  public function setUpHeadless() {
    $this->dropByPrefix('civicrm_value_favorite');
    $this->cleanup([
      'tablesToTruncate' => [
        'civicrm_custom_group',
        'civicrm_custom_field'
      ],
    ]);
    return parent::setUpHeadless();
  }

  public function testBasicFieldsGathering() {
    $gatherer = new SpecGatherer();
    $specs = $gatherer->getSpec('Contact', 'get', FALSE);
    $contactDAO = _civicrm_api3_get_DAO('Contact');
    $contactFields = $contactDAO::fields();
    $specFieldNames = $specs->getFieldNames();
    $contactFieldNames = array_column($contactFields, 'name');

    $this->assertEmpty(array_diff_key($contactFieldNames, $specFieldNames));
  }

  public function testWithSpecProvider() {
    $gather = new SpecGatherer();

    $provider = $this->prophesize(SpecProviderInterface::class);
    $provider->applies('Contact', 'create')->willReturn(TRUE);
    $provider->modifySpec(Argument::any())->will(function ($args) {
      /** @var RequestSpec $spec */
      $spec = $args[0];
      $spec->addFieldSpec(new FieldSpec('foo', 'Contact'));
    });
    $gather->addSpecProvider($provider->reveal());

    $spec = $gather->getSpec('Contact', 'create', FALSE);
    $fieldNames = $spec->getFieldNames();

    $this->assertContains('foo', $fieldNames);
  }

  public function testPseudoConstantOptionsWillBeAdded() {
    $customGroupId = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'FavoriteThings')
      ->addValue('extends', 'Contact')
      ->execute()
      ->first()['id'];

    $options = ['r' => 'Red', 'g' => 'Green', 'p' => 'Pink'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'FavColor')
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('options', $options)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    $gatherer = new SpecGatherer();
    $spec = $gatherer->getSpec('Contact', 'get', TRUE);

    $regularField = $spec->getFieldByName('contact_type');
    $this->assertNotEmpty($regularField->getOptions());
    $this->assertContains('Individual', $regularField->getOptions());

    $customField = $spec->getFieldByName('FavoriteThings.FavColor');
    $this->assertNotEmpty($customField->getOptions());
    $this->assertContains('Green', $customField->getOptions());
    $this->assertEquals('Pink', $customField->getOptions()['p']);
  }

}
