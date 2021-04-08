(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('RelationshipType', RelationshipType);

  /**
   * Relationship Type Service
   */
  function RelationshipType () {
    this.getAll = getAll;
    this.getByName = getByName;

    /**
     * Get all relationship types
     *
     * @returns {object[]} relationship types
     */
    function getAll () {
      return CRM['civicase-base'].relationshipTypes;
    }

    /**
     * Get relationship type for the given name
     *
     * @param {string} relationshipName relationship name
     * @returns {string} relationship name
     */
    function getByName (relationshipName) {
      return _.find(getAll(), function (relationship) {
        return relationship.name_a_b === relationshipName ||
          relationship.name_b_a === relationshipName;
      });
    }
  }
})(angular, CRM.$, CRM._, CRM);
