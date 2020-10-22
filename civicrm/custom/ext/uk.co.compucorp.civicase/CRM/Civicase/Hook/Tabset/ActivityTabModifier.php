<?php

/**
 * Class CRM_Civicase_Hook_Tabset_ActivityTabModifier.
 */
class CRM_Civicase_Hook_Tabset_ActivityTabModifier {

  /**
   * Determines what happens if the hook is handled.
   *
   * @param string $tabsetName
   *   Tabset name.
   * @param array $tabs
   *   Tabs list.
   * @param array $context
   *   Context.
   * @param bool $useAng
   *   Whether to use angular.
   */
  public function run($tabsetName, array &$tabs, array $context, &$useAng) {
    if (!$this->shouldRun($tabsetName)) {
      return;
    }

    foreach ($tabs as $key => &$tab) {
      if ($tab['id'] === 'activity') {
        $activity_types = array_flip(CRM_Activity_BAO_Activity::buildOptions('activity_type_id', 'validate'));
        $useAng = TRUE;
        $tab['url'] = CRM_Utils_System::url('civicrm/case/contact-act-tab', [
          'cid' => $context['contact_id'],
        ]);
        // Exclude bulk email activity type from the Activity count because
        // there are issues with target contact for this activity type.
        // To remove this code once issue is fixed from core.
        $params = [
          'activity_type_exclude_id' => $activity_types['Bulk Email'],
          'contact_id' => $context['contact_id'],
        ];
        $tab['count'] = CRM_Activity_BAO_Activity::getActivitiesCount($params);
      }
    }
  }

  /**
   * Checks if the hook should run.
   *
   * @param string $tabsetName
   *   Tabset name.
   *
   * @return bool
   *   Return value.
   */
  private function shouldRun($tabsetName) {
    return $tabsetName === 'civicrm/contact/view';
  }

}
