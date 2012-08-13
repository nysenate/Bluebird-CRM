<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class holds all the Pseudo constants that are specific to Contributions. This avoids
 * polluting the core class and isolates the mass mailer class
 */
class CRM_Contribute_PseudoConstant extends CRM_Core_PseudoConstant {

  /**
   * contribution types
   * @var array
   * @static
   */
  private static $contributionType;

  /**
   * contribution pages
   * @var array
   * @static
   */
  private static $contributionPageActive = NULL;

  /**
   * contribution pages
   * @var array
   * @static
   */
  private static $contributionPageAll = NULL;

  /**
   * payment instruments
   *
   * @var array
   * @static
   */
  private static $paymentInstrument;

  /**
   * credit card
   *
   * @var array
   * @static
   */
  private static $creditCard;

  /**
   * contribution status
   *
   * @var array
   * @static
   */
  private static $contributionStatus;

  /**
   * Personal campaign pages
   * @var array
   * @static
   */
  private static $pcPage;

  /**
   * status of personal campaign page
   * @var array
   * @static
   */
  private static $pcpStatus = array();

  /**
   * Get all the contribution types
   *
   * @access public
   *
   * @return array - array reference of all contribution types if any
   * @static
   */
  public static function &contributionType($id = NULL) {
    if (!self::$contributionType) {
      CRM_Core_PseudoConstant::populate(self::$contributionType,
        'CRM_Contribute_DAO_ContributionType'
      );
    }
    if ($id) {
      $result = CRM_Utils_Array::value($id, self::$contributionType);
      return $result;
    }
    return self::$contributionType;
  }

  /**
   * Flush given pseudoconstant so it can be reread from db
   * nex time it's requested.
   *
   * @access public
   * @static
   *
   * @param boolean $name pseudoconstant to be flushed
   *
   */
  public static function flush($name) {
    self::$$name = NULL;
  }

  /**
   * Get all the contribution pages
   *
   * @param integer $id  id of the contribution page
   * @param boolean $all do we want all pages or only active pages
   *
   * @access public
   *
   * @return array - array reference of all contribution pages if any
   * @static
   */
  public static function &contributionPage($id = NULL, $all = FALSE) {
    if ($all) {
      $cacheVarToUse = &self::$contributionPageAll;
    }
    else {
      $cacheVarToUse = &self::$contributionPageActive;
    }

    if (!$cacheVarToUse) {
      CRM_Core_PseudoConstant::populate($cacheVarToUse,
        'CRM_Contribute_DAO_ContributionPage',
        $all, 'title'
      );
    }
    if ($id) {
      $pageTitle = CRM_Utils_Array::value($id, $cacheVarToUse);
      return $pageTitle;
    }
    return $cacheVarToUse;
  }

  /**
   * Get all the payment instruments
   *
   * @access public
   *
   * @return array - array reference of all payment instruments if any
   * @static
   */
  public static function &paymentInstrument($columnName = 'label') {
    if (!isset(self::$paymentInstrument[$columnName])) {
      self::$paymentInstrument[$columnName] = CRM_Core_OptionGroup::values('payment_instrument',
        FALSE, FALSE, FALSE, NULL, $columnName
      );
    }

    return self::$paymentInstrument[$columnName];
  }

  /**
   * Get all the valid accepted credit cards
   *
   * @access public
   *
   * @return array - array reference of all payment instruments if any
   * @static
   */
  public static function &creditCard() {
    $acceptCreditCard = array();
    $creditCard = CRM_Core_OptionGroup::values('accept_creditcard');

    if (!$creditCard) {
      $creditCard = array();
    }
    foreach ($creditCard as $key => $value) {
      $acceptCreditCard[$value] = $value;
    }
    return $acceptCreditCard;
  }

  /**
   * Get all premiums
   *
   * @access public
   *
   * @return array - array of all Premiums if any
   * @static
   */
  public static function products($pageID = NULL) {
    $products       = array();
    $dao            = new CRM_Contribute_DAO_Product();
    $dao->is_active = 1;
    $dao->orderBy('id');
    $dao->find();

    while ($dao->fetch()) {
      $products[$dao->id] = $dao->name;
    }
    if ($pageID) {
      $dao               = new CRM_Contribute_DAO_Premium();
      $dao->entity_table = 'civicrm_contribution_page';
      $dao->entity_id    = $pageID;
      $dao->find(TRUE);
      $premiumID = $dao->id;

      $productID = array();

      $dao = new CRM_Contribute_DAO_PremiumsProduct();
      $dao->premiums_id = $premiumID;
      $dao->find();
      while ($dao->fetch()) {
        $productID[$dao->product_id] = $dao->product_id;
      }

      $tempProduct = array();
      foreach ($products as $key => $value) {
        if (!array_key_exists($key, $productID)) {
          $tempProduct[$key] = $value;
        }
      }

      return $tempProduct;
    }

    return $products;
  }

  /**
   * Get all the contribution statuses
   *
   * @access public
   *
   * @return array - array reference of all contribution statuses
   * @static
   */
  public static function &contributionStatus($id = NULL, $columnName = 'label') {
    $cacheKey = $columnName;
    if (!isset(self::$contributionStatus[$cacheKey])) {
      self::$contributionStatus[$cacheKey] = CRM_Core_OptionGroup::values('contribution_status',
        FALSE, FALSE, FALSE, NULL, $columnName
      );
    }
    $result = self::$contributionStatus[$cacheKey];
    if ($id) {
      $result = CRM_Utils_Array::value($id, $result);
    }

    return $result;
  }

  /**
   * Get all the Personal campaign pages
   *
   * @access public
   *
   * @return array - array reference of all pcp if any
   * @static
   */
  public static function &pcPage($pageType = NULL, $id = NULL) {
    if (!isset(self::$pcPage[$pageType])) {
      if ($pageType) {
        $params = "page_type='{$pageType}'";
      }
      else {
        $params = '';
      }
      CRM_Core_PseudoConstant::populate(self::$pcPage[$pageType],
        'CRM_PCP_DAO_PCP',
        FALSE, 'title', 'is_active', $params
      );
    }
    $result = self::$pcPage[$pageType];
    if ($id) {
      return $result = CRM_Utils_Array::value($id, $result);
    }

    return $result;
  }

  /**
   * Get all PCP Statuses.
   *
   * The static array pcpStatus is returned
   *
   * @access public
   * @static
   *
   * @return array - array reference of all PCP activity statuses
   */
  public static function &pcpStatus($column = 'label') {
    if (!array_key_exists($column, self::$pcpStatus)) {
      self::$pcpStatus[$column] = array();

      self::$pcpStatus[$column] = CRM_Core_OptionGroup::values('pcp_status', FALSE,
        FALSE, FALSE, NULL, $column
      );
    }
    return self::$pcpStatus[$column];
  }
}

