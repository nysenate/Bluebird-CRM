<?php

class CRM_Kam_Page_AJAX {

  public static function navMenu() {
    if (CRM_Core_Session::singleton()->get('userID')) {

      $menu = CRM_Core_BAO_Navigation::buildNavigationTree();
      self::buildHomeMenu($menu);
      CRM_Utils_Hook::navigationMenu($menu);
      CRM_Core_BAO_Navigation::fixNavigationMenu($menu);
      CRM_Core_BAO_Navigation::orderByWeight($menu);
      self::filterByPermission($menu);
      self::formatMenuItems($menu);

      $output = [
        'menu' => $menu,
        'search' => CRM_Utils_Array::makeNonAssociative(CRM_Admin_Page_AJAX::getSearchOptions()),
      ];
      // Encourage browsers to cache for a long time - 1 year
      $ttl = 60 * 60 * 24 * 364;
      CRM_Utils_System::setHttpHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $ttl));
      CRM_Utils_System::setHttpHeader('Cache-Control', "max-age=$ttl, public");
      CRM_Utils_System::setHttpHeader('Content-Type', 'application/json');
      print (json_encode($output));
    }
    CRM_Utils_System::civiExit();
  }

  /**
   * Unset menu items for disabled components and non-permissioned users
   *
   * @param $menu
   */
  public static function filterByPermission(&$menu) {
    foreach ($menu as $key => $item) {
      if (
        (array_key_exists('active', $item['attributes']) && !$item['attributes']['active']) ||
        !CRM_Core_BAO_Navigation::checkPermission($item['attributes'])
      ) {
        unset($menu[$key]);
        continue;
      }
      if (!empty($item['child'])) {
        self::filterByPermission($menu[$key]['child']);
      }
    }
  }

  public static function formatMenuItems(&$menu) {
    foreach ($menu as $key => &$item) {
      $props = $item['attributes'];
      unset($item['attributes']);
      if (!empty($props['separator'])) {
        $item['separator'] = ($props['separator'] == 1 ? 'bottom' : 'top');
      }
      if (!empty($props['icon'])) {
        $item['icon'] = $props['icon'];
      }
      if (!empty($props['attr'])) {
        $item['attr'] = $props['attr'];
      }
      if (!empty($props['url'])) {
        $item['url'] = CRM_Utils_System::evalUrl(CRM_Core_BAO_Navigation::makeFullyFormedUrl($props['url']));
      }
      if (!empty($props['label'])) {
        $item['label'] = ts($props['label'], ['context' => 'menu']);
      }
      $item['name'] = !empty($props['name']) ? $props['name'] : CRM_Utils_String::munge(CRM_Utils_Array::value('label', $props));
      if (!empty($item['child'])) {
        self::formatMenuItems($item['child']);
      }
    }
    $menu = array_values($menu);
  }

  /**
   * @param array $menu
   */
  public static function buildHomeMenu(&$menu) {
    foreach ($menu as &$item) {
      if (CRM_Utils_Array::value('name', $item['attributes']) === 'Home') {
        unset($item['attributes']['label'], $item['attributes']['url']);
        $item['attributes']['icon'] = 'crm-logo-sm';
        $item['attributes']['attr']['accesskey'] = 'm';
        $item['child'] = [
          [
            'attributes' => [
              'label' => 'CiviCRM Home',
              'name' => 'CiviCRM Home',
              'url' => 'civicrm/dashboard?reset=1',
              'weight' => 1,
            ]
          ],
          [
            'attributes' => [
              'label' => 'Hide Menu',
              'name' => 'Hide Menu',
              'url' => '#hidemenu',
              'weight' => 2,
            ]
          ],
          [
            'attributes' => [
              'label' => 'Log out',
              'name' => 'Log out',
              'url' => 'civicrm/logout?reset=1',
              'weight' => 3,
            ]
          ],
        ];
        return;
      }
    }
  }

}
