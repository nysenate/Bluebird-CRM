/* eslint-env jasmine */

describe('Case Details People Tab', () => {
  let $controller, $rootScope, $scope, CasesData, caseRoleSelectorContact,
    ContactsData, crmConfirmDialog, crmConfirmYesEvent, originalCrmConfirm,
    originalSelect2;
  const CONTACT_CANT_HAVE_ROLE_MESSAGE = 'Case clients cannot be selected for a case role. Please select another contact.';

  beforeEach(module('civicase', 'civicase.data'));

  beforeEach(inject(function (_$controller_, _$q_, _$rootScope_, _CasesData_, _ContactsData_) {
    $controller = _$controller_;
    $rootScope = _$rootScope_;
    CasesData = _CasesData_;
    ContactsData = _ContactsData_;

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
      } else if (option === 'container') {
        return crmConfirmDialog.find('[name=caseRoleSelector]');
      }
    });
  }));

  afterEach(() => {
    CRM.confirm = originalCrmConfirm;
    CRM.$.fn.select2 = originalSelect2;

    crmConfirmDialog.remove();
  });

  beforeEach(() => {
    $scope.item = CasesData.get().values[0];

    initController({
      $scope: $scope
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
          role: roleName
        });
        setRoleContact(contact);
        setRoleDescription(roleDescription);
        crmConfirmDialog.trigger(crmConfirmYesEvent);
        $rootScope.$digest();
      });

      it('creates a new relationship between the case client and the selected contact using the given role', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Relationship', 'create', {
            relationship_type_id: relationshipTypeId,
            start_date: 'now',
            end_date: null,
            contact_id_b: contact.contact_id,
            case_id: $scope.item.id,
            description: roleDescription,
            contact_id_a: $scope.item.client[0].contact_id
          }]
        ]));
      });

      it('creates a new completed activity to record the contact being assigned a role to the case', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Activity', 'create', {
            case_id: $scope.item.id,
            target_contact_id: contact.contact_id,
            status_id: 'Completed',
            activity_type_id: 'Assign Case Role',
            subject: `${contact.display_name} added as ${roleName}`
          }]
        ]));
      });

      it('closes the contact selection dialog', () => {
        expect(crmConfirmYesEvent.preventDefault).not.toHaveBeenCalled();
      });
    });

    describe('when replacing a role', () => {
      beforeEach(() => {
        previousContact = CRM._.sample(ContactsData.values);

        $scope.replaceRoleOrClient({
          contact_id: previousContact.contact_id,
          display_name: previousContact.display_name,
          relationship_type_id: relationshipTypeId,
          role: roleName
        });
        setRoleContact(contact);
        setRoleDescription(roleDescription);
        crmConfirmDialog.trigger(crmConfirmYesEvent);
        $rootScope.$digest();
      });

      it('marks the current role relationship as finished', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Relationship', 'get', {
            relationship_type_id: relationshipTypeId,
            contact_id_b: previousContact.contact_id,
            case_id: $scope.item.id,
            is_active: 1,
            'api.Relationship.create': {
              is_active: 0, end_date: 'now'
            }
          }]
        ]));
      });

      it('creates a new relationship between the case client and the selected contact using the given role', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Relationship', 'create', {
            relationship_type_id: relationshipTypeId,
            start_date: 'now',
            end_date: null,
            contact_id_b: contact.contact_id,
            case_id: $scope.item.id,
            description: roleDescription,
            contact_id_a: $scope.item.client[0].contact_id
          }]
        ]));
      });

      it('creates a new completed activity to record the case role has been reassigned', () => {
        expect($scope.refresh).toHaveBeenCalledWith(jasmine.arrayContaining([
          ['Activity', 'create', {
            case_id: $scope.item.id,
            target_contact_id: jasmine.arrayContaining([
              previousContact.contact_id,
              contact.contact_id
            ]),
            status_id: 'Completed',
            activity_type_id: 'Assign Case Role',
            subject: `${contact.display_name} replaced ${previousContact.display_name} as ${roleName}`
          }]
        ]));
      });

      it('closes the contact selection dialog', () => {
        expect(crmConfirmYesEvent.preventDefault).not.toHaveBeenCalled();
      });
    });

    describe('when assigning a role to a client', () => {
      beforeEach(() => {
        var client = CRM._.first($scope.item.client);
        $scope.assignRoleOrClient({
          relationship_type_id: relationshipTypeId,
          role: roleName
        });
        setRoleContact(CRM._.assign({}, client, {
          id: client.contact_id
        }));
        setRoleDescription(roleDescription);
        crmConfirmDialog.trigger(crmConfirmYesEvent);
        $rootScope.$digest();
      });

      it('displays an error message', () => {
        expect(crmConfirmDialog.text()).toContain(CONTACT_CANT_HAVE_ROLE_MESSAGE);
      });

      it('does not make api requests', () => {
        expect($scope.refresh).not.toHaveBeenCalled();
      });

      it('does not close the contact selection dialog', () => {
        expect(crmConfirmYesEvent.preventDefault).toHaveBeenCalled();
      });
    });

    describe('when replacing a role with a client', () => {
      beforeEach(() => {
        var client = CRM._.first($scope.item.client);
        $scope.replaceRoleOrClient({
          relationship_type_id: relationshipTypeId,
          role: roleName
        });
        setRoleContact(CRM._.assign({}, client, {
          id: client.contact_id
        }));
        setRoleDescription(roleDescription);
        crmConfirmDialog.trigger(crmConfirmYesEvent);
        $rootScope.$digest();
      });

      it('displays an error message', () => {
        expect(crmConfirmDialog.text()).toContain(CONTACT_CANT_HAVE_ROLE_MESSAGE);
      });

      it('does not make api requests', () => {
        expect($scope.refresh).not.toHaveBeenCalled();
      });

      it('does not close the contact selection dialog', () => {
        expect(crmConfirmYesEvent.preventDefault).toHaveBeenCalled();
      });
    });

    describe('when adding a new case client', () => {
      beforeEach(() => {
        $scope.assignRoleOrClient({
          role: 'Client'
        });
        setRoleContact(contact);
        crmConfirmDialog.trigger(crmConfirmYesEvent);
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
        expect(crmConfirmYesEvent.preventDefault).not.toHaveBeenCalled();
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
        setRoleContact(contact);
        crmConfirmDialog.trigger(crmConfirmYesEvent);
        $rootScope.$digest();
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
        expect(crmConfirmYesEvent.preventDefault).not.toHaveBeenCalled();
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
   * Initializes the controller.
   *
   * @param {object} dependencies the list of dependencies to pass to the controller.
   */
  function initController (dependencies) {
    $controller('civicaseViewPeopleController', dependencies);
  }

  /**
   * Sets the given contact as the selected value of the case role selector dropdown.
   *
   * @param {object} contact a contact.
   */
  function setRoleContact (contact) {
    CRM.$('[name=caseRoleSelector]', crmConfirmDialog).val(contact.id);
    caseRoleSelectorContact = {
      id: contact.id,
      text: contact.display_name,
      extra: contact
    };
  }

  /**
   * Sets the description for the role being created using the confirm dialog.
   *
   * @param {string} description a description for the role.
   */
  function setRoleDescription (description) {
    CRM.$('[name=description]', crmConfirmDialog).val(description);
  }
});
