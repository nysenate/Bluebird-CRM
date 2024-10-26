describe('BulkActionsMessage', function () {
  var $compile, $rootScope, $scope, element;

  beforeEach(module('civicase', 'civicase.templates'));

  beforeEach(inject(function (_$compile_, _$rootScope_) {
    $compile = _$compile_;
    $rootScope = _$rootScope_;
    $scope = $rootScope.$new();
  }));

  beforeEach(function () {
    compileDirective();
  });

  describe('basic tests', function () {
    it('complies the BulkActionsCheckboxes directive', function () {
      expect(element.html()).toContain('div class="alert"');
    });
  });

  describe('if selectedItem is greater than 0 and showCheckboxes is true', function () {
    beforeEach(function () {
      element.isolateScope().showCheckboxes = true;
      element.isolateScope().selectedItems = 10;
      $scope.$digest();
    });
    it('do not hide the message box', function () {
      expect(element.find('.civicase__bulkactions-message').hasClass('ng-hide')).toBe(false);
    });
  });

  describe('if selectedItem is greater than 0 and showCheckboxes is false', function () {
    beforeEach(function () {
      element.isolateScope().showCheckboxes = false;
      element.isolateScope().selectedItems = 10;
      $scope.$digest();
    });
    it('hide the message box', function () {
      expect(element.find('.civicase__bulkactions-message').hasClass('ng-hide')).toBe(true);
    });
  });

  describe('if selectedItem is equal to 0 and showCheckboxes is true', function () {
    beforeEach(function () {
      element.isolateScope().showCheckboxes = true;
      element.isolateScope().selectedItems = 0;
      $scope.$digest();
    });
    it('hide the message box', function () {
      expect(element.find('.civicase__bulkactions-message').hasClass('ng-hide')).toBe(true);
    });
  });

  describe('if selectedItem is equal to 0 and showCheckboxes is false', function () {
    beforeEach(function () {
      element.isolateScope().showCheckboxes = false;
      element.isolateScope().selectedItems = 0;
      $scope.$digest();
    });
    it('hide the message box', function () {
      expect(element.find('.civicase__bulkactions-message').hasClass('ng-hide')).toBe(true);
    });
  });

  describe('totalCount is equal to selected items', function () {
    beforeEach(function () {
      element.isolateScope().totalCount = 20;
      element.isolateScope().selectedItems = 20;
      $scope.$digest();
    });

    it('does not hides clear All button', function () {
      expect(element.find('a[ng-click*="select(\'none\')"]').hasClass('ng-hide')).toBe(false);
    });

    it('hides Mark All selection button', function () {
      expect(element.find('a[ng-click*="select(\'all\')"]').hasClass('ng-hide')).toBe(true);
    });
  });

  describe('totalCount is not equal to selected items', function () {
    beforeEach(function () {
      element.isolateScope().totalCount = 20;
      element.isolateScope().selectedItems = 10;
      element.isolateScope().isSelectAll = false;
      $scope.$digest();
    });

    it('hides clear All button', function () {
      expect(element.find('a[ng-click*="select(\'none\')"]').hasClass('ng-hide')).toBe(true);
    });

    it('show Mark All selection button', function () {
      expect(element.find('a[ng-click*="select(\'all\')"]').hasClass('ng-hide')).toBe(false);
    });
  });

  /**
   * Function responsible for setting up compilation of the directive
   */
  function compileDirective () {
    $scope.selectedItems = 10;
    $scope.totalCount = 20;
    $scope.showCheckboxes = true;
    $scope.isSelectAll = false;
    element = $compile('<civicase-bulk-actions-message selected-items="selectedItems" total-count="totalCount" is-select-all="isSelectAll" show-checkboxes="showCheckboxes"></civicase-bulk-actions-message>')($scope);
    $scope.$digest();
  }
});
