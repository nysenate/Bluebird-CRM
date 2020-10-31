(function (angular, _) {
  var module = angular.module('civicase.data');

  module.service('activitiesMockData', ['datesMockData', function (dates) {
    var activitiesMockData = [
      {
        id: '1717',
        activity_type_id: '13',
        activity_date_time: dates.yesterday,
        status_id: '2',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '0',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: []
      },
      {
        id: '1718',
        activity_type_id: '56',
        activity_date_time: dates.today,
        status_id: '2',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '0',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: []
      },
      {
        id: '1719',
        activity_type_id: '58',
        activity_date_time: dates.today,
        status_id: '1',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '1',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: []
      },
      {
        id: '1720',
        activity_type_id: '66',
        activity_date_time: dates.today,
        status_id: '1',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '1',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: []
      },
      {
        id: '1721',
        activity_type_id: '14',
        activity_date_time: dates.tomorrow,
        status_id: '1',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '1',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: []
      },
      {
        id: '1722',
        activity_type_id: '14',
        activity_date_time: dates.tomorrow,
        status_id: '2',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '0',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: [
          '147'
        ]
      },
      {
        id: '1723',
        activity_type_id: '14',
        activity_date_time: dates.theDayAfterTomorrow,
        status_id: '1',
        is_star: '0',
        case_id: [
          '136'
        ],
        is_overdue: '0',
        source_contact_id: '202',
        target_contact_id: [
          '197'
        ],
        assignee_contact_id: []
      }
    ];

    return {
      /**
       * Returns a list of mocked activities
       *
       * @returns {object[]} each array contains an object with the activity data.
       */
      get: function () {
        return _.clone(activitiesMockData);
      },

      /**
       * Returns sent number of mocked activities
       *
       * @param {number} number the number of activities to return.
       * @returns {object[]} each array contains an object with the activity data.
       */
      getSentNoOfActivities: function (number) {
        var activities = [];
        var activity;

        for (var i = 0; i < number; i++) {
          activity = _.clone(activitiesMockData[0]);
          activity.id = parseInt(activity.id) + i;

          activities.push(activity);
        }

        return activities;
      }
    };
  }]);
})(angular, CRM._);
