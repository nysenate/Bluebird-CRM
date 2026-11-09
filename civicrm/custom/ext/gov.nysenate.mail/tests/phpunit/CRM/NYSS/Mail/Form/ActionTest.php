<?php
declare(strict_types = 1);

use Civi\Test\EndToEndInterface;

/**
 * NYSS #18870, #19344 - per-action permissions for the mailing action form
 * (cancel, pause, resume, archive, delete).
 *
 * Covers the 'cancel mailings' permission declared by mail_civicrm_permission()
 * (including that 'access CiviMail' implies it), the permission each action
 * needs in CRM_NYSS_Mail_Form_Action, and the civicrm/mailing/action route
 * override in mail_civicrm_alterMenu().
 *
 * Permissions are faked via CRM_NYSS_Mail_TestPermission. Workflow mode must
 * be on for the instance, as it is in Bluebird; otherwise core always denies
 * create/schedule/approve mailings.
 *
 * Run from the extension directory:
 *   INSTANCE=<instance> HTTP_HOST=<instance> phpunit
 *
 * @group e2e
 */
class CRM_NYSS_Mail_Form_ActionTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface {

  private const ACTIONS = [
    'cancel' => CRM_Core_Action::DISABLE,
    'pause' => CRM_Core_Action::CLOSE,
    'resume' => CRM_Core_Action::REOPEN,
    'archive' => CRM_Core_Action::RENEW,
    'delete' => CRM_Core_Action::DELETE,
  ];

  private CRM_NYSS_Mail_TestPermission $permission;

  private $original_permission_class;

  public static function setUpBeforeClass(): void {
    \Civi\Test::e2e()->installMe(__DIR__)->apply();
  }

  public function setUp(): void {
    parent::setUp();
    $this->assertTrue(CRM_Mailing_Info::workflowEnabled(),
      'CiviMail workflow mode is off on this instance; these tests assume Bluebird\'s configuration.');

    $config = CRM_Core_Config::singleton();
    $this->original_permission_class = $config->userPermissionClass;
    $this->permission = new CRM_NYSS_Mail_TestPermission();
    $config->userPermissionClass = $this->permission;
  }

  public function tearDown(): void {
    CRM_Core_Config::singleton()->userPermissionClass = $this->original_permission_class;
    parent::tearDown();
  }

  public function testCancelPermissionIsDeclared(): void {
    $permissions = CRM_Core_Permission::basicPermissions(TRUE, TRUE);
    $this->assertArrayHasKey('cancel mailings', $permissions);
    $this->assertContains('access CiviMail', $permissions['cancel mailings']['implied_by'] ?? []);
  }

  /**
   * Roles as granted by scripts/resetRolePerms.sh, each on top of a base role
   * that grants 'access CiviCRM'.
   */
  public static function rolePermissionProvider(): array {
    return [
      'Administrator' => [
        ['access CiviCRM', 'access CiviMail', 'create mailings', 'schedule mailings', 'approve mailings', 'delete in CiviMail'],
        ['cancel', 'pause', 'resume', 'archive', 'delete'],
      ],
      // 'access CiviMail' alone must still cancel, via implied_by
      'access CiviMail only' => [
        ['access CiviCRM', 'access CiviMail'],
        ['cancel', 'pause', 'resume', 'archive'],
      ],
      'Mailing Creator' => [
        ['access CiviCRM', 'create mailings', 'delete in CiviMail'],
        ['archive', 'delete'],
      ],
      'Mailing Scheduler' => [
        ['access CiviCRM', 'schedule mailings', 'delete in CiviMail'],
        ['delete'],
      ],
      'Mailing Approver' => [
        ['access CiviCRM', 'approve mailings', 'cancel mailings', 'delete in CiviMail'],
        ['cancel', 'delete'],
      ],
      'Creator + Scheduler + Approver' => [
        ['access CiviCRM', 'create mailings', 'schedule mailings', 'approve mailings', 'cancel mailings', 'delete in CiviMail'],
        ['cancel', 'archive', 'delete'],
      ],
      'Staff' => [
        ['access CiviCRM'],
        [],
      ],
    ];
  }

  /**
   * @dataProvider rolePermissionProvider
   */
  public function testActionPermissions(array $granted, array $expected_allowed): void {
    $this->permission->granted_permissions = $granted;

    $actual_allowed = [];
    foreach (self::ACTIONS as $name => $action) {
      if (CRM_NYSS_Mail_Form_Action::isActionPermitted($action)) {
        $actual_allowed[] = $name;
      }
    }
    $this->assertSame($expected_allowed, $actual_allowed);
  }

  public function testUnknownActionIsDenied(): void {
    $this->permission->granted_permissions = ['access CiviCRM', 'access CiviMail', 'delete in CiviMail'];
    $this->assertFalse(CRM_NYSS_Mail_Form_Action::isActionPermitted(CRM_Core_Action::VIEW));
  }

  public function testRouteUsesNyssFormAndAllowsWorkflowRoles(): void {
    $items = CRM_Core_Menu::xmlItems(TRUE);
    $item = $items['civicrm/mailing/action'] ?? NULL;
    $this->assertNotNull($item, 'Core no longer defines civicrm/mailing/action; re-check the NYSS #18870, #19344 override.');
    $this->assertSame('CRM_NYSS_Mail_Form_Action', $item['page_callback']);
    $this->assertSame('or', $item['access_arguments'][1]);
    foreach (['access CiviMail', 'create mailings', 'schedule mailings', 'approve mailings'] as $permission) {
      $this->assertContains($permission, $item['access_arguments'][0]);
    }
  }

  /**
   * The subclass re-implements core's preProcess(). If core's form gains
   * methods or changes its parent, this is a reminder to re-check it.
   */
  public function testCoreFormStillMatchesOverride(): void {
    $this->assertSame(CRM_Core_Form::class, get_parent_class(CRM_Mailing_Form_Action::class));
    $this->assertTrue(CRM_Core_Smarty::singleton()->templateExists('CRM/Mailing/Form/Action.tpl'));
  }

}
