(function(angular, $, _) {
  "use strict";

  // Define module & autoload dependencies.
  angular.module('contactlayout', CRM.angRequires('contactlayout'))

    // Service for loading relationship type options and displaying loading state.
    .service('contactLayoutRelationshipOptions', function() {
      const RELATIONSHIP_TYPES = CRM.vars.contactlayout.relationshipTypes;
      const service = this;

      service.options = formatRelationshipOptions(RELATIONSHIP_TYPES);
      service.getRelationshipFromOption = getRelationshipFromOption;

      // for each relationship type, it includes an option for the a_b relationship
      // and another for the b_a relationship.
      function formatRelationshipOptions (relationshipTypeResponse) {
        return _.chain(relationshipTypeResponse)
          .reduce(function (result, relationshipType) {
            const isReciprocal = relationshipType.label_a_b === relationshipType.label_b_a;

            if (isReciprocal) {
              result.push({ id: relationshipType.id + '_r', text: relationshipType.label_a_b });
            } else {
              result.push({ id: relationshipType.id + '_ab', text: relationshipType.label_a_b });
              result.push({ id: relationshipType.id + '_ba', text: relationshipType.label_b_a });
            }

            return result;
          }, [])
          .sortBy('text')
          .value();
      }

      // Returns the relationship type data and direction for the given relationship option
      function getRelationshipFromOption (relationshipOption) {
        const relationship = relationshipOption.split('_');
        const relationshipTypeId = parseInt(relationship[0], 10);
        const relationshipType = _.find(RELATIONSHIP_TYPES, { id: relationshipTypeId });

        return {
          type: relationshipType,
          direction: relationship[1]
        };
      }
    });

})(angular, CRM.$, CRM._);
