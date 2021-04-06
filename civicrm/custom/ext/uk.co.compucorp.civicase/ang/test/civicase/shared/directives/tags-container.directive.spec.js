(() => {
  describe('civicaseTag', () => {
    let $controller, $rootScope, $scope, mockTags;

    beforeEach(module('civicase'));

    beforeEach(inject(function (_$controller_, _$rootScope_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;

      mockTags = {
        1: {
          'tag_id.name': 'mock name',
          'tag_id.description': 'mock description'
        },
        2: {
          'tag_id.name': 'mock name 2',
          'tag_id.description': 'mock description 2'
        }
      };
    }));

    describe('on init', () => {
      beforeEach(() => {
        initController(mockTags);
        $scope.$digest();
      });

      it('converts the tags object into an array', () => {
        expect($scope.tagsArray).toEqual(Object.values(mockTags));
      });
    });

    describe('when passing an empty object of tags', () => {
      beforeEach(() => {
        initController({});
        $scope.$digest();
      });

      it('sets the tags array as empty', () => {
        expect($scope.tagsArray).toEqual([]);
      });
    });

    /**
     * Initialise the controller
     *
     * @param {object} tags a list of tags to include in the scope.
     */
    function initController (tags) {
      $scope = $rootScope.$new();
      $scope.tags = tags;

      $controller('civicaseTagsContainerController', {
        $scope: $scope
      });
    }
  });
})();
