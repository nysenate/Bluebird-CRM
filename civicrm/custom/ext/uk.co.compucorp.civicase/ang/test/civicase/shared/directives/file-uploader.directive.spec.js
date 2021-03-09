/* eslint-env jasmine */

(function (_) {
  describe('civicaseFileUploader', function () {
    var $q, $controller, $rootScope, $scope, $timeout, civicaseCrmApi,
      civicaseCrmApiMock, TagsMockData;

    beforeEach(module('civicase', 'civicase.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('FileUploader', function () {
        this.getNotUploadedItems = jasmine.createSpy('getNotUploadedItems');
        this.uploadAllWithPromise = jasmine.createSpy('uploadAllWithPromise');
      });
    }));

    beforeEach(inject(function (_$q_, _$controller_, _$rootScope_, _$timeout_,
      _civicaseCrmApi_, _TagsMockData_) {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
      civicaseCrmApi = _civicaseCrmApi_;
      TagsMockData = _TagsMockData_;
    }));

    describe('displaying tags', function () {
      beforeEach(function () {
        civicaseCrmApiMock.and.returnValue($q.resolve({ values: TagsMockData.get() }));
        initController();
      });

      it('inits the tags', () => {
        expect($scope.tags).toEqual({ all: [], selected: [] });
      });

      it('fetches all tags for associated with activity', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith('Tag', 'get', {
          sequential: 1,
          used_for: { LIKE: '%civicrm_activity%' },
          options: { limit: 0 }
        });
      });

      describe('after fetching the tags', () => {
        beforeEach(function () {
          $scope.$digest();
        });

        it('shows the tags on the ui', () => {
          expect($scope.tags.all).toEqual(TagsMockData.get());
        });
      });
    });

    describe('saving activity', function () {
      beforeEach(function () {
        civicaseCrmApiMock.and.callFake(function (entity, endpoint, params) {
          if (entity === 'Activity') {
            return $q.resolve({ id: '101' });
          } else if (entity === 'Tag') {
            return $q.resolve({ values: TagsMockData.get() });
          }
        });

        initController();

        $scope.block = jasmine.createSpy('block');
        $scope.tags.selected = ['102'];
        $scope.fileUploadForm = jasmine.createSpyObj(['$setPristine']);
        $scope.uploader = jasmine.createSpyObj([
          'clearQueue', 'getNotUploadedItems', 'uploadAllWithPromise']);

        $scope.saveActivity();
        $scope.$digest();
        $timeout.flush();
      });

      it('creates a new file type activity', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith('Activity', 'create', $scope.activity);
      });

      it('saves the selected tags with respect to the activity', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith('EntityTag', 'createByQuery', {
          entity_table: 'civicrm_activity',
          tag_id: ['102'],
          entity_id: '101'
        });
      });

      it('clears the upload queue', () => {
        expect($scope.uploader.clearQueue).toHaveBeenCalledWith();
      });

      it('sets the upload form as pristine', () => {
        expect($scope.fileUploadForm.$setPristine).toHaveBeenCalledWith();
      });
    });

    /**
     * Initialise controller
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.ctx = {};

      $controller('civicaseFilesUploaderController', {
        $scope: $scope
      });
    }
  });
})(CRM._);
