/* eslint-env jasmine */

((_) => {
  describe('workflow edit controller', () => {
    let $controller, $rootScope, $scope, $window, civicaseCrmUrl,
      CaseTypesMockData, CaseManagementWorkflow;

    beforeEach(module('workflow', 'civicase.data', ($provide) => {
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$controller_, _$rootScope_, _$window_,
      _CaseTypesMockData_, _CaseManagementWorkflow_, _civicaseCrmUrl_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      CaseTypesMockData = _CaseTypesMockData_;
      CaseManagementWorkflow = _CaseManagementWorkflow_;

      spyOn(CaseManagementWorkflow, 'getEditWorkflowURL');
    }));

    describe('when clicking on edit button', () => {
      var workflow;

      beforeEach(() => {
        workflow = CaseTypesMockData.getSequential()[0];

        civicaseCrmUrl.and.returnValue('crm url mock');
        CaseManagementWorkflow.getEditWorkflowURL.and.returnValue('mock/url');
        initController();
        $scope.clickHandler(workflow);
      });

      it('redirects to the case type page for the clicked workflow', () => {
        expect(CaseManagementWorkflow.getEditWorkflowURL).toHaveBeenCalledWith(workflow);
        expect(civicaseCrmUrl).toHaveBeenCalledWith('mock/url');
        expect($window.location.href).toBe('crm url mock');
      });
    });

    /**
     * Initializes the workflow epic controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('WorkflowEditController', { $scope: $scope });
    }
  });
})(CRM._);
