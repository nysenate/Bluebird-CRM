(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.factory('formatCase', function (formatActivity, ContactsCache, CaseStatus, CaseType) {
    var caseTypes = CaseType.getAll();
    var caseStatuses = CaseStatus.getAll(true);

    return function (item) {
      item.client = [];
      item.subject = (typeof item.subject === 'undefined') ? '' : item.subject;
      item.status = caseStatuses[item.status_id].label;
      item.color = caseStatuses[item.status_id].color;
      item.case_type = caseTypes[item.case_type_id].title;
      item.selected = false;
      item.is_deleted = item.is_deleted === '1';

      countIncompleteOtherTasks(item);

      _.each(item.activity_summary, function (activities) {
        _.each(activities, function (act) {
          formatActivity(act, item.id);
        });
      });

      _.each(item, function (field) {
        if (field && typeof field.activity_date_time !== 'undefined') {
          formatActivity(field, item.id);
        }
      });

      _.each(item.contacts, function (contact) {
        if (!contact.relationship_type_id) {
          item.client.push(contact);
        }

        if (contact.manager) {
          item.manager = contact;
        }
      });

      return item;
    };

    /**
     * Accumulates non communication and task counts as
     * other count for incomplete tasks
     *
     * @param {object} item case
     */
    function countIncompleteOtherTasks (item) {
      item.category_count.other = {};
      item.category_count.other.incomplete = 0;
      item.category_count.other.overdue = 0;

      var excludedCategories = ['communication', 'task', 'other'];

      _.each(_.keys(item.category_count), function (category) {
        if (!_.contains(excludedCategories, category)) {
          item.category_count.other.incomplete += item.category_count[category].incomplete || 0;
          item.category_count.other.overdue += item.category_count[category].overdue || 0;
        }
      });
    }
  });
})(angular, CRM.$, CRM._, CRM);
