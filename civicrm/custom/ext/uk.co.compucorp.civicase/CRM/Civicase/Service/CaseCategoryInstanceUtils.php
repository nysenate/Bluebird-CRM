<?php

/**
 * Base abstract for the CaseCategoryInstance classes to implement.
 *
 * This class contains some abstract methods that the case category
 * instance classes should implement. These methods helps distinguish
 * a category instance class for another. e.g There is a method to fetch
 * the menu object which will not be same for the instance classes.
 */
abstract class CRM_Civicase_Service_CaseCategoryInstanceUtils {

  /**
   * Primary key holding relationship between the instance and case category.
   *
   * @var int
   */
  private $caseCategoryInstanceKey;

  /**
   * Returns the menu object for the category instance.
   *
   * @return CRM_Civicase_Service_CaseCategoryMenu
   *   Menu object.
   */
  abstract public function getMenuObject();

  /**
   * Returns the custom group post processor for the category instance.
   *
   * This processor will be responsible for the events that happen after a
   * custom group set extending a case category is saved or updated.
   *
   * @return CRM_Civicase_Service_BaseCustomGroupPostProcessor
   *   Custom group Post processor class.
   */
  abstract public function getCustomGroupPostProcessor();

  /**
   * Returns the case type processor for the category instance.
   *
   * This processor will be responsible for the events that happen after a
   * case type belonging to a particular case category is saved or updated.
   *
   * @return CRM_Civicase_Service_BaseCaseTypePostProcessor|null
   *   Case type post processor class.
   */
  abstract public function getCaseTypePostProcessor();

  /**
   * Returns the custom group display formatter for the category instance.
   *
   * This processor will be responsible for the rows display for the
   * custom group set for the category instance on the custom group
   * listing page.
   *
   * @return CRM_Civicase_Service_BaseCustomGroupDisplayFormatter
   *   Custom group display formatter class.
   */
  abstract public function getCustomGroupDisplayFormatter();

  /**
   * Returns function to fetch Case category entity types.
   *
   * Returns function to fetch entity types for the case category
   * custom group entity for the category instance. For most case category
   * instances, this function is not necessary so returning NULL is fine here.
   */
  public function getCustomGroupEntityTypesFunction() {
    return NULL;
  }

  /**
   * CRM_Civicase_Service_CaseCategoryInstanceUtils constructor.
   *
   * @param int|null $caseCategoryInstanceKey
   *   Case Category Instance Key.
   */
  public function __construct($caseCategoryInstanceKey = NULL) {
    $this->caseCategoryInstanceKey = $caseCategoryInstanceKey;
  }

  /**
   * Returns the caseCategoryInstanceKey variable.
   *
   * @return int
   *   Case category instance key.
   */
  public function getCaseCategoryInstanceKey() {
    return $this->caseCategoryInstanceKey;
  }

}
