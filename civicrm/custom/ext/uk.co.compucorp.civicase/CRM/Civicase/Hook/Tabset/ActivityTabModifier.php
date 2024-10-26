<?php

/**
 * Hook Class to modify Activity Tab in Contacts Summary page.
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
        $useAng = TRUE;
        $tab['url'] = CRM_Utils_System::url('civicrm/case/contact-act-tab', [
          'cid' => $context['contact_id'],
        ]);
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
