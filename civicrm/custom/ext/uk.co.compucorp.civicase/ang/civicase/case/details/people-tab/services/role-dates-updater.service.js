(function (_, $, angular) {
  var module = angular.module('civicase');

  module.service('civicaseRoleDatesUpdater', civicaseRoleDatesUpdater);

  /**
   * Provides the data and handlers needed to update the date for a relationship.
   *
   * @param {string} dateInputFormatValue The Date Format according to CiviCRM configuration.
   * @param {string} loggedInContactId The ID of the logged in user.
   */
  function civicaseRoleDatesUpdater (dateInputFormatValue, loggedInContactId) {
    var API_DATE_FORMAT = 'yy-mm-dd';

    this.getApiCallsForEndDate = getApiCallsForEndDate;
    this.getApiCallsForStartDate = getApiCallsForStartDate;
    this.updatePreviousValue = updatePreviousValue;

    /**
     * @param {string} dateString A date in a year-month-day format.
     * @returns {string} A formatted date according to the CiviCRM settings.
     */
    function formatDate (dateString) {
      return $.datepicker.formatDate(
        dateInputFormatValue,
        $.datepicker.parseDate(API_DATE_FORMAT, dateString)
      );
    }

    /**
     * @param {RoleDateChangeParams} params List of parameters used for building
     *   the change role date's API calls.
     * @returns {Array} a list of API calls for updating the date for each case
     *   relation and also create an activity that records the change done to the
     *   date.
     */
    function getApiCallsForDate (params) {
      var role = params.role;
      var caseId = params.caseId;
      var currentDateValue = role.relationship[params.dateFieldName];
      var subject = getRoleDateChangeActivitySubject(params);
      var relationshipApiCallParams = {};
      relationshipApiCallParams[params.dateFieldName] = currentDateValue;

      return _([])
        .concat(getUpdateRelationshipCalls(role, relationshipApiCallParams))
        .push(
          ['Activity', 'create', {
            activity_type_id: params.activityTypeId,
            activity_date_time: 'now',
            case_id: caseId,
            source_contact_id: loggedInContactId,
            status_id: 'Completed',
            subject: subject
          }]
        )
        .value();
    }

    /**
     * @param {object} role A role object as provided by the roles service.
     * @param {string} caseId The ID of the case the role belongs to.
     * @returns {Array} Returns the API calls needed to update the role's end
     *   date for each relation and record the change in an activity.
     */
    function getApiCallsForEndDate (role, caseId) {
      return getApiCallsForDate({
        activityTypeId: 'Change Case Role End Date',
        caseId: caseId,
        dateFieldName: 'end_date',
        dateFieldLabel: 'end date',
        role: role
      });
    }

    /**
     * @param {object} role A role object as provided by the roles service.
     * @param {string} caseId The ID of the case the role belongs to.
     * @returns {Array} Returns the API calls needed to update the role's start
     *   date for each relation and record the change in an activity.
     */
    function getApiCallsForStartDate (role, caseId) {
      return getApiCallsForDate({
        activityTypeId: 'Change Case Role Start Date',
        caseId: caseId,
        dateFieldName: 'start_date',
        dateFieldLabel: 'start date',
        role: role
      });
    }

    /**
     * @param {RoleDateChangeParams} params List of parameters used for building
     *   the change role date's API calls.
     * @returns {string} Returns the subject used for the activity that gets
     *   recorded when the role's start or end date changes.
     */
    function getRoleDateChangeActivitySubject (params) {
      var role = params.role;
      var previousDateValue = role.previousValues[params.dateFieldName];
      var currentDateValue = role.relationship[params.dateFieldName];
      var subject = role.display_name + ', with ' + role.role + ' case role,' +
        ' had ' + params.dateFieldLabel + ' changed';

      if (previousDateValue) {
        subject += ' from ' + formatDate(previousDateValue);
      }

      if (currentDateValue) {
        subject += ' to ' + formatDate(currentDateValue);
      }

      return subject;
    }

    /**
     * @param {object} role A role object as provided by the roles service.
     * @param {object} extraParams Extra parameters to pass to each one of the
     *   relationship update api calls.
     * @returns {Array} A list of api calls to update all relationships assigned
     *   to a role.
     */
    function getUpdateRelationshipCalls (role, extraParams) {
      return _(role.relationship.relationship_ids)
        .map(function (relationshipId) {
          var apiParams = _.assign({ id: relationshipId }, extraParams);

          return ['Relationship', 'create', apiParams];
        })
        .value();
    }

    /**
     * Stores the current value of the role's relationship in the previous
     * values object. Useful for storing the relationship date changes.
     *
     * @param {object} role A role object as provided by the roles service.
     * @param {string} fieldName The name of the field to store.
     */
    function updatePreviousValue (role, fieldName) {
      role.previousValues[fieldName] = role.relationship[fieldName];
    }

    /**
     * @typedef {object} RoleDateChangeParams
     * @param {string} activityTypeId Name of the activity type that will
     *   be used when recording the date change.
     *   Ex: "Change Case Role End Date".
     * @param {string} caseId The ID of the case the activity will belong
     *   to.
     * @param {string} dateFieldLabel Human readable name for the date
     *   field. Example: "start date".
     * @param {string} dateFieldName Name of the date field as defined
     *   in the endpoint.
     * @param {object} role Role data as returned by the People's tab
     *   role service.
     */
  }
})(CRM._, CRM.$, angular);
