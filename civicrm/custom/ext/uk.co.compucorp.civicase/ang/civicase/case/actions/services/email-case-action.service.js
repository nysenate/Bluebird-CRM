(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('EmailCaseAction', EmailCaseAction);

  /**
   * EmailCaseAction service.
   *
   * @param {object} $q $q service
   * @param {object} ts translation service
   * @param {Function} isTruthy service to check if value is truthy
   * @param {object} dialogService dialog service
   * @param {object} CaseType case type service
   * @param {object} CaseTypeCategory case type category service
   * @param {object} civicaseCrmApi service to use civicrm api
   * @param {object} Select2Utils select 2 utility service
   * @param {Function} currentCaseCategory current case category
   */
  function EmailCaseAction ($q, ts, isTruthy, dialogService, CaseType,
    CaseTypeCategory, civicaseCrmApi, Select2Utils, currentCaseCategory) {
    this.isActionAllowed = isActionAllowed;
    this.doAction = doAction;

    /**
     * Check if action is allowed.
     *
     * @param {object} action - action data.
     * @param {object} cases - cases.
     * @param {object} attributes - item attributes.
     *
     * @returns {boolean} - true if action is allowed, false otherwise.
     */
    function isActionAllowed (action, cases, attributes) {
      return attributes.mode === 'case-bulk-actions';
    }

    /**
     * Returns the configuration options to open up a mail popup to
     * communicate with the selected role. Displays an error message
     * when no roles have been assigned to the case.
     *
     * @param {Array} cases list of cases
     * @param {object} action action to be performed
     * @param {Function} callbackFn the callback function
     *
     * @returns {Promise} promise which resolves to the path for the popup
     */
    function doAction (cases, action, callbackFn) {
      var model = {
        caseRoles: [],
        selectedCaseRoles: '',
        caseIds: [],
        deferObject: $q.defer()
      };

      model.caseRoles = getCaseRoles();
      model.caseClientIDs = getClientIds(cases);
      model.caseIds = cases.map(function (caseObj) {
        return caseObj.id;
      });

      openRoleSelectorPopUp(model);

      return model.deferObject.promise;
    }

    /**
     * @param {string|number[]} caseRoleIds list of case roles ids
     * @param {object} model popups model object
     * @returns {Promise} promise resolves to list of contact ids
     */
    function getContactsForCaseIds (caseRoleIds, model) {
      var isClientRoleSelected = _.includes(caseRoleIds, 'client');
      var contactIDs = [];

      if (isClientRoleSelected) {
        contactIDs = model.caseClientIDs;
      }

      var caseRolesWithoutClient = _.without(caseRoleIds, 'client');

      if (caseRolesWithoutClient.length > 0) {
        return getNonClientContacts(caseRolesWithoutClient, model.caseIds)
          .then(function (contacts) {
            contactIDs = contactIDs.concat(contacts);

            return contactIDs;
          });
      } else {
        return $q.resolve(contactIDs);
      }
    }

    /**
     * @param {object[]} cases list of cases object
     * @returns {number[]} list of client ids
     */
    function getClientIds (cases) {
      return _.flatten(_.map(cases, function (caseObj) {
        return _.map(caseObj.client, function (client) {
          return client.contact_id;
        });
      }));
    }

    /**
     * @param {string|number[]} caseRoleIds list of case roles ids
     * @param {string|number[]} caseIDs list of case ids
     * @returns {Promise} promise resolves to list of contact ids
     */
    function getNonClientContacts (caseRoleIds, caseIDs) {
      return civicaseCrmApi('Relationship', 'get', {
        sequential: 1,
        case_id: { IN: caseIDs },
        relationship_type_id: { IN: caseRoleIds },
        is_active: 1,
        options: { limit: 0 }
      }).then(function (relationshipsData) {
        return relationshipsData.values.map(function (relationship) {
          return relationship.contact_id_b;
        });
      });
    }

    /**
     * Get case roles to be displayed on a dropdown list
     *
     * @returns {object[]} list of case roles
     */
    function getCaseRoles () {
      var caseTypeCategoryID = CaseTypeCategory.findByName(currentCaseCategory).value;
      var allCaseRoles = _.map(CaseType.getAllRolesByCategoryID(caseTypeCategoryID), function (caseRole) {
        return _.extend(caseRole, { text: caseRole.name });
      });

      allCaseRoles.unshift({ id: 'client', name: 'Client', text: ts('Client') });

      return allCaseRoles;
    }

    /**
     * Open a popup where user can select roles
     *
     * @param {object} model popups model object
     */
    function openRoleSelectorPopUp (model) {
      dialogService.open(
        'EmailCaseActionRoleSelector',
        '~/civicase/case/actions/directives/email-role-selector.html',
        model,
        {
          autoOpen: false,
          height: '300px',
          width: '40%',
          title: 'Email Case Role(s)',
          buttons: [{
            text: ts('Draft Email'),
            icons: { primary: 'fa-check' },
            click: function () {
              roleSelectorClickHandler(model);
            }
          }]
        }
      );
    }

    /**
     * Click handler for role selector popup sace button
     *
     * @param {object} model popups model object
     */
    function roleSelectorClickHandler (model) {
      if (model.selectedCaseRoles.length === 0) {
        CRM.alert(
          ts('Select case role(s).'),
          ts('No case roles are selected'),
          'error'
        );

        return;
      }

      getContactsForCaseIds(
        Select2Utils.getSelect2Value(model.selectedCaseRoles),
        model
      ).then(function (contactIDs) {
        dialogService.close('EmailCaseActionRoleSelector');
        contactIDs = _.uniq(contactIDs);

        if (contactIDs.length === 0) {
          CRM.alert(
            ts('Please add a contact for the selected role(s).'),
            ts('No contacts available for selected role(s)'),
            'error'
          );
          model.deferObject.resolve();

          return;
        }

        var popupPath = {
          path: 'civicrm/activity/email/add',
          query: {
            action: 'add',
            hideDraftButton: 1,
            reset: 1,
            cid: contactIDs.join(','),
            caseid: model.caseIds[0],
            caseRolesBulkEmail: 1
          }
        };

        if (model.caseIds.length > 1) {
          popupPath.query.allCaseIds = model.caseIds.join(',');
        }

        model.deferObject.resolve(popupPath);
      });
    }
  }
})(angular, CRM.$, CRM._);
