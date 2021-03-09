<?php

/**
 * The base class for case category custom group display formatting.
 */
abstract class CRM_Civicase_Service_BaseCustomGroupDisplayFormatter {

  /**
   * Process the category display on the custom group listing page.
   *
   * Some case category instance requires to format the custom group
   * display on the listing page differently hence the need for this
   * function. This function will modify the custom group page object
   * and modify the rows/fields display as necessary.
   *
   * @param array $row
   *   One of the rows of the custom group listing page.
   */
  abstract public function processDisplay(array &$row);

}
