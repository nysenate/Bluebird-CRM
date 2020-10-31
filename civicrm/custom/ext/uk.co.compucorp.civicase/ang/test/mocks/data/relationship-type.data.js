(function () {
  var module = angular.module('civicase.data');

  var relationshipTypes = [
    {
      id: '17',
      name_a_b: 'Application Manager is',
      label_a_b: 'Application Manager is',
      name_b_a: 'Application Manager',
      label_b_a: 'Application Manager',
      description: 'Application Manager',
      is_active: '1'
    },
    {
      id: '14',
      name_a_b: 'Benefits Specialist is',
      label_a_b: 'Benefits Specialist is',
      name_b_a: 'Benefits Specialist',
      label_b_a: 'Benefits Specialist',
      description: 'Benefits Specialist',
      contact_type_a: 'Individual',
      contact_type_b: 'Individual',
      is_reserved: '0',
      is_active: '1'
    }
  ];

  module.constant('RelationshipTypeData', {
    values: relationshipTypes
  });
}());
