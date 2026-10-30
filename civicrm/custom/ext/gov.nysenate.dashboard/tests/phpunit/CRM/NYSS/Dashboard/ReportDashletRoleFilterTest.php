<?php
declare(strict_types = 1);

use Civi\Test\EndToEndInterface;

/**
 * NYSS 3439 - legacy report dashlets must only be offered to users in one of
 * the report's ACL Group/Roles.
 *
 * Core (dev/core#6290, civicrm-core PR 34630) copies the roles into
 * civicrm_dashboard.permission as "has user role X" when a report is saved,
 * but it joins them to the report's own permission with OR whenever more than
 * one role is selected, and it never updates dashlets saved before the
 * upgrade. dashboard_civicrm_selectWhereClause() closes both gaps by checking
 * the report's grouprole directly. These tests go through
 * CRM_Core_BAO_Dashboard::getContactDashlets(), so they will also catch a core
 * refactor that stops routing that call through permissioned Api4 (which
 * would silently bypass the hook).
 *
 * Permissions and roles are faked via CRM_NYSS_Dashboard_TestPermission;
 * the report instances, dashlets and contact are real rows, removed in
 * tearDown().
 *
 * Run from the extension directory:
 *   INSTANCE=<instance> HTTP_HOST=<instance> phpunit
 *
 * @group e2e
 */
class CRM_NYSS_Dashboard_ReportDashletRoleFilterTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface {

  private const ROLE_A = 'NYSS 3439 Test Role A';

  private const ROLE_B = 'NYSS 3439 Test Role B';

  private CRM_NYSS_Dashboard_TestPermission $permission;

  private $original_permission_class;

  private $original_user_id;

  private int $contact_id;

  private array $report_ids = [];

  private array $dashlet_ids = [];

  public static function setUpBeforeClass(): void {
    \Civi\Test::e2e()->installMe(__DIR__)->apply();
  }

  public function setUp(): void {
    parent::setUp();

    $config = CRM_Core_Config::singleton();
    $this->original_permission_class = $config->userPermissionClass;
    $this->permission = new CRM_NYSS_Dashboard_TestPermission();
    $this->permission->granted_permissions = ['access CiviCRM', 'access CiviReport'];
    $config->userPermissionClass = $this->permission;

    $this->contact_id = (int) \Civi\Api4\Contact::create(FALSE)
      ->addValue('contact_type', 'Individual')
      ->addValue('first_name', 'NYSS3439')
      ->addValue('last_name', 'DashletTest')
      ->execute()
      ->first()['id'];

    $session = CRM_Core_Session::singleton();
    $this->original_user_id = $session->get('userID');
    $session->set('userID', $this->contact_id);
  }

  public function tearDown(): void {
    if ($this->dashlet_ids) {
      \Civi\Api4\Dashboard::delete(FALSE)
        ->addWhere('id', 'IN', $this->dashlet_ids)
        ->execute();
    }
    foreach ($this->report_ids as $report_id) {
      CRM_Report_BAO_ReportInstance::deleteRecord(['id' => $report_id]);
    }
    \Civi\Api4\Contact::delete(FALSE)
      ->setUseTrash(FALSE)
      ->addWhere('id', '=', $this->contact_id)
      ->execute();

    CRM_Core_Session::singleton()->set('userID', $this->original_user_id);
    CRM_Core_Config::singleton()->userPermissionClass = $this->original_permission_class;

    parent::tearDown();
  }

  /**
   * The case core gets wrong: a report permission plus 2+ roles is stored
   * with permission_operator OR, so 'access CiviReport' alone passes core's
   * dashlet check.
   */
  public function testMultiRoleDashletHiddenFromUserWithoutRole(): void {
    $dashlet_id = $this->createReportDashlet([self::ROLE_A, self::ROLE_B]);

    $operator = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Dashboard', $dashlet_id, 'permission_operator');
    $this->assertSame('OR', $operator,
      'Core now stores multi-role report dashlets with a different operator; re-check whether the NYSS 3439 hook is still needed.');

    $this->assertNotContains($dashlet_id, $this->getVisibleDashletIds());
  }

  public function testMultiRoleDashletVisibleToUserWithOneRole(): void {
    $dashlet_id = $this->createReportDashlet([self::ROLE_A, self::ROLE_B]);
    $this->permission->granted_roles = [self::ROLE_B];

    $this->assertContains($dashlet_id, $this->getVisibleDashletIds());
  }

  public function testSingleRoleDashletHiddenFromUserWithoutRole(): void {
    $dashlet_id = $this->createReportDashlet([self::ROLE_A]);

    $this->assertNotContains($dashlet_id, $this->getVisibleDashletIds());
  }

  /**
   * Dashlets saved before the dev/core#6290 upgrade have no "has user role"
   * entries in civicrm_dashboard.permission at all.
   */
  public function testPreUpgradeDashletHiddenFromUserWithoutRole(): void {
    $dashlet_id = $this->createReportDashlet([self::ROLE_A]);
    CRM_Core_DAO::executeQuery(
      "UPDATE civicrm_dashboard SET permission = 'access CiviReport', permission_operator = NULL WHERE id = %1",
      [1 => [$dashlet_id, 'Integer']]
    );

    $this->assertNotContains($dashlet_id, $this->getVisibleDashletIds());
  }

  public function testUnrestrictedDashletVisible(): void {
    $dashlet_id = $this->createReportDashlet([]);

    $this->assertContains($dashlet_id, $this->getVisibleDashletIds());
  }

  /**
   * CRM_Report_Utils_Report::isInstanceGroupRoleAllowed() lets report
   * administrators through regardless of role; the hook keeps that.
   */
  public function testAdministerReportsSeesMultiRoleDashlet(): void {
    $dashlet_id = $this->createReportDashlet([self::ROLE_A, self::ROLE_B]);
    $this->permission->granted_permissions[] = 'administer Reports';

    $this->assertContains($dashlet_id, $this->getVisibleDashletIds());
  }

  /**
   * System-wide lookups (e.g. Angular module wiring) run Dashboard.get
   * without permission checks and must still see every dashlet.
   */
  public function testUnpermissionedDashboardGetNotFiltered(): void {
    $dashlet_id = $this->createReportDashlet([self::ROLE_A, self::ROLE_B]);

    $count = \Civi\Api4\Dashboard::get(FALSE)
      ->addWhere('id', '=', $dashlet_id)
      ->execute()
      ->count();
    $this->assertSame(1, $count);
  }

  /**
   * Creates a report instance the same way the report Access tab does, with
   * "Available for Dashboard?" checked, and returns its dashlet ID.
   */
  private function createReportDashlet(array $roles): int {
    $instance = CRM_Report_BAO_ReportInstance::create([
      'title' => 'NYSS 3439 Dashlet Test ' . uniqid(),
      'report_id' => 'contact/detail',
      'domain_id' => CRM_Core_Config::domainID(),
      'permission' => 'access CiviReport',
      'grouprole' => $roles,
      'addToDashboard' => 1,
      'form_values' => serialize([]),
    ]);
    $this->report_ids[] = (int) $instance->id;

    $dashlet_id = (int) CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Dashboard', "report/{$instance->id}", 'id', 'name');
    $this->assertGreaterThan(0, $dashlet_id, 'Report instance did not create a dashlet.');
    $this->dashlet_ids[] = $dashlet_id;
    return $dashlet_id;
  }

  private function getVisibleDashletIds(): array {
    return array_map('intval', array_column(CRM_Core_BAO_Dashboard::getContactDashlets(), 'id'));
  }

}
