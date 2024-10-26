(function () {
  var module = angular.module('civicase.data');

  var groups = [
    {
      id: '1',
      name: 'Group1',
      title: 'Group 1',
      description: 'Contacts in this group are assigned Administrator role permissions.',
      is_active: '1',
      visibility: 'User and User Admin Only',
      group_type: ['1'],
      is_hidden: '0',
      is_reserved: '0'
    }, {
      id: '2',
      name: 'Group2',
      title: 'Group 2',
      description: 'Contacts in this group are assigned user role permissions.',
      is_active: '1',
      visibility: 'User Only',
      group_type: ['1'],
      is_hidden: '0',
      is_reserved: '0'
    }
  ];

  module.constant('GroupData', {
    values: groups
  });
}());
