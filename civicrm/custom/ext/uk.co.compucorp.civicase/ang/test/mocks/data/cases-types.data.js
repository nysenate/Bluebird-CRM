(function (_) {
  var module = angular.module('civicase.data');

  var caseTypesMock = {
    1: {
      id: '1',
      name: 'housing_support',
      title: 'Housing Support',
      description: 'Help homeless individuals obtain temporary and long-term housing',
      definition: {
        activityTypes: [
          {
            name: 'Open Case',
            max_instances: '1'
          },
          {
            name: 'Medical evaluation'
          },
          {
            name: 'Mental health evaluation'
          },
          {
            name: 'Secure temporary housing'
          },
          {
            name: 'Income and benefits stabilization'
          },
          {
            name: 'Long-term housing plan'
          },
          {
            name: 'Follow up'
          },
          {
            name: 'Change Case Type'
          },
          {
            name: 'Change Case Status'
          },
          {
            name: 'Change Case Start Date'
          },
          {
            name: 'Link Cases'
          },
          {
            name: 'Print PDF Letter'
          },
          {
            name: 'Email'
          },
          {
            name: 'Case Task'
          }
        ],
        activitySets: [
          {
            name: 'standard_timeline',
            label: 'Standard Timeline',
            timeline: '1',
            activityTypes: [
              {
                name: 'Open Case',
                status: 'Completed'
              },
              {
                name: 'Medical evaluation',
                reference_activity: 'Open Case',
                reference_offset: '1',
                reference_select: 'newest'
              },
              {
                name: 'Mental health evaluation',
                reference_activity: 'Open Case',
                reference_offset: '1',
                reference_select: 'newest'
              },
              {
                name: 'Secure temporary housing',
                reference_activity: 'Open Case',
                reference_offset: '2',
                reference_select: 'newest'
              },
              {
                name: 'Follow up',
                reference_activity: 'Open Case',
                reference_offset: '3',
                reference_select: 'newest'
              },
              {
                name: 'Income and benefits stabilization',
                reference_activity: 'Open Case',
                reference_offset: '7',
                reference_select: 'newest'
              },
              {
                name: 'Long-term housing plan',
                reference_activity: 'Open Case',
                reference_offset: '14',
                reference_select: 'newest'
              },
              {
                name: 'Follow up',
                reference_activity: 'Open Case',
                reference_offset: '21',
                reference_select: 'newest'
              },
              {
                name: 'Case Task',
                reference_activity: 'Open Case',
                reference_offset: '9',
                reference_select: 'newest'
              },
              {
                name: 'Case Task',
                reference_activity: 'Open Case',
                reference_offset: '10',
                reference_select: 'newest'
              }
            ]
          }
        ],
        timelineActivityTypes: [
          {
            name: 'Open Case',
            status: 'Completed'
          },
          {
            name: 'Medical evaluation',
            reference_activity: 'Open Case',
            reference_offset: '1',
            reference_select: 'newest'
          },
          {
            name: 'Mental health evaluation',
            reference_activity: 'Open Case',
            reference_offset: '1',
            reference_select: 'newest'
          },
          {
            name: 'Secure temporary housing',
            reference_activity: 'Open Case',
            reference_offset: '2',
            reference_select: 'newest'
          },
          {
            name: 'Follow up',
            reference_activity: 'Open Case',
            reference_offset: '3',
            reference_select: 'newest'
          },
          {
            name: 'Income and benefits stabilization',
            reference_activity: 'Open Case',
            reference_offset: '7',
            reference_select: 'newest'
          },
          {
            name: 'Long-term housing plan',
            reference_activity: 'Open Case',
            reference_offset: '14',
            reference_select: 'newest'
          },
          {
            name: 'Follow up',
            reference_activity: 'Open Case',
            reference_offset: '21',
            reference_select: 'newest'
          },
          {
            name: 'Case Task',
            reference_activity: 'Open Case',
            reference_offset: '9',
            reference_select: 'newest'
          },
          {
            name: 'Case Task',
            reference_activity: 'Open Case',
            reference_offset: '10',
            reference_select: 'newest'
          }
        ],
        caseRoles: [
          {
            name: 'Homeless Services Coordinator',
            creator: '1',
            manager: '1'
          },
          {
            name: 'Health Services Coordinator'
          },
          {
            name: 'Benefits Specialist'
          }
        ]
      },
      icon: 'icon',
      color: 'color',
      case_type_category: '1'
    },
    2: {
      id: '2',
      name: 'adult_day_care_referral',
      title: 'Adult Day Care Referral',
      description: 'Arranging adult day care for senior individuals',
      definition: {
        activityTypes: [
          {
            name: 'Open Case',
            max_instances: '1'
          },
          {
            name: 'Medical evaluation'
          },
          {
            name: 'Mental health evaluation'
          },
          {
            name: 'ADC referral'
          },
          {
            name: 'Follow up'
          },
          {
            name: 'Change Case Type'
          },
          {
            name: 'Change Case Status'
          },
          {
            name: 'Change Case Start Date'
          },
          {
            name: 'Link Cases'
          },
          {
            name: 'Print PDF Letter'
          },
          {
            name: 'Email'
          },
          {
            name: 'Case Task'
          }
        ],
        activitySets: [
          {
            name: 'standard_timeline',
            label: 'Standard Timeline',
            timeline: '1',
            activityTypes: [
              {
                name: 'Open Case',
                status: 'Completed'
              },
              {
                name: 'Medical evaluation',
                reference_activity: 'Open Case',
                reference_offset: '3',
                reference_select: 'newest'
              },
              {
                name: 'Mental health evaluation',
                reference_activity: 'Open Case',
                reference_offset: '7',
                reference_select: 'newest'
              },
              {
                name: 'ADC referral',
                reference_activity: 'Open Case',
                reference_offset: '10',
                reference_select: 'newest'
              },
              {
                name: 'Follow up',
                reference_activity: 'Open Case',
                reference_offset: '14',
                reference_select: 'newest'
              }
            ]
          }
        ],
        timelineActivityTypes: [
          {
            name: 'Open Case',
            status: 'Completed'
          },
          {
            name: 'Medical evaluation',
            reference_activity: 'Open Case',
            reference_offset: '3',
            reference_select: 'newest'
          },
          {
            name: 'Mental health evaluation',
            reference_activity: 'Open Case',
            reference_offset: '7',
            reference_select: 'newest'
          },
          {
            name: 'ADC referral',
            reference_activity: 'Open Case',
            reference_offset: '10',
            reference_select: 'newest'
          },
          {
            name: 'Follow up',
            reference_activity: 'Open Case',
            reference_offset: '14',
            reference_select: 'newest'
          }
        ],
        caseRoles: [
          {
            name: 'Senior Services Coordinator',
            creator: '1',
            manager: '1'
          },
          {
            name: 'Health Services Coordinator'
          },
          {
            name: 'Benefits Specialist'
          }
        ]
      },
      case_type_category: '2'
    }
  };

  CRM['civicase-base'].caseTypes = _.clone(caseTypesMock);

  module.provider('CaseTypesMockData', function () {
    /**
     * Merges the given case types to the global case types list.
     *
     * @param {object} newCaseTypes a map of case types indexed by their id.
     */
    this.add = function (newCaseTypes) {
      caseTypesMock = _.assign({}, caseTypesMock, newCaseTypes);
      CRM['civicase-base'].caseTypes = _.clone(caseTypesMock);
    };

    this.$get = () => {
      return {
        /**
         * Returns a list of case types
         *
         * @returns {object} a list of case types indexed by id.
         */
        get: function () {
          return _.clone(caseTypesMock);
        }
      };
    };
  });
}(CRM._));
