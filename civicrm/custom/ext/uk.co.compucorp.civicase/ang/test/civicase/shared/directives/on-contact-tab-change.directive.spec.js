(($) => {
  describe('civicaseOnContactTabChange', () => {
    let $compile, $rootScope, $scope, element;
    const testUrl = '/civicrm/contact?cid=999&tab=test-tab';

    beforeEach(module('civicase'));

    beforeEach(inject((_$compile_, _$rootScope_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
    }));

    beforeEach(() => {
      $scope = $rootScope.$new();
      $scope.contactTabChangeSpy = jasmine.createSpy('contactTabChangeSpy');

      spyOn($scope, '$apply').and.callThrough();
      initDirective();
    });

    afterEach(() => {
      element.remove();
    });

    describe('when the contact tab changes', () => {
      beforeEach(() => {
        const uiTab = {
          newTab: element.find('li')
        };

        element.find('#mainTabContainer')
          .trigger('tabsactivate', uiTab);
      });

      it('calls the change handler function', () => {
        expect($scope.contactTabChangeSpy).toHaveBeenCalled();
      });

      it('passes the new tab URL', () => {
        expect($scope.contactTabChangeSpy).toHaveBeenCalledWith(jasmine.objectContaining({
          $tabUrl: testUrl
        }));
      });

      it('passes the parsed URL parameters of the new tab', () => {
        expect($scope.contactTabChangeSpy).toHaveBeenCalledWith(jasmine.objectContaining({
          $tabUrlParams: {
            cid: '999',
            tab: 'test-tab'
          }
        }));
      });
    });

    /**
     * Attaches the DOM elements needed to test the directive and initializes
     * the directive itself. The DOM elements are attached to the body of the DOM
     * so it needs to be removed on each cicle.
     */
    function initDirective () {
      element = CRM.$(`
        <div id="civicase-dom-test" class="page-civicrm-contact">
          <ul id="mainTabContainer">
            <li>
              <a href="${testUrl}">
                Test Tab
              </a>
            </li>
          </ul>
          <div
            civicase-on-contact-tab-change="contactTabChangeSpy({
              $tabUrl: $tabUrl,
              $tabUrlParams: $tabUrlParams
            })"
          ></div>
        </div>
      `);
      element.appendTo('body');

      $compile(element)($scope);
    }
  });
})(CRM.$);
