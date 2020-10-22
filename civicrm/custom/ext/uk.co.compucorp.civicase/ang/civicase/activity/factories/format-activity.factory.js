(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.factory('formatActivity', function (ContactsCache, ActivityStatusType, ActivityStatus, ActivityType, CaseStatus, CaseType) {
    var activityTypes = ActivityType.getAll(true);
    var activityStatuses = ActivityStatus.getAll();
    var caseTypes = CaseType.getAll();
    var caseStatuses = CaseStatus.getAll();

    return function (act, caseId) {
      act.category = (activityTypes[act.activity_type_id].grouping ? activityTypes[act.activity_type_id].grouping.split(',') : []);
      act.icon = activityTypes[act.activity_type_id].icon;
      act.type = activityTypes[act.activity_type_id].label;
      act.status = activityStatuses[act.status_id].label;
      act.status_name = activityStatuses[act.status_id].name;
      act.status_type = getStatusType(act.status_id);
      act.is_completed = act.status_type !== 'incomplete'; // FIXME doesn't distinguish cancelled from completed
      act.is_overdue = (typeof act.is_overdue === 'string') ? (act.is_overdue === '1') : act.is_overdue;
      act.color = activityStatuses[act.status_id].color || '#42afcb';
      act.status_css = 'status-type-' + act.status_type + ' activity-status-' + act.status_name.toLowerCase().replace(' ', '-');

      if (act.category.indexOf('alert') > -1) {
        act.color = ''; // controlled by css
      }

      if (caseId && (!act.case_id || act.case_id === caseId || _.contains(act.case_id, caseId))) {
        act.case_id = caseId;
      } else if (act.case_id) {
        act.case_id = act.case_id[0];
      } else {
        act.case_id = null;
      }

      if (act['case_id.case_type_id']) {
        act.case = {};

        _.each(act, function (val, key) {
          if (key.indexOf('case_id.') === 0) {
            act.case[key.replace('case_id.', '')] = val;
            delete act[key];
          }
        });

        act.case.client = [];
        act.case.status = caseStatuses[act.case.status_id];
        act.case.type = caseTypes[act.case.case_type_id];

        _.each(act.case.contacts, function (contact) {
          if (!contact.relationship_type_id) {
            act.case.client.push(contact);
          }
          if (contact.manager) {
            act.case.manager = contact;
          }
        });

        delete act.case.contacts;
      }

      return act;
    };

    /**
     * Get Status Types
     *
     * @param {number/string} statusId statusId
     * @returns {object} status type
     */
    function getStatusType (statusId) {
      var statusType;
      _.each(ActivityStatusType.getAll(), function (statuses, type) {
        if (statuses.indexOf(parseInt(statusId)) >= 0) {
          statusType = type;
        }
      });

      return statusType;
    }
  });
})(angular, CRM.$, CRM._, CRM);
