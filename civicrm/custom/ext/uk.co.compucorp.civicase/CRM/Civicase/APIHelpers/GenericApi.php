<?php

/**
 * Class CRM_Civicase_APIHelpers_GenericApi.
 */
class CRM_Civicase_APIHelpers_GenericApi {

  /**
   * Return the entity values based on the parameters passed.
   *
   * @param string $entityName
   *   The entity name.
   * @param array $params
   *   API parameters.
   * @param array $returnFields
   *   The fields the API will return.
   *
   * @return array
   *   Array of activities.
   */
  public function getEntityValues($entityName, array $params, array $returnFields = []) {
    $entityValues = [];
    if ($returnFields) {
      $params['return'] = $returnFields;
    }

    $params['options'] = ['limit' => 0];

    $results = civicrm_api3($entityName, 'get', $params);

    if ($results['count'] > 0) {
      $entityValues = $results['values'];
    }

    return $entityValues;
  }

  /**
   * Gets the parameter value from $params array.
   *
   * This function is useful when we want the parameter to not support
   * any SQL operator, i.e we expect a single value or an array of values to
   * be passed in for the parameter.
   *
   * @param array $params
   *   API parameters.
   * @param string $parameterName
   *   Parameter name.
   *
   * @return array
   *   The parameter value.
   */
  public function getParameterValue(array $params, $parameterName) {
    if (empty($params[$parameterName])) {
      return [];
    }

    if (!is_array($params[$parameterName])) {
      return [$params[$parameterName]];
    }

    $acceptedSQLOperators = CRM_Core_DAO::acceptedSQLOperators();
    if (array_intersect($acceptedSQLOperators, array_keys($params[$parameterName]))) {
      throw new InvalidArgumentException("No SQL operators allowed for {$parameterName}");
    }

    return $params[$parameterName];

  }

}
