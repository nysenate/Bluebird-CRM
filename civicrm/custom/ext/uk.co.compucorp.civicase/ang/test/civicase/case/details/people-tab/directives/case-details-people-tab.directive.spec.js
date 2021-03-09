/* eslint-env jasmine */

describe('Case Details People Tab', () => {
  let $controller, $rootScope, $scope, CasesData, caseRoleSelectorContact,
    civicasePeopleTabRoles, civicaseRoleDatesUpdater, ContactsData,
    crmConfirmDialog, crmConfirmYesEvent, originalCrmConfirm, originalSelect2,
    dialogServiceMock, CaseTypesMockData, PeoplesTabMessageConstants;

  beforeEach(module('civicase', 'civicase.data', ($provide) => {
    dialogServiceMock = jasmine.createSpyObj('dialogService', ['open', 'close']);

    $provide.value('dialogService', dialogServiceMock);
  }));

  beforeEach(inject(function (_$controller_, _$q_, _$rootScope_,
    _CasesData_, _civicasePeopleTabRoles_, _civicaseRoleDatesUpdater_,
    _ContactsData_, _CaseTypesMockData_, _PeoplesTabMessageConstants_) {
    $controller = _$controller_;
    $rootScope = _$rootScope_;
    CasesData = _CasesData_;
    civicasePeopleTabRoles = _civicasePeopleTabRoles_;
    civicaseRoleDatesUpdater = _civicaseRoleDatesUpdater_;
    ContactsData = _ContactsData_;
    CaseTypesMockData = _CaseTypesMockData_;
    PeoplesTabMessageConstants = _PeoplesTabMessageConstants_;

    $scope = $rootScope.$new();
    $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');
    $scope.refresh = jasmine.createSpy('refresh');

    originalCrmConfirm = CRM.confirm;
    originalSelect2 = CRM.$.fn.select2;
    crmConfirmDialog = CRM.$('<div class="mock-crm-confirm-dialog"></div>');
    crmConfirmYesEvent = CRM.$.Event('crmConfirm:yes');
    crmConfirmYesEvent.preventDefault = jasmine.createSpy('preventDefault');
    CRM.confirm = function (options) {
      crmConfirmDialog.append(options.message);
      if (options.open) {
        options.open();
      }

      return crmConfirmDialog;
    };
    CRM.$.fn.select2 = jasmine.createSpy('select2').and.callFake(function (option) {
      if (option === 'data') {
        return caseRoleSelectorContact;
      }
    });
  }));

  afterEach(() => {
    CRM.confirm = originalCrmConfirm;
    CRM.$.fn.select2 = originalSelect2;

    crmConfirmDialog.remove();
  });

  beforeEach(() => {
    const caseType = CaseTypesMockData.get()[1];
    $scope.item = CasesData.get().values[0];
    $scope.item.definition = caseType.definition;

    initController({
      $scope: $scope
    });
  });

  describe('on init', () => {
    it('stores a reference to the people tab roles service', () => {
      expect($scope.roles).toBe(civicasePeopleTabRoles);
    });

    it('stores a reference to the role dates updater service', () => {
      expect($scope.roleDatesUpdater).toBe(civicaseRoleDatesUpdater);
    });
  });

  describe('assigning roles', () => {
    const roleName = 'Service Provider';
    const roleDescription = 'Service Provider Role Description';
    let contact, previousContact, relationshipTypeId;

    beforeEach(() => {
      contact = CRM._.sample(ContactsData.values);
      relationshipTypeId = CRM._.uniqueId();
    });

    describe('when assigning a new role', () => {
      beforeEach(() => {
        $scope.assignRoleOrClient({
          relationship_type_id: relationshipTypeId,
          role: roleName,
          start_date: moment().add(5, 'day')
        });
        selectDialogContact(contact);
        updateDialogModel({
          description: roleDescription
        });
        submitDialog();
        $rootScope.$digest();
      });

      it('creates a new relationship between the case client and the selected contact using the given role', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Relationship', 'create', {
            relationship_type_id: relationshipTypeId,
            start_date: getDialogModel().startDate,
            end_date: null,
            contact_id_b: contact.contact_id,
            case_id: $scope.item.id,
            description: roleDescription,
            contact_id_a: $scope.item.client[0].contact_id
          }]
        ]));
      });

      it('closes the contact selection dialog', () => {
        expect(dialogServiceMock.close).toHaveBeenCalled();
      });
    });

    describe('when replacing a role', () => {
      describe('when reassignment date is before start date', () => {
        beforeEach(() => {
          previousContact = CRM._.sample(ContactsData.values);

          $scope.replaceRoleOrClient({
            contact_id: previousContact.contact_id,
            display_name: previousContact.display_name,
            relationship_type_id: relationshipTypeId,
            role: roleName,
            id: '101',
            relationship: {
              start_date: moment().add(5, 'day')
            }
          });
          selectDialogContact(contact);
          updateDialogModel({
            description: roleDescription
          });
          submitDialog();
          $rootScope.$digest();
        });

        it('does not creates a new relationship', () => {
          expect($scope.refresh).not.toHaveBeenCalled();
        });

        it('does not close the contact selection dialog', () => {
          expect(dialogServiceMock.close).not.toHaveBeenCalled();
        });
      });

      describe('when reassignment date is not before start date', () => {
        beforeEach(() => {
          previousContact = CRM._.sample(ContactsData.values);

          $scope.replaceRoleOrClient({
            contact_id: previousContact.contact_id,
            display_name: previousContact.display_name,
            relationship_type_id: relationshipTypeId,
            role: roleName,
            id: '101',
            relationship: {
              start_date: moment().add(-1, 'day')
            }
          });
          selectDialogContact(contact);
          updateDialogModel({
            description: roleDescription
          });
          submitDialog();
          $rootScope.$digest();
        });

        it('creates a new relationship between the case client and the selected contact using the given role', () => {
          expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
            ['Relationship', 'get', {
              case_id: $scope.item.id,
              contact_id_b: previousContact.contact_id,
              is_active: 1,
              relationship_type_id: relationshipTypeId,
              'api.Relationship.create': {
                id: false,
                relationship_type_id: relationshipTypeId,
                start_date: getDialogModel().reassignmentDate.value,
                end_date: null,
                contact_id_a: $scope.item.client[0].contact_id,
                contact_id_b: contact.contact_id,
                case_id: $scope.item.id,
                description: roleDescription,
                reassign_rel_id: '$value.id'
              }
            }]
          ]));
        });

        it('closes the contact selection dialog', () => {
          expect(dialogServiceMock.close).toHaveBeenCalled();
        });
      });
    });

    describe('when assigning a role to a client', () => {
      beforeEach(() => {
        var client = CRM._.first($scope.item.client);
        $scope.assignRoleOrClient({
          relationship_type_id: relationshipTypeId,
          role: roleName
        });
        selectDialogContact(CRM._.assign({}, client, {
          id: client.contact_id
        }));
        updateDialogModel({
          description: roleDescription
        });
        submitDialog();
        $rootScope.$digest();
      });

      it('displays an error message', () => {
        expect(getDialogModel().errorMessage.contactSelection)
          .toBe(PeoplesTabMessageConstants.CONTACT_CANT_HAVE_ROLE_MESSAGE);
      });

      it('does not make api requests', () => {
        expect($scope.refresh).not.toHaveBeenCalled();
      });

      it('does not close the contact selection dialog', () => {
        expect(dialogServiceMock.close).not.toHaveBeenCalled();
      });
    });

    describe('when replacing a role with a client', () => {
      beforeEach(() => {
        var client = CRM._.first($scope.item.client);
        $scope.replaceRoleOrClient({
          relationship_type_id: relationshipTypeId,
          role: roleName,
          relationship: {
            start_date: moment().format('YYYY-MM-DD')
          }
        });
        selectDialogContact(CRM._.assign({}, client, {
          id: client.contact_id
        }));
        updateDialogModel({
          description: roleDescription
        });
        submitDialog();
        $rootScope.$digest();
      });

      it('displays an error message', () => {
        expect(getDialogModel().errorMessage.contactSelection)
          .toBe(PeoplesTabMessageConstants.CONTACT_CANT_HAVE_ROLE_MESSAGE);
      });

      it('does not make api requests', () => {
        expect($scope.refresh).not.toHaveBeenCalled();
      });

      it('does not close the contact selection dialog', () => {
        expect(dialogServiceMock.close).not.toHaveBeenCalled();
      });
    });

    describe('when not selecting a contact', () => {
      beforeEach(() => {
        $scope.assignRoleOrClient({
          relationship_type_id: relationshipTypeId,
          role: roleName
        });
        CRM.$.fn.select2.and.returnValue();
        updateDialogModel({
          description: roleDescription
        });
        submitDialog();
        $rootScope.$digest();
      });

      it('displays an error message', () => {
        expect(getDialogModel().errorMessage.contactSelection)
          .toBe(PeoplesTabMessageConstants.CONTACT_NOT_SELECTED_MESSAGE);
      });

      it('does not make api requests', () => {
        expect($scope.refresh).not.toHaveBeenCalled();
      });

      it('does not close the contact selection dialog', () => {
        expect(dialogServiceMock.close).not.toHaveBeenCalled();
      });
    });

    describe('when adding a new case client', () => {
      beforeEach(() => {
        $scope.assignRoleOrClient({
          role: 'Client'
        });
        selectDialogContact(contact);
        submitDialog();
        $rootScope.$digest();
      });

      it('creates a new client using the selected contact', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['CaseContact', 'create', {
            case_id: $scope.item.id,
            contact_id: contact.contact_id
          }]
        ]));
      });

      it('creates a new completed activity to record the contact being assigned as a client to the case', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Activity', 'create', {
            case_id: $scope.item.id,
            target_contact_id: contact.contact_id,
            status_id: 'Completed',
            activity_type_id: 'Add Client To Case',
            subject: `${contact.display_name} added as Client`
          }]
        ]));
      });

      it('duplicates all existing relations for current case for the new client', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Relationship', 'get', {
            case_id: $scope.item.id,
            contact_id_a: $scope.item.client[0].contact_id,
            is_active: 1,
            'api.Relationship.create': {
              id: false,
              contact_id_a: contact.contact_id,
              start_date: 'now',
              contact_id_b: '$value.contact_id_b',
              relationship_type_id: '$value.relationship_type_id',
              description: '$value.description',
              case_id: '$value.case_id'
            }
          }]
        ]));
      });

      it('closes the contact selection dialog', () => {
        expect(dialogServiceMock.close).toHaveBeenCalled();
      });
    });

    describe('when replacing the case client', () => {
      beforeEach(() => {
        previousContact = CRM._.sample(ContactsData.values);

        $scope.replaceRoleOrClient({
          contact_id: previousContact.contact_id,
          display_name: previousContact.display_name,
          role: 'Client'
        }, true);
        selectDialogContact(contact);
        submitDialog();
        $rootScope.$digest();
      });

      it('does not show the reassignment datepicker', () => {
        expect(dialogServiceMock.open).toHaveBeenCalledWith(
          'PromptForContactDialog',
          '~/civicase/case/details/people-tab/directives/contact-prompt-dialog.html',
          jasmine.objectContaining({
            reassignmentDate: {
              value: undefined,
              show: false,
              maxDate: undefined
            }
          }),
          jasmine.any(Object)
        );
      });

      it('replaces the old client with the new selected contact', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['CaseContact', 'get', {
            case_id: $scope.item.id,
            contact_id: previousContact.contact_id,
            'api.CaseContact.create': {
              case_id: $scope.item.id,
              contact_id: parseInt(contact.contact_id)
            }
          }]
        ]));
      });

      it('updates all existing relationships for the old contact with the new client', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Relationship', 'get', {
            case_id: $scope.item.id,
            is_active: true,
            contact_id_a: previousContact.contact_id,
            'api.Relationship.update': { contact_id_a: contact.contact_id }
          }]
        ]));
      });

      it('creates a new completed activity to record the case being reassigned to another client', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Activity', 'create', {
            case_id: $scope.item.id,
            target_contact_id: jasmine.arrayContaining([
              previousContact.contact_id,
              contact.contact_id
            ]),
            status_id: 'Completed',
            activity_type_id: 'Reassigned Case',
            subject: `${contact.display_name} replaced ${previousContact.display_name} as Client`
          }]
        ]));
      });

      it('closes the contact selection dialog', () => {
        expect(dialogServiceMock.close).toHaveBeenCalled();
      });
    });

    describe('when unassigning a client', () => {
      let sampleContact;

      beforeEach(() => {
        sampleContact = CRM._.sample(ContactsData.values);

        $scope.unassignRole({
          contact_id: sampleContact.contact_id,
          display_name: sampleContact.display_name,
          role: 'Client'
        });

        crmConfirmDialog.trigger(crmConfirmYesEvent);
        $rootScope.$digest();
      });

      it('deletes the client', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([['CaseContact', 'get', {
          case_id: $scope.item.id,
          contact_id: sampleContact.contact_id,
          'api.CaseContact.delete': {}
        }]]));
      });

      it('makes the exisiting relationships with the client as inactive', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([['Relationship', 'get', {
          case_id: $scope.item.id,
          is_active: 1,
          contact_id_a: sampleContact.contact_id,
          'api.Relationship.create': { is_active: 0, end_date: 'now' }
        }]]));
      });

      it('creates an activity about removing the client', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([['Activity', 'create', {
          case_id: $scope.item.id,
          target_contact_id: sampleContact.contact_id,
          status_id: 'Completed',
          activity_type_id: 'Remove Client From Case',
          subject: sampleContact.display_name + ' removed as Client'
        }]]));
      });
    });

    describe('when unassigning a role', () => {
      let sampleContact, relationshipTypeId;

      describe('when start date is after role end date', () => {
        beforeEach(() => {
          sampleContact = CRM._.sample(ContactsData.values);
          relationshipTypeId = CRM._.uniqueId();

          $scope.unassignRole({
            contact_id: sampleContact.contact_id,
            display_name: sampleContact.display_name,
            relationship_type_id: relationshipTypeId,
            role: 'Role',
            relationship: {
              start_date: moment().add(5, 'day')
            }
          });

          updateDialogModel({
            description: roleDescription
          });
          submitDialog();

          $rootScope.$digest();
        });

        it('does not make an api call to update relationship', () => {
          expect($scope.refresh).not.toHaveBeenCalled();
        });
      });

      describe('when start date is before role end date', () => {
        beforeEach(() => {
          sampleContact = CRM._.sample(ContactsData.values);
          relationshipTypeId = CRM._.uniqueId();

          $scope.unassignRole({
            contact_id: sampleContact.contact_id,
            display_name: sampleContact.display_name,
            relationship_type_id: relationshipTypeId,
            role: 'Role',
            relationship: {
              start_date: moment().add(-5, 'day')
            }
          });

          updateDialogModel({
            description: roleDescription
          });
          submitDialog();

          $rootScope.$digest();
        });

        it('marks the current role relationship as finished using the active field', () => {
          expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
            ['Relationship', 'get', {
              relationship_type_id: relationshipTypeId,
              contact_id_b: sampleContact.contact_id,
              case_id: $scope.item.id,
              is_active: 1,
              'api.Relationship.create': {
                end_date: moment().format('YYYY-MM-DD'),
                is_active: 0
              }
            }]
          ]));
        });
      });

      describe('when reassining to a past date', () => {
        beforeEach(() => {
          sampleContact = CRM._.sample(ContactsData.values);
          relationshipTypeId = CRM._.uniqueId();

          $scope.unassignRole({
            contact_id: sampleContact.contact_id,
            display_name: sampleContact.display_name,
            relationship_type_id: relationshipTypeId,
            role: 'Role',
            relationship: {
              start_date: moment().add(-5, 'day')
            }
          });

          updateDialogModel({
            description: roleDescription,
            endDate: { value: moment().add(-3, 'day').format('YYYY-MM-DD') }
          });
          submitDialog();

          $rootScope.$digest();
        });

        it('marks the current role relationship as finished using the end date of the new relationship start date', () => {
          expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
            ['Relationship', 'get', {
              relationship_type_id: relationshipTypeId,
              contact_id_b: sampleContact.contact_id,
              case_id: $scope.item.id,
              is_active: 1,
              'api.Relationship.create': {
                end_date: moment().add(-3, 'day').format('YYYY-MM-DD'),
                is_active: 0
              }
            }]
          ]));
        });
      });
    });
  });

  describe('bulk action', () => {
    beforeEach(() => {
      spyOn($scope, 'doContactTask');

      $scope.doBulkAction('1');
    });

    it('performs the clicked bulk action', () => {
      expect($scope.rolesSelectedTask).toBe('1');
      expect($scope.doContactTask).toHaveBeenCalledWith('roles');
    });
  });

  /**
   * @returns {object} The dialog's model object data.
   */
  function getDialogModel () {
    return dialogServiceMock.open.calls.mostRecent().args[2];
  }

  /**
   * Initializes the controller.
   *
   * @param {object} dependencies the list of dependencies to pass to the controller.
   */
  function initController (dependencies) {
    $controller('civicaseViewPeopleController', dependencies);
  }

  /**
   * Makes the select2 function return the information for the given object.
   *
   * @param {object} contact contact's data.
   */
  function selectDialogContact (contact) {
    CRM.$.fn.select2.and.returnValue({
      id: contact.id,
      label: contact.sort_name,
      icon_class: contact.contact_type,
      description: [contact.email],
      extra: {
        display_name: contact.display_name
      }
    });
  }

  /**
   * Submits the active modal dialog by clicking on the first configured button.
   */
  function submitDialog () {
    dialogServiceMock.open.calls.mostRecent().args[3].buttons[0].click();
  }

  /**
   * Updates the dialog's model with the given data.
   *
   * @param {object} newModelData new data to append to the model.
   */
  function updateDialogModel (newModelData) {
    var currentModelData = getDialogModel();

    Object.assign(currentModelData, newModelData);
  }
});
