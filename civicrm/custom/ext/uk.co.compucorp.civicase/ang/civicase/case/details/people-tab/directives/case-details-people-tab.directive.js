(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseDetailsPeopleTab', function () {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/details/people-tab/directives/case-details-people-tab.directive.html',
      controller: civicaseViewPeopleController,
      scope: {
        item: '=civicaseCaseDetailsPeopleTab',
        refresh: '=refreshCallback'
      }
    };
  });

  module.controller('civicaseViewPeopleController', civicaseViewPeopleController);

  /**
   * @typedef {{
   *   contact_id: number,
   *   contact_sub_type: string,
   *   contact_type: string,
   *   display_name: string,
   *   relationship_type_id: number,
   *   role: string,
   * }} Role
   * @typedef {{
   *   showDescriptionField: boolean,
   *   role: Role,
   *   title: string
   * }} ContactPromptOptions
   * @typedef {{
   *  contact: {
   *    id: number,
   *    extra: {
   *      display_name: string
   *    }
   *  },
   *  description: string,
   *  event: document#event,
   *  role: Role,
   *  showContactSelectionError: (message: string) => void
   * }} ContactPromptResult
   */

  /**
   * civicaseViewPeopleController Controller
   *
   * @param {object} $scope $scope
   * @param {object} allowMultipleCaseClients allow multiple clients configuration value
   * @param {Function} civicaseCrmUrl crm url service.
   * @param {object} civicaseCrmApi service to interact with civicrm api
   * @param {object} civicasePeopleTabRoles People's tab roles list service
   * @param {object} DateHelper DateHelper
   * @param {object} PeoplesTabMessageConstants Error message strings
   * @param {object} ts ts
   * @param {object} RelationshipType RelationshipType
   * @param {object} civicaseRoleDatesUpdater Role Dates Updater service
   * @param {boolean} civicaseSingleCaseRolePerType if a single case role can be assigned per type
   * @param {object} dialogService A reference to the dialog service
   * @param {Function} removeDatePickerHrefs Removes date picker href attributes
   * @param {Function} civicaseCrmLoadForm service to load civicrm forms
   */
  function civicaseViewPeopleController ($scope, allowMultipleCaseClients,
    civicaseCrmUrl, civicaseCrmApi, civicasePeopleTabRoles, DateHelper,
    PeoplesTabMessageConstants, ts, RelationshipType,
    civicaseRoleDatesUpdater, civicaseSingleCaseRolePerType, dialogService,
    removeDatePickerHrefs, civicaseCrmLoadForm) {
    // The ts() and hs() functions help load strings for this module.
    var clients = _.indexBy($scope.item.client, 'contact_id');
    var item = $scope.item;
    var relTypes = RelationshipType.getAll();

    $scope.ts = ts;
    $scope.allowMultipleCaseClients = allowMultipleCaseClients;
    $scope.civicaseSingleCaseRolePerType = civicaseSingleCaseRolePerType;
    $scope.roles = civicasePeopleTabRoles;
    $scope.rolesFilter = '';
    $scope.rolesPage = 1;
    $scope.rolesAlphaFilter = '';
    $scope.rolesSelectionMode = '';
    $scope.rolesSelectedTask = '';
    $scope.relations = [];
    $scope.relationsPage = 1;
    $scope.relationsAlphaFilter = '';
    $scope.relationsSelectionMode = '';
    $scope.relationsSelectedTask = '';
    $scope.roleDatesUpdater = civicaseRoleDatesUpdater;
    $scope.showInactiveRoles = false;
    $scope.letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
    $scope.contactTasks = CRM.civicase.contactTasks;
    $scope.ceil = Math.ceil;
    $scope.isRelationshipLoading = true;

    $scope.formatDate = DateHelper.formatDate;
    $scope.getRelations = getRelations;
    $scope.checkIfRoleIsDisabled = checkIfRoleIsDisabled;
    $scope.getSelectedContacts = getSelectedContacts;
    $scope.setSelectionMode = setSelectionMode;
    $scope.setTab = setTab;
    $scope.setLetterFilter = setLetterFilter;
    $scope.doBulkAction = doBulkAction;
    $scope.doContactTask = doContactTask;
    $scope.assignRoleOrClient = assignRoleOrClient;
    $scope.replaceRoleOrClient = replaceRoleOrClient;
    $scope.unassignRole = unassignRole;
    $scope.toggleInactiveRoles = toggleInactiveRoles;

    (function init () {
      $scope.$bindToRoute({ expr: 'tab', param: 'peopleTab', format: 'raw', default: 'roles' });
      $scope.$watch('item', function () {
        if (!$scope.item.definition) {
          return;
        }

        $scope.roles.setCaseContacts($scope.item.contacts);
        $scope.roles.setCaseRelationships($scope.item['api.Relationship.get'].values);
        $scope.roles.setCaseTypeRoles($scope.item.definition.caseRoles);
        $scope.roles.updateRolesList({
          showInactiveRoles: $scope.showInactiveRoles
        });
      }, true);
      $scope.$watch('rolesPage', function () {
        $scope.roles.goToPage($scope.rolesPage);
      });
      $scope.$watch('tab', function (tab) {
        if (tab === 'relations' && !$scope.relations.length) {
          getRelations();
        }
      });
    }());

    /**
     * Toggle displaying inactive roles in the UI
     */
    function toggleInactiveRoles () {
      $scope.showInactiveRoles = !$scope.showInactiveRoles;

      $scope.roles.updateRolesList({
        showInactiveRoles: $scope.showInactiveRoles
      });
    }

    /**
     * Get selected contacts from the selection bar
     *
     * @param {string} tab tab
     * @param {boolean} onlyChecked onlyChecked
     * @returns {Array} selected contact
     */
    function getSelectedContacts (tab, onlyChecked) {
      var idField = tab === 'roles' ? 'contact_id' : 'id';
      var recordsList = tab === 'roles' ? $scope.roles.fullRolesList : $scope.relations;
      var isCheckedSelectionMode = $scope[tab + 'SelectionMode'] === 'checked';
      var isAllSelectionMode = $scope[tab + 'SelectionMode'] === 'all';

      if (isCheckedSelectionMode) {
        return _(recordsList).filter({ checked: true }).map(idField)
          .uniq().value();
      } else if (isAllSelectionMode) {
        return _(recordsList).map(idField).uniq().compact().value();
      }

      return [];
    }

    /**
     * Sets selection mode
     *
     * @param {string} mode mode
     * @param {string} tab tab
     */
    function setSelectionMode (mode, tab) {
      $scope[tab + 'SelectionMode'] = mode;
    }

    /**
     * Sets selected tab
     *
     * @param {string} tab tab
     */
    function setTab (tab) {
      $scope.tab = tab;
    }

    /**
     * Filters result on the basis of letter clicked
     *
     * @param {string} letter letter
     * @param {string} tab tab
     */
    function setLetterFilter (letter, tab) {
      if ($scope[tab + 'AlphaFilter'] === letter) {
        $scope[tab + 'AlphaFilter'] = '';
      } else {
        $scope[tab + 'AlphaFilter'] = letter;
      }

      if (tab === 'roles') {
        $scope.roles.filterRoles($scope.rolesAlphaFilter, $scope.rolesFilter);
      } else {
        $scope.getRelations();
      }
    }

    /**
     * @param {string} key key of the bulk action
     */
    function doBulkAction (key) {
      $scope.rolesSelectedTask = key;
      $scope.doContactTask('roles');
    }

    /**
     * Update the contacts with the task
     *
     * @param {string} tab tab
     */
    function doContactTask (tab) {
      var task = $scope.contactTasks[$scope[tab + 'SelectedTask']];
      $scope[tab + 'SelectedTask'] = '';
      civicaseCrmLoadForm(civicaseCrmUrl(task.url, { cids: $scope.getSelectedContacts(tab).join(',') }))
        .on('crmFormSuccess', $scope.refresh)
        .on('crmFormSuccess', function () {
          $scope.refresh();
          if (tab === 'relations') {
            $scope.getRelations();
          }
        });
    }

    /**
     * Prompts the user to create either a new role or client related to the case.
     *
     * @param {Role} role the role to assign to the case.
     */
    function assignRoleOrClient (role) {
      var isAssigningRole = role && !!role.relationship_type_id;

      isAssigningRole
        ? assignRole(role)
        : assignClient();
    }

    /**
     * Replaces the given role or client with another one selected by the user.
     *
     * @param {Role} role the role to replace.
     */
    function replaceRoleOrClient (role) {
      var isReplacingClient = !role.relationship_type_id;

      var promptForContactParams = {
        title: ts('Replace %1', { 1: role.role }),
        showDescriptionField: !isReplacingClient,
        role: role
      };

      if (!isReplacingClient) {
        promptForContactParams.reassignmentDate = {
          maxDate: moment().format('YYYY-MM-DD'),
          value: moment().isBefore(moment(role.relationship.start_date))
            ? moment(role.relationship.start_date).format('YYYY-MM-DD')
            : moment().format('YYYY-MM-DD')
        };
      }

      promptForContactThatIsNotCaseClient(
        promptForContactParams,
        handleReplaceRoleOrClient
      );
    }

    /**
     * Unassign the role to a contact
     *
     * @param {object} role role
     */
    function unassignRole (role) {
      // when client
      if (!role.relationship_type_id) {
        CRM.confirm({
          title: ts('Remove %1', { 1: role.role }),
          message: ts('Remove %1 as %2?', { 1: role.display_name, 2: role.role })
        }).on('crmConfirm:yes', function () {
          var apiCalls = [unassignClientCall(role)];
          getApiParamsToSetRelationshipsAsInactiveWhenClientIsRemoved(role, apiCalls);

          makeAPICalltoUnassignRole(apiCalls, 'Remove Client From Case', role);
        });
      } else {
        promptForContact(
          {
            title: ts('Remove %1 as %2?', { 1: role.display_name, 2: role.role }),
            showDescriptionField: false,
            hideContactField: true,
            role: role,
            endDate: {
              maxDate: moment().format('YYYY-MM-DD'),
              value: moment().format('YYYY-MM-DD')
            }
          },
          function (contactPromptResult) {
            if (!isSameOrAfter(
              contactPromptResult.endDate,
              contactPromptResult.role.relationship.start_date
            )) {
              contactPromptResult.showErrorMessageFor(
                'endDate',
                PeoplesTabMessageConstants.RELATIONSHIP_END_DATE_MESSAGE
              );

              return;
            }

            var apiCalls = [unassignRoleCall(role, contactPromptResult.endDate)];

            makeAPICalltoUnassignRole(apiCalls, 'Remove Case Role', role);
          }
        );
      }
    }

    /**
     * @param {string} date1 first date
     * @param {string} date2 second date
     * @returns {boolean} if date 1 same of after date 2
     */
    function isSameOrAfter (date1, date2) {
      return moment(date1).isSameOrAfter(moment(date2));
    }

    /**
     * @param {object[]} apiCalls list of api calls
     * @param {string} activityType activity type
     * @param {object} role role object
     */
    function makeAPICalltoUnassignRole (apiCalls, activityType, role) {
      apiCalls.push(getActivityApiCallRelatedToRole({
        activity_type_id: activityType,
        subject: ts('%1 removed as %2', { 1: role.display_name, 2: role.role }),
        target_contact_id: role.contact_id
      }));

      $scope.refresh(apiCalls);
    }

    /**
     * Check if the sent role should be disabled
     *
     * @param {object} role role
     * @returns {boolean} if the sent role should be disabled
     */
    function checkIfRoleIsDisabled (role) {
      return $scope.civicaseSingleCaseRolePerType ? role.count === 1 : false;
    }

    /**
     * Returns the parameters needed to create a completed activity related to the case.
     *
     * @param {object} extraParams extra parameters to pass to the activity creation call.
     * @returns {[string, string, object]} the create activity api call params.
     */
    function getActivityApiCallRelatedToRole (extraParams) {
      return ['Activity', 'create', _.extend({}, {
        case_id: item.id,
        status_id: 'Completed'
      }, extraParams)];
    }

    /**
     * Returns all the calls needed to create relationships between the selected contact and all the
     * clients related to the case.
     *
     * If it needs to replace the previous relationship it first makes a call to retrieve the
     * previous relationship ID, which is needed when passing the `reassign_rel_id` parameter.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     * @param {boolean} replacePreviousRelationship whether to replace previous relationship
     * @returns {Array[]} a list of api calls.
     */
    function getCreateCaseRoleApiCalls (contactPromptResult, replacePreviousRelationship) {
      var params = {
        relationship_type_id: contactPromptResult.role.relationship_type_id,
        start_date: contactPromptResult.startDate || contactPromptResult.reassignmentDate,
        end_date: null,
        contact_id_b: contactPromptResult.contact.id,
        case_id: item.id,
        description: contactPromptResult.description
      };

      if (!replacePreviousRelationship) {
        return _.map(item.client, function (client) {
          return ['Relationship', 'create', _.extend({ contact_id_a: client.contact_id }, params)];
        });
      } else {
        return _.map(item.client, function (client) {
          return ['Relationship', 'get', {
            case_id: item.id,
            contact_id_b: contactPromptResult.role.contact_id,
            is_active: 1,
            relationship_type_id: contactPromptResult.role.relationship_type_id,
            'api.Relationship.create': _.extend({}, params, {
              id: false,
              contact_id_a: client.contact_id,
              reassign_rel_id: '$value.id'
            })
          }];
        });
      }
    }

    /**
     * Prompts for a client contact and assigns it to the case.
     * Passes the selected client to the assign client handler.
     */
    function assignClient () {
      var role = { role: ts('Client') };

      promptForContact(
        {
          title: ts('Add Client'),
          showDescriptionField: false,
          role: role
        },
        handleAssignClient
      );
    }

    /**
     * Prompts the user to select a contact to assign them to the case.
     * Relationships between the selected contact and the case clients are
     * created using the provided role. This event is also recorded as an
     * activity related to the case.
     *
     * @param {Role} role the role details.
     */
    function assignRole (role) {
      promptForContactThatIsNotCaseClient(
        {
          title: ts('Add %1', { 1: role.role }),
          showDescriptionField: true,
          role: role,
          startDate: moment().format('YYYY-MM-DD')
        },
        handleAssignRole
      );
    }

    /**
     * Determines if the given client id is already part of the list of case clients.
     *
     * @param {number} contactId the contact id to check.
     * @returns {boolean} true if the given client is part of the case.
     */
    function checkContactIsClient (contactId) {
      return _.some(item.client, function (client) {
        return client.contact_id === contactId;
      });
    }

    /**
     * Returns the activity subject used for replacing a case contact.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     * @returns {string} the activity subject.
     */
    function getActivitySubjectForReplaceCaseContact (contactPromptResult) {
      return ts('%1 replaced %2 as %3', {
        1: contactPromptResult.contact.extra.display_name,
        2: contactPromptResult.role.display_name,
        3: contactPromptResult.role.role
      });
    }

    /**
     * @param {object} role role object
     * @param {object[]} apiCalls list of api calls
     */
    function getApiParamsToSetRelationshipsAsInactiveWhenClientIsRemoved (role, apiCalls) {
      apiCalls.push(['Relationship', 'get', {
        case_id: item.id,
        is_active: 1,
        contact_id_a: role.contact_id,
        'api.Relationship.create': { is_active: 0, end_date: 'now' }
      }]);
    }

    /**
     * @param {object} contactPromptResult contact prompt result object
     * @param {object[]} apiCalls list of api calls
     */
    function getApiParamsToReassignExistingRelationshipsToNewClient (contactPromptResult, apiCalls) {
      apiCalls.push(['Relationship', 'get', {
        case_id: item.id,
        is_active: true,
        contact_id_a: contactPromptResult.role.contact_id,
        'api.Relationship.update': { contact_id_a: contactPromptResult.contact.id }
      }]);
    }

    /**
     * @param {object} contactPromptResult contact prompt result object
     * @param {object[]} apiCalls list of api calls
     */
    function getApiParamsToDuplicateExistingRelationshipsToNewClient (contactPromptResult, apiCalls) {
      apiCalls.push(['Relationship', 'get', {
        case_id: item.id,
        contact_id_a: item.client[0].contact_id,
        is_active: 1,
        'api.Relationship.create': {
          id: false,
          contact_id_a: contactPromptResult.contact.id,
          start_date: 'now',
          contact_id_b: '$value.contact_id_b',
          relationship_type_id: '$value.relationship_type_id',
          description: '$value.description',
          case_id: '$value.case_id'
        }
      }]);
    }

    /**
     * Returns the API calls necessary to replace the case client and record the event as an activity.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     * @returns {Array} the list of api calls to replace the case client.
     */
    function getReplaceClientApiCalls (contactPromptResult) {
      var activitySubject = getActivitySubjectForReplaceCaseContact(contactPromptResult);
      var apiCalls = [
        getActivityApiCallRelatedToRole({
          activity_type_id: 'Reassigned Case',
          subject: activitySubject,
          target_contact_id: [
            contactPromptResult.contact.id,
            contactPromptResult.role.contact_id
          ]
        }),
        ['CaseContact', 'get', {
          case_id: item.id,
          contact_id: contactPromptResult.role.contact_id,
          'api.CaseContact.create': {
            case_id: item.id,
            contact_id: parseInt(contactPromptResult.contact.id)
          }
        }]
      ];

      getApiParamsToReassignExistingRelationshipsToNewClient(contactPromptResult, apiCalls);

      return apiCalls;
    }

    /**
     * Returns the API calls necessary to replace the case role and record the event as an activity.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     * @returns {Array} the list of api calls to replace the case role.
     */
    function getReplaceRoleApiCalls (contactPromptResult) {
      var apiCalls = [];

      if (!contactPromptResult.role.relationship_type_id) {
        apiCalls = [unassignClientCall(contactPromptResult.role)];
      }

      return apiCalls.concat(
        getCreateCaseRoleApiCalls(contactPromptResult, true)
      );
    }

    /**
     * Sends the API calls necessary to assign the given client contact to the
     * current case. This event is also recorded as an activity related
     * to the case.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     */
    function handleAssignClient (contactPromptResult) {
      var activitySubject = ts('%1 added as Client', {
        1: contactPromptResult.contact.extra.display_name
      });
      var apiCalls = [
        getActivityApiCallRelatedToRole({
          activity_type_id: 'Add Client To Case',
          subject: activitySubject,
          target_contact_id: contactPromptResult.contact.id
        }),
        ['CaseContact', 'create', {
          case_id: item.id,
          contact_id: contactPromptResult.contact.id
        }]
      ];

      getApiParamsToDuplicateExistingRelationshipsToNewClient(contactPromptResult, apiCalls);

      $scope.refresh(apiCalls);
    }

    /**
     * Sends the api calls necessary to assign the given contact as a case role. This
     * event is also recorded as an activity.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     */
    function handleAssignRole (contactPromptResult) {
      var apiCalls = getCreateCaseRoleApiCalls(contactPromptResult);

      $scope.refresh(apiCalls);
    }

    /**
     * Sends the api calls necessary to replace the given case role or case client. This
     * event is also recorded as activity.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     */
    function handleReplaceRoleOrClient (contactPromptResult) {
      var isReplacingClient = !contactPromptResult.role.relationship_type_id;
      var apiCalls = isReplacingClient
        ? getReplaceClientApiCalls(contactPromptResult)
        : getReplaceRoleApiCalls(contactPromptResult);

      $scope.refresh(apiCalls);
    }

    /**
     * Displays a confirmation dialog used to select a contact.
     * An optional description input can also be included in the confirmation dialog.
     *
     * @param {ContactPromptOptions} options the prompt options
     * @param {(contactPromptResult: ContactPromptResult) => void} onConfirmCallback a callback executed after confirming
     *   the dialog.
     */
    function promptForContact (options, onConfirmCallback) {
      options = _.assign({ roles: {} }, options);
      options.endDate = options.endDate || {};
      options.reassignmentDate = options.reassignmentDate || {};

      var model = {
        contact: { id: null },
        errorMessage: {
          contactSelection: null,
          endDate: null,
          reassignmentDate: null
        },
        description: null,
        removeDatePickerHrefs: removeDatePickerHrefs,
        role: options.role,
        showDescriptionField: options.showDescriptionField,
        hideContactField: options.hideContactField,
        showStartDate: !!options.startDate,
        startDate: options.startDate,
        endDate: {
          value: options.endDate.value,
          show: !!options.endDate.value,
          maxDate: moment().format('YYYY-MM-DD')
        },
        reassignmentDate: {
          value: options.reassignmentDate.value,
          show: !!options.reassignmentDate.value,
          maxDate: options.reassignmentDate.maxDate
        }
      };

      dialogService.open(
        'PromptForContactDialog',
        '~/civicase/case/details/people-tab/directives/contact-prompt-dialog.html',
        model,
        {
          title: options.title,
          width: '450px',
          buttons: [
            {
              text: ts('Continue'),
              icon: 'fa-check',
              click: handleContactSubmit
            }
          ]
        }
      );

      /**
       * Executes the confirmation callback function, if provided and closes
       * the contact prompt modal if no error messages are displayed.
       */
      function handleContactSubmit () {
        // CRM's Entity Ref directive only returns the contact ID, this returns
        // the full contact data.
        var contact = $('[ng-model="model.contact.id"]').select2('data');

        if (!onConfirmCallback) {
          return;
        }

        onConfirmCallback({
          contact: contact,
          description: model.description,
          role: options.role,
          showErrorMessageFor: showErrorMessageFor,
          startDate: model.startDate,
          endDate: model.endDate.value,
          reassignmentDate: model.reassignmentDate.value
        });

        if (_.every(_.values(model.errorMessage), function (value) { return !value; })) {
          dialogService.close('PromptForContactDialog');
        }
      }

      /**
       * Displays the given error message under the given input.
       *
       * @param {string} errorObjectName the error object name which needs to be updated
       * @param {string} message the error message to display under the contact selection.
       */
      function showErrorMessageFor (errorObjectName, message) {
        model.errorMessage[errorObjectName] = message;
      }
    }

    /**
     * Prompts the user to select a contact, but rejects it if the selected contact is already a case client
     * and displays an error message. Otherwise it executes the confirmation handler as normal.
     *
     * @param {ContactPromptOptions} promptOptions the options to pass to the contact prompt.
     * @param {(contactPromptResult: ContactPromptResult) => void} contactSelectedHandler the handler to use when a contact is selected.
     */
    function promptForContactThatIsNotCaseClient (promptOptions, contactSelectedHandler) {
      promptForContact(promptOptions, function (contactPromptResult) {
        var isError = false;

        if (!contactPromptResult.contact) {
          contactPromptResult.showErrorMessageFor(
            'contactSelection',
            PeoplesTabMessageConstants.CONTACT_NOT_SELECTED_MESSAGE
          );
          isError = true;
        } else if (checkContactIsClient(contactPromptResult.contact.id)) {
          contactPromptResult.showErrorMessageFor(
            'contactSelection',
            PeoplesTabMessageConstants.CONTACT_CANT_HAVE_ROLE_MESSAGE
          );

          isError = true;
        }

        if (
          contactPromptResult.reassignmentDate &&
          !isSameOrAfter(
            contactPromptResult.reassignmentDate,
            contactPromptResult.role.relationship.start_date
          )
        ) {
          contactPromptResult.showErrorMessageFor(
            'reassignmentDate',
            PeoplesTabMessageConstants.RELATIONSHIP_REASSIGNMENT_DATE_MESSAGE
          );
          isError = true;
        }

        if (isError) {
          return;
        }

        contactSelectedHandler(contactPromptResult);
      });
    }

    /**
     * Unassign client
     *
     * @param {object} role role
     * @returns {Array} API call
     */
    function unassignClientCall (role) {
      return ['CaseContact', 'get', {
        case_id: item.id,
        contact_id: role.contact_id,
        'api.CaseContact.delete': {}
      }];
    }

    /**
     * Unassign role
     *
     * @param {object} role role
     * @param {object} endDate end date for the relationship
     * @returns {Array} API call
     */
    function unassignRoleCall (role, endDate) {
      return ['Relationship', 'get', {
        relationship_type_id: role.relationship_type_id,
        contact_id_b: role.contact_id,
        case_id: item.id,
        is_active: 1,
        'api.Relationship.create': {
          end_date: endDate,
          is_active: 0
        }
      }];
    }

    /**
     * Updates the case relationship list
     */
    function getRelations () {
      var params = {
        options: { limit: 25, offset: $scope.relationsPage - 1 },
        case_id: item.id,
        sequential: 1,
        return: ['display_name', 'phone', 'email']
      };
      if ($scope.relationsAlphaFilter) {
        params.display_name = $scope.relationsAlphaFilter;
      }
      civicaseCrmApi('Case', 'getrelations', params).then(function (contacts) {
        $scope.relations = _.each(contacts.values, function (rel) {
          var relType = relTypes[rel.relationship_type_id];
          rel.relation = relType['label_' + rel.relationship_direction];
          rel.client = clients[rel.client_contact_id].display_name;
        });
        $scope.relationsCount = contacts.count;
        $scope.isRelationshipLoading = false;
      });
    }
  }
})(angular, CRM.$, CRM._);
