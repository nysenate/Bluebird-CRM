((_) => {
  describe('case management workflow', () => {
    let $q, $rootScope, $window, civicaseCrmApiMock, CaseTypesMockData,
      CaseManagementWorkflow;

    beforeEach(module('workflow.mock', 'workflow', 'civicase.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$q_, _$rootScope_, _$window_, _CaseManagementWorkflow_,
      _CaseTypesMockData_) => {
      $q = _$q_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      CaseManagementWorkflow = _CaseManagementWorkflow_;
      CaseTypesMockData = _CaseTypesMockData_;
    }));

    describe('when getting list of workflow', () => {
      var results;

      describe('when page in page 1', () => {
        beforeEach(() => {
          civicaseCrmApiMock.and.returnValue($q.resolve([
            { values: CaseTypesMockData.getSequential() },
            CaseTypesMockData.getSequential().length
          ]));

          CaseManagementWorkflow.getWorkflowsListForCaseOverview({ case_type_category: 'some_case_type_category' }, {
            size: 25,
            num: 1
          })
            .then(function (data) {
              results = data;
            });
          $rootScope.$digest();
        });

        it('fetches the first 25 workflows for the case management instance', () => {
          expect(civicaseCrmApiMock).toHaveBeenCalledWith([
            [
              'CaseType',
              'get', {
                sequential: 1,
                case_type_category: 'some_case_type_category',
                options: {
                  limit: 25,
                  offset: 0
                }
              }
            ],
            [
              'CaseType',
              'getcount', {
                case_type_category: 'some_case_type_category'
              }
            ]
          ]);
        });

        it('displays the list of fetched workflows and pagination', () => {
          expect(results).toEqual({
            values: CaseTypesMockData.getSequential(),
            count: CaseTypesMockData.getSequential().length
          });
        });
      });

      describe('when page in page 2', () => {
        beforeEach(() => {
          civicaseCrmApiMock.and.returnValue($q.resolve([
            { values: CaseTypesMockData.getSequential() },
            CaseTypesMockData.getSequential().length
          ]));

          CaseManagementWorkflow.getWorkflowsListForCaseOverview({ case_type_category: 'some_case_type_category' }, {
            size: 25,
            num: 2
          }).then(function (data) {
            results = data;
          });
          $rootScope.$digest();
        });

        it('fetches the workflows from 26th to 50th for the case management instance', () => {
          expect(civicaseCrmApiMock).toHaveBeenCalledWith([
            [
              'CaseType',
              'get', {
                sequential: 1,
                case_type_category: 'some_case_type_category',
                options: {
                  limit: 25,
                  offset: 25
                }
              }
            ],
            [
              'CaseType',
              'getcount', {
                case_type_category: 'some_case_type_category'
              }
            ]
          ]);
        });

        it('displays the list of fetched workflows and pagination', () => {
          expect(results).toEqual({
            values: CaseTypesMockData.getSequential(),
            count: CaseTypesMockData.getSequential().length
          });
        });
      });
    });

    describe('when duplicating a workflow', () => {
      var workflow;

      beforeEach(() => {
        workflow = CaseTypesMockData.getSequential()[0];
        CaseManagementWorkflow.createDuplicate(workflow);
      });

      it('creates a duplicate workflow', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith([
          ['CaseType', 'create', _.extend({}, workflow, { id: null })]
        ]);
      });
    });

    describe('when redirecting to the create workflow page', () => {
      beforeEach(() => {
        CaseManagementWorkflow.redirectToWorkflowCreationScreen();
      });

      it('redirects to the case management new workflow page', () => {
        expect($window.location.href).toBe('/civicrm/a/#/caseType/new');
      });
    });

    describe('when editing a workflow', () => {
      var returnValue;

      beforeEach(() => {
        var workflow = CaseTypesMockData.getSequential()[0];

        returnValue = CaseManagementWorkflow.getEditWorkflowURL(workflow);
      });

      it('redirects to the case type page for the clicked workflow', () => {
        expect(returnValue).toBe('civicrm/a/#/caseType/1');
      });
    });

    describe('when loading dashboard', () => {
      var returnValue;

      beforeEach(() => {
        returnValue = CaseManagementWorkflow.getActivityFilters();
      });

      it('shows the cases from active case types and non deleted contacts', () => {
        expect(returnValue).toEqual({
          case_filter: { 'case_type_id.is_active': 1, contact_is_deleted: 0 }
        });
      });
    });
  });
})(CRM._);
