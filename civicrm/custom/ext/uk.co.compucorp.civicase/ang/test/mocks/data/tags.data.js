(function (angular, _) {
  var module = angular.module('civicase.data');

  module.service('TagsMockData', function () {
    var tagsMockData = [
      {
        id: '1',
        name: 'Non-profit',
        description: 'Any not-for-profit organization.',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_contact'
      },
      {
        id: '2',
        name: 'Company',
        description: 'For-profit organization.',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_contact'
      },
      {
        id: '3',
        name: 'Government Entity',
        description: 'Any governmental entity.',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_contact'
      },
      {
        id: '4',
        name: 'Major Donor',
        description: 'High-value supporter of our organization.',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_contact'
      },
      {
        id: '5',
        name: 'Volunteer',
        description: 'Active volunteers.',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_contact'
      },
      {
        id: '6',
        name: 'Fruit',
        description: 'Sweet and nutritious',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '1',
        used_for: 'civicrm_activity,civicrm_case',
        created_id: '202',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '7',
        name: 'Apple',
        description: 'An apple a day keeps the Windows away',
        parent_id: '6',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity,civicrm_case',
        created_id: '202',
        color: '#ec3737',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '8',
        name: 'Banana',
        description: 'Going bananas for tagsets',
        parent_id: '6',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity,civicrm_case',
        created_id: '202',
        color: '#d5d620',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '9',
        name: 'Grape',
        description: 'I heard it through the grapevine',
        parent_id: '6',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity,civicrm_case',
        created_id: '202',
        color: '#9044b8',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '10',
        name: 'Orange',
        description: 'Orange you glad this isn\'t a pun?',
        parent_id: '6',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity,civicrm_case',
        created_id: '202',
        color: '#ff9d2a',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '11',
        name: 'Edge Case',
        description: 'Edge Case',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_case',
        created_id: '202',
        color: '#000000',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '12',
        name: 'Strenuous',
        description: 'Strenuous activity',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        color: '#00ff00',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '13',
        name: 'Leisurely',
        description: 'Leisurely activity',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        color: '#006f00',
        created_date: '2018-10-11 12:38:07'
      },
      {
        id: '14',
        name: 'AAA',
        is_selectable: '0',
        is_reserved: '0',
        is_tagset: '1',
        used_for: 'civicrm_activity',
        created_id: '202',
        created_date: '2018-12-17 08:57:28'
      },
      {
        id: '15',
        name: 'awq',
        parent_id: '14',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        created_date: '2018-12-17 08:57:36'
      },
      {
        id: '16',
        name: '1112',
        parent_id: '12',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        created_date: '2018-12-17 09:13:09'
      },
      {
        id: '17',
        name: 'L1',
        parent_id: '13',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        created_date: '2018-12-27 07:38:06'
      },
      {
        id: '18',
        name: 'L2',
        parent_id: '17',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        created_date: '2018-12-27 07:38:15'
      },
      {
        id: '18',
        name: 'L3',
        parent_id: '1',
        is_selectable: '1',
        is_reserved: '0',
        is_tagset: '0',
        used_for: 'civicrm_activity',
        created_id: '202',
        created_date: '2018-12-27 07:38:15'
      }
    ];

    return {
      /**
       * Returns a list of mocked tags
       *
       * @returns {Array} each array contains an object with the tags data.
       */
      get: function () {
        return _.clone(tagsMockData);
      }
    };
  });
})(angular, CRM._);
