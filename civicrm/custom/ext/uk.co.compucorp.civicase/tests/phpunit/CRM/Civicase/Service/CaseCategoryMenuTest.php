<?php

use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;
use CRM_Civicase_Service_CaseCategoryMenu as CaseCategoryMenuService;
use CRM_Civicase_Test_Fabricator_CaseCategory as CaseCategoryFabricator;
use CRM_Civicase_Test_Fabricator_CaseCategoryInstance as CaseCategoryInstanceFabricator;
use CRM_Civicase_Test_Fabricator_CaseCategoryInstanceType as CaseCategoryInstanceTypeFabricator;

/**
 * Test class for the CRM_Civicase_Service_CaseCategoryMenu.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseCategoryMenuTest extends BaseHeadlessTest {

  /**
   * Instance of CaseCategoryMenu service.
   *
   * @var CRM_Civicase_Service_CaseCategoryMenu
   */
  private $caseCategoryMenu;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->caseCategoryMenu = new CaseCategoryMenuService();
  }

  /**
   * Test createItems method adds the expected menus.
   */
  public function testCreateNewItemsAddsExpectedMenus() {
    $caseCategory = CaseCategoryFabricator::fabricate();

    $this->caseCategoryMenu->createItems($caseCategory['name']);

    $this->assertMenuCreatedForCaseCategory($caseCategory);
  }

  /**
   * Test calling twice createItems does not create duplicates.
   */
  public function testCreateTwiceSameItemsDoesNotCreateDuplicates() {
    $caseCategory = CaseCategoryFabricator::fabricate();

    // First call.
    $this->caseCategoryMenu->createItems($caseCategory['name']);
    // Second call.
    $this->caseCategoryMenu->createItems($caseCategory['name']);

    $menuCreatedCount = civicrm_api3('Navigation', 'getcount', ['name' => $caseCategory['name']]);
    $this->assertEquals(1, $menuCreatedCount);
  }

  /**
   * Test createItems method assigns same weight to different menus.
   */
  public function testCreateTwoDifferentMenusAssignsSameWeight() {
    $caseCategoryOne = CaseCategoryFabricator::fabricate();
    $caseCategoryTwo = CaseCategoryFabricator::fabricate();
    $expectWeightForMenu = $this->getExpectedWeightForCategoryMenu();

    $this->caseCategoryMenu->createItems($caseCategoryOne['name']);
    // This clears the cache.
    civicrm_api3('Navigation', 'getfields', ['cache_clear' => 1]);
    $this->caseCategoryMenu->createItems($caseCategoryTwo['name']);

    $menuOneWeight = civicrm_api3('Navigation', 'getsingle',
      [
        'name' => $caseCategoryOne['name'],
        'return' => ['weight'],
      ]
    )['weight'];
    $menuTwoWeight = civicrm_api3('Navigation', 'getsingle',
      [
        'name' => $caseCategoryTwo['name'],
        'return' => ['weight'],
      ]
    )['weight'];
    $this->assertEquals($expectWeightForMenu, $menuOneWeight);
    $this->assertEquals($expectWeightForMenu, $menuTwoWeight);
  }

  /**
   * Test deleteItems method removes menus and submenus.
   */
  public function testDeleteItemsRemovesMenusAndSubMenus() {
    $caseCategory = CaseCategoryFabricator::fabricate();

    $this->caseCategoryMenu->createItems($caseCategory['name']);
    $menuCreatedId = civicrm_api3('Navigation', 'getsingle',
      [
        'name' => $caseCategory['name'],
        'return' => ['id'],
      ]
    )['id'];

    $this->caseCategoryMenu->deleteItems($caseCategory['name']);

    $menuCreatedCount = civicrm_api3('Navigation', 'getcount', ['id' => $menuCreatedId]);
    $subMenusCreatedCount = civicrm_api3('Navigation', 'getcount', ['parent_id' => $menuCreatedId]);
    $this->assertEquals(0, $menuCreatedCount);
    $this->assertEquals(0, $subMenusCreatedCount);
  }

  /**
   * Test updateItems method produces expected changes.
   */
  public function testUpdateItemsProducesExpectedChanges() {
    $caseCategory = CaseCategoryFabricator::fabricate();
    $newValues = [
      'icon' => 'new icon',
      'is_active' => 0,
    ];

    $this->caseCategoryMenu->createItems($caseCategory['name']);
    $menuCreated = civicrm_api3('Navigation', 'getsingle', ['name' => $caseCategory['name']]);
    foreach ($newValues as $key => $value) {
      $this->assertNotEquals($value, $menuCreated[$key]);
    }

    $this->caseCategoryMenu->updateItems($caseCategory['id'], $newValues);
    $menuCreated = civicrm_api3('Navigation', 'getsingle', ['name' => $caseCategory['name']]);
    foreach ($newValues as $key => $value) {
      $this->assertEquals($value, $menuCreated[$key]);
    }
  }

  /**
   * Test createManageWorkflowMenu method creates expected submenu.
   *
   * @param bool $showCategoryNameOnMenuLabel
   *   Value for the second parameter of tested method.
   *
   * @dataProvider getDataForCreateManageWorkflowMenu
   */
  public function testCreateManageWorkflowMenuCreatesExpectedSubitem(bool $showCategoryNameOnMenuLabel) {
    $caseCategory = CaseCategoryFabricator::fabricate();
    $caseCategoryInstanceType = CaseCategoryInstanceTypeFabricator::fabricate();
    CaseCategoryInstanceFabricator::fabricate([
      'category_id' => $caseCategory['value'],
      'instance_id' => $caseCategoryInstanceType['value'],
    ]);
    $menuCreated = $this->createMainMenuWithFirstSubItem($caseCategory['name']);

    $this->caseCategoryMenu->createManageWorkflowMenu($caseCategoryInstanceType['name'], $showCategoryNameOnMenuLabel);

    $subMenuCreated = civicrm_api3('Navigation', 'getsingle', [
      'name' => 'manage_' . $caseCategory['name'] . '_workflows',
    ]);
    $menuLabel = $showCategoryNameOnMenuLabel
      ? 'Manage ' . $caseCategory['name']
      : 'Manage Workflows';
    $this->assertEquals($menuLabel, $subMenuCreated['label']);
    $parentMenu = civicrm_api3('Navigation', 'getsingle', ['id' => $subMenuCreated['parent_id']]);
    $this->assertEquals($menuCreated['id'], $parentMenu['id']);
  }

  /**
   * Creates the expected menus for a given category name.
   *
   * It creates the main menu, and a first submenu assigned to it. This is
   * required for correctly testing createManageWorkflowMenu method.
   *
   * @param string $caseCategoryName
   *   Case Type Category name.
   */
  public function createMainMenuWithFirstSubItem(string $caseCategoryName) {
    $menuCreated = civicrm_api3('Navigation', 'create', ['label' => $caseCategoryName]);
    civicrm_api3('Navigation', 'create',
      [
        'parent_id' => $menuCreated['id'],
        'label' => 'First subitem',
      ]
    );

    return $menuCreated;
  }

  /**
   * Assert the menu created for the given category has expected information.
   */
  private function assertMenuCreatedForCaseCategory(array $caseCategory) {
    $menuCreated = civicrm_api3('Navigation', 'getsingle', ['name' => $caseCategory['name']]);

    $this->assertEquals(ts($caseCategory['name']), $menuCreated['name']);
    $this->assertEquals($caseCategory['label'], $menuCreated['label']);
    $this->assertEquals(1, $menuCreated['is_active']);
    $this->assertEquals($this->getPermissionForNavigationMenu($caseCategory['name']), $menuCreated['permission']);
    $this->assertEquals('OR', $menuCreated['permission_operator']);
    $this->assertEquals($this->getExpectedWeightForCategoryMenu(), $menuCreated['weight']);

    $subMenusCreatedCount = civicrm_api3('Navigation', 'getcount', ['parent_id' => $menuCreated['id']]);
    $this->assertEquals(6, $subMenusCreatedCount);
  }

  /**
   * Returns permissions that the menu should have.
   */
  private function getPermissionForNavigationMenu(string $caseTypeCategoryName) {
    $permissions = (new CaseCategoryPermission())->get($caseTypeCategoryName);

    return sprintf(
      "%s,%s",
      $permissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name'],
      $permissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name']
    );
  }

  /**
   * Get the expected weight for the category menu.
   */
  private static function getExpectedWeightForCategoryMenu() {
    return CRM_Core_DAO::getFieldValue(
        'CRM_Core_DAO_Navigation',
        'Cases',
        'weight',
        'name'
      ) + 1;
  }

  /**
   * Data provider for returning options for createManageWorkflowMenu method.
   *
   * Returns the two possible options for the boolean flag.
   */
  public function getDataForCreateManageWorkflowMenu() {
    return [
      [TRUE],
      [FALSE],
    ];
  }

}
