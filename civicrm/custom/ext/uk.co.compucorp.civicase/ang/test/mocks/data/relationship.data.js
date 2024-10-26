((_) => {
  var module = angular.module('civicase.data');

  module.service('RelationshipData', function () {
    var relationships = [
      {
        id: '27',
        contact_id_a: '2',
        contact_id_b: '4',
        relationship_type_id: '11',
        start_date: '2020-10-13',
        is_active: '1',
        is_permission_a_b: '0',
        is_permission_b_a: '0',
        case_id: '1'
      },
      {
        id: '28',
        contact_id_a: '5',
        contact_id_b: '6',
        relationship_type_id: '11',
        start_date: '2020-10-13',
        is_active: '1',
        is_permission_a_b: '0',
        is_permission_b_a: '0',
        case_id: '22'
      }
    ];

    return {
      /**
       * Returns a list of mocked relationships
       *
       * @returns {Array} list of relationships
       */
      get: function () {
        return angular.copy(relationships);
      }
    };
  });
})(CRM._);
