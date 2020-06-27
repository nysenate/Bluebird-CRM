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
   * ViewPeople Controller
   *
   * @param {object} $q $q
   * @param {object} $scope $scope
   * @param {object} allowMultipleCaseClients allow multiple clients configuration value
   * @param {object} crmApi crmApi
   * @param {object} DateHelper DateHelper
   * @param {object} ts ts
   * @param {object} RelationshipType RelationshipType
   */
  function civicaseViewPeopleController ($q, $scope, allowMultipleCaseClients, crmApi, DateHelper,
    ts, RelationshipType) {
    // The ts() and hs() functions help load strings for this module.
    var CONTACT_CANT_HAVE_ROLE_MESSAGE = ts('Case clients cannot be selected for a case role. Please select another contact.');
    var clients = _.indexBy($scope.item.client, 'contact_id');
    var item = $scope.item;
    var relTypes = RelationshipType.getAll();

    $scope.ts = ts;
    $scope.allowMultipleCaseClients = allowMultipleCaseClients;
    $scope.roles = [];
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
    $scope.letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
    $scope.contactTasks = CRM.civicase.contactTasks;
    $scope.ceil = Math.ceil;
    $scope.allRoles = [];
    $scope.isRolesLoading = true;
    $scope.isRelationshipLoading = true;

    $scope.formatDate = DateHelper.formatDate;
    $scope.getRelations = getRelations;

    (function init () {
      $scope.$bindToRoute({ expr: 'tab', param: 'peopleTab', format: 'raw', default: 'roles' });
      $scope.$watch('item', getCaseRoles, true);
      $scope.$watch('item.definition', function () {
        if ($scope.item && $scope.item.definition && $scope.item.definition.caseRoles) {
          $scope.allRoles = _.each(_.cloneDeep($scope.item.definition.caseRoles), formatRole);
        }
      });
      $scope.$watch('rolesFilter', getCaseRoles);
      $scope.$watch('tab', function (tab) {
        if (tab === 'relations' && !$scope.relations.length) {
          getRelations();
        }
      });
    }());

    /**
     * Get selected contacts from the selection bar
     *
     * @param {string} tab tab
     * @param {boolean} onlyChecked onlyChecked
     * @returns {Array} selected contact
     */
    $scope.getSelectedContacts = function (tab, onlyChecked) {
      var idField = (tab === 'roles' ? 'contact_id' : 'id');
      if (onlyChecked || $scope[tab + 'SelectionMode'] === 'checked') {
        return _.collect(_.filter($scope[tab], { checked: true }), idField);
      } else if ($scope[tab + 'SelectionMode'] === 'all') {
        return _.collect(_.filter($scope[tab], function (el) {
          return el.contact_id;
        }), idField);
      }
      return [];
    };

    /**
     * On the contact tab all the records don't have some contact assigned
     * This filters the list with roles assigned to a contact.
     *
     * @param {Array} roles roles
     * @returns {number} count of roles
     */
    $scope.getCountOfRolesWithContacts = function (roles) {
      return _.filter(roles, function (role) {
        return role.contact_id;
      }).length;
    };

    /**
     * Sets selection mode
     *
     * @param {string} mode mode
     * @param {string} tab tab
     */
    $scope.setSelectionMode = function (mode, tab) {
      $scope[tab + 'SelectionMode'] = mode;
    };

    /**
     * Sets selected tab
     *
     * @param {string} tab tab
     */
    $scope.setTab = function (tab) {
      $scope.tab = tab;
    };

    /**
     * Filters result on the basis of letter clicked
     *
     * @param {string} letter letter
     * @param {string} tab tab
     */
    $scope.setLetterFilter = function (letter, tab) {
      if ($scope[tab + 'AlphaFilter'] === letter) {
        $scope[tab + 'AlphaFilter'] = '';
      } else {
        $scope[tab + 'AlphaFilter'] = letter;
      }

      if (tab === 'roles') {
        getCaseRoles();
      } else {
        $scope.getRelations();
      }
    };

    /**
     * @param {string} key key of the bulk action
     */
    $scope.doBulkAction = function (key) {
      $scope.rolesSelectedTask = key;
      $scope.doContactTask('roles');
    };

    /**
     * Update the contacts with the task
     *
     * @param {string} tab tab
     */
    $scope.doContactTask = function (tab) {
      var task = $scope.contactTasks[$scope[tab + 'SelectedTask']];
      $scope[tab + 'SelectedTask'] = '';
      CRM.loadForm(CRM.url(task.url, { cids: $scope.getSelectedContacts(tab).join(',') }))
        .on('crmFormSuccess', $scope.refresh)
        .on('crmFormSuccess', function () {
          $scope.refresh();
          if (tab === 'relations') {
            $scope.getRelations();
          }
        });
    };

    /**
     * Prompts the user to create either a new role or client related to the case.
     *
     * @param {Role} role the role to assign to the case.
     */
    $scope.assignRoleOrClient = function (role) {
      var isAssigningRole = role && !!role.relationship_type_id;

      isAssigningRole
        ? assignRole(role)
        : assignClient();
    };

    /**
     * Replaces the given role or client with another one selected by the user.
     *
     * @param {Role} role the role to replace.
     */
    $scope.replaceRoleOrClient = function (role) {
      var isReplacingClient = !role.relationship_type_id;

      promptForContactThatIsNotCaseClient(
        {
          title: ts('Replace %1', { 1: role.role }),
          showDescriptionField: !isReplacingClient,
          role: role
        },
        handleReplaceRoleOrClient
      );
    };

    /**
     * Unassign the role to a contact
     *
     * @param {string} role role
     */
    $scope.unassignRole = function (role) {
      CRM.confirm({
        title: ts('Remove %1', { 1: role.role }),
        message: ts('Remove %1 as %2?', { 1: role.display_name, 2: role.role })
      }).on('crmConfirm:yes', function () {
        var apiCalls = [unassignRoleCall(role)];

        // when client
        if (!role.relationship_type_id) {
          getApiParamsToSetRelationshipsAsInactiveWhenClientIsRemoved(role, apiCalls);
        }

        apiCalls.push(['Activity', 'create', {
          case_id: item.id,
          target_contact_id: role.contact_id,
          status_id: 'Completed',
          activity_type_id: role.relationship_type_id ? 'Remove Case Role' : 'Remove Client From Case',
          subject: ts('%1 removed as %2', { 1: role.display_name, 2: role.role })
        }]);

        $scope.refresh(apiCalls);
      });
    };

    /**
     * Returns the parameters needed to create a completed activity related to the case.
     *
     * @param {object} extraParams extra parameters to pass to the activity creation call.
     * @returns {[string, string, object]} the create activity api call params.
     */
    function getCreateRoleActivityApiCall (extraParams) {
      return ['Activity', 'create', _.extend({}, {
        case_id: item.id,
        status_id: 'Completed'
      }, extraParams)];
    }

    /**
     * Returns all the calls needed to create relationships between the selected contact and all the
     * clients related to the case.
     *
     * @param {ContactPromptResult} contactPromptResult the contact returned by the confirm dialog
     * @returns {Array[]} a list of api calls.
     */
    function getCreateCaseRoleApiCalls (contactPromptResult) {
      var params = {
        relationship_type_id: contactPromptResult.role.relationship_type_id,
        start_date: 'now',
        end_date: null,
        contact_id_b: contactPromptResult.contact.id,
        case_id: item.id,
        description: contactPromptResult.description
      };

      return _.map(item.client, function (client) {
        return ['Relationship', 'create', _.extend({ contact_id_a: client.contact_id }, params)];
      });
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
     * Prompts the user to select a contact to assign them to the case. Relationships between
     * the selected contact and the case clients are created using the provided role. This event is also
     * recorded as an activity related to the case.
     *
     * @param {Role} role the role details.
     */
    function assignRole (role) {
      promptForContactThatIsNotCaseClient(
        {
          title: ts('Add %1', { 1: role.role }),
          showDescriptionField: true,
          role: role
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
        getCreateRoleActivityApiCall({
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
      var activitySubject = getActivitySubjectForReplaceCaseContact(contactPromptResult);
      var apiCalls = [
        unassignRoleCall(contactPromptResult.role),
        getCreateRoleActivityApiCall({
          activity_type_id: 'Assign Case Role',
          subject: activitySubject,
          target_contact_id: [
            contactPromptResult.contact.id,
            contactPromptResult.role.contact_id
          ]
        })
      ];

      return apiCalls.concat(
        getCreateCaseRoleApiCalls(contactPromptResult)
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
        getCreateRoleActivityApiCall({
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
      var activitySubject = ts('%1 added as %2', {
        1: contactPromptResult.contact.extra.display_name,
        2: contactPromptResult.role.role
      });
      var apiCalls = [
        getCreateRoleActivityApiCall({
          activity_type_id: 'Assign Case Role',
          subject: activitySubject,
          target_contact_id: contactPromptResult.contact.id
        })
      ].concat(
        getCreateCaseRoleApiCalls(contactPromptResult)
      );

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
     * Displays a confirmation dialog used to select a contact. An optional description input can also be
included in the confirmation dialog.
     *
     * @param {ContactPromptOptions} options the prompt options
     * @param {(contactPromptResult: ContactPromptResult) => void} onConfirmCallback a callback executed after confirming
     *   the dialog.
     */
    function promptForContact (options, onConfirmCallback) {
      options = _.assign({ roles: {} }, options);

      var modalBody = '<input name="caseRoleSelector" placeholder="' + ts('Select Contact') + '" />';

      if (options.showDescriptionField) {
        modalBody += '<br/><textarea rows="3" cols="35" name="description" class="crm-form-textarea" style="margin-top: 10px;padding-left: 10px;border-color: #C2CFDE;color: #9494A4;" placeholder="Description"></textarea>';
      }

      var dialogElement = CRM.confirm({
        title: options.title,
        message: modalBody,
        open: function () {
          $('[name=caseRoleSelector]', this).crmEntityRef({
            create: true,
            api: {
              extra: ['display_name'],
              params: {
                contact_type: options.role.contact_type,
                contact_sub_type: options.role.contact_sub_type
              }
            }
          });
        }
      })
        .on('crmConfirm:yes', function (event) {
          if (!onConfirmCallback) {
            return;
          }

          var contact = $('[name=caseRoleSelector]', this).select2('data');
          var description = $('[name=description]', this).val();

          onConfirmCallback({
            contact: contact,
            description: description,
            event: event,
            role: options.role,
            showContactSelectionError: showContactSelectionError
          });
        });

      /**
       * Displays the given error message under the contact selection input.
       *
       * @param {string} message the error message to display under the contact selection.
       */
      function showContactSelectionError (message) {
        var contactSelector = $('[name=caseRoleSelector]', dialogElement).select2('container');
        var hasError = dialogElement.find('.contact-selection-error').length > 0;

        if (hasError) {
          return;
        }

        contactSelector.addClass('crm-error');
        $('<div class="contact-selection-error crm-error crm-error-label"></div>')
          .text(message)
          .prepend('<i class="fa fa-times"></i>')
          .css('max-width', contactSelector.width())
          .insertAfter(contactSelector);
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
        if (checkContactIsClient(contactPromptResult.contact.id)) {
          contactPromptResult.event.preventDefault();
          contactPromptResult
            .showContactSelectionError(CONTACT_CANT_HAVE_ROLE_MESSAGE);

          return;
        }

        contactSelectedHandler(contactPromptResult);
      });
    }

    /**
     * Unassign role
     *
     * @param {object} role role
     * @returns {Array} API call
     */
    function unassignRoleCall (role) {
      // Case Role
      if (role.relationship_type_id) {
        return ['Relationship', 'get', {
          relationship_type_id: role.relationship_type_id,
          contact_id_b: role.contact_id,
          case_id: item.id,
          is_active: 1,
          'api.Relationship.create': { is_active: 0, end_date: 'now' }
        }];
      } else { // Case Client
        return ['CaseContact', 'get', {
          case_id: item.id,
          contact_id: role.contact_id,
          'api.CaseContact.delete': {}
        }];
      }
    }

    /**
     * Formats the role in required format
     *
     * @param {object} role role
     */
    function formatRole (role) {
      var relType = _.find(relTypes, function (relation) {
        return relation.name_a_b === role.name || relation.name_b_a === role.name;
      });
      role.role = relType.label_b_a;
      role.contact_type = relType.contact_type_b;
      role.contact_sub_type = relType.contact_sub_type_b;
      role.description = (role.manager === '1' ? (ts('Case Manager.') + ' ') : '') + (relType.description || '');
      role.relationship_type_id = relType.id;
    }

    if ($scope.item && $scope.item.definition && $scope.item.definition.caseRoles) {
      $scope.allRoles = _.each(_.cloneDeep($scope.item.definition.caseRoles), formatRole);
    }
    /**
     * Updates the case roles list
     */
    function getCaseRoles () {
      var caseRoles, selected;
      var relDesc = [];
      var allRoles = [];
      if ($scope.item && $scope.item.definition && $scope.item.definition.caseRoles) {
        allRoles = _.each(_.cloneDeep($scope.item.definition.caseRoles), formatRole);
        // Case Roles loading completes after all Roles are fetched
        $scope.isRolesLoading = false;
      }
      caseRoles = $scope.rolesAlphaFilter ? [] : _.cloneDeep(allRoles);
      selected = $scope.getSelectedContacts('roles', true);
      if ($scope.rolesFilter) {
        caseRoles = $scope.rolesFilter === 'client' ? [] : [_.findWhere(caseRoles, { name: $scope.rolesFilter })];
      }

      // get relationship descriptions
      if (item['api.Relationship.get']) {
        _.each(item['api.Relationship.get'].values, function (relationship) {
          relDesc[relationship.contact_id_b + '_' + relationship.relationship_type_id] = relationship.description ? relationship.description : '';
          relDesc[relationship.contact_id_b + '_' + relationship.relationship_type_id + '_date'] = relationship.start_date;
        });
      }
      // get clients from the contacts
      _.each(item.contacts, function (contact) {
        var role = contact.relationship_type_id ? _.findWhere(caseRoles, { relationship_type_id: contact.relationship_type_id }) : null;
        if ((!role || role.contact_id) && contact.relationship_type_id) {
          role = _.cloneDeep(_.findWhere(allRoles, { relationship_type_id: contact.relationship_type_id }));
          if (!$scope.rolesFilter || role.name === $scope.rolesFilter) {
            caseRoles.push(role);
          }
        }
        // Apply filters
        if ($scope.rolesAlphaFilter && contact.display_name.toUpperCase().indexOf($scope.rolesAlphaFilter) < 0) {
          if (role) _.pull(caseRoles, role);
        } else if (role) {
          if (!$scope.rolesFilter || role.name === $scope.rolesFilter) {
            $.extend(role, { checked: selected.indexOf(contact.contact_id) >= 0 }, contact);
          }
        } else if (!$scope.rolesFilter || $scope.rolesFilter === 'client') {
          var isChecked = selected.indexOf(contact.contact_id) >= 0;
          var caseRole = $.extend({}, contact, {
            role: ts('Client'),
            checked: isChecked
          });

          caseRoles.push(caseRole);
        }
      });

      // Set description and start date for no clients case roles
      _.each(caseRoles, function (role, index) {
        if (role && role.role !== 'Client' && (role.contact_id + '_' + role.relationship_type_id in relDesc)) {
          caseRoles[index].desc = relDesc[role.contact_id + '_' + role.relationship_type_id];
          caseRoles[index].start_date = relDesc[role.contact_id + '_' + role.relationship_type_id + '_date'];
        }
      });
      $scope.rolesCount = caseRoles.length;
      // Apply pager
      if ($scope.rolesCount <= (25 * ($scope.rolesPage - 1))) {
        // Reset if out of range
        $scope.rolesPage = 1;
      }
      $scope.roles = _.slice(caseRoles, (25 * ($scope.rolesPage - 1)), 25 * $scope.rolesPage);
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
      crmApi('Case', 'getrelations', params).then(function (contacts) {
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
