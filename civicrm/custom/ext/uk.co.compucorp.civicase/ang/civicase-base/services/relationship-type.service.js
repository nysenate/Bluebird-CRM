(function (angular, $, _, CRM) {
  var module = angular.module('civicase-base');

  module.service('RelationshipType', RelationshipType);

  /**
   * Relationship Type Service
   */
  function RelationshipType () {
    this.getAll = function () {
      return CRM['civicase-base'].relationshipTypes;
    };
  }
})(angular, CRM.$, CRM._, CRM);
