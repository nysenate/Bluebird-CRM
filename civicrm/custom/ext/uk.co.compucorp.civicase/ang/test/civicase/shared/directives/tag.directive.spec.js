/* eslint-env jasmine */

(function (colorContrast) {
  describe('civicaseTag', function () {
    var $controller, $rootScope, $scope, mockTag;

    beforeEach(module('civicase'));

    beforeEach(inject(function (_$controller_, _$rootScope_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;

      mockTag = {
        'tag_id.name': 'mock name',
        'tag_id.description': 'mock description'
      };
    }));

    describe('text colour', function () {
      describe('when the tag colour is dark', function () {
        beforeEach(function () {
          mockTag['tag_id.color'] = '#333333';

          initController(mockTag);
        });

        it('defines the text colour as white', function () {
          expect($scope.textColour).toBe('white');
        });
      });

      describe('when the tag colour is light', function () {
        beforeEach(function () {
          mockTag['tag_id.color'] = '#cccccc';

          initController(mockTag);
        });

        it('defines the text colour as black', function () {
          expect($scope.textColour).toBe('black');
        });
      });

      describe('when no colour is defined', function () {
        beforeEach(function () {
          initController(mockTag);
        });

        it('does not define a text colour', function () {
          expect($scope.textColour).toBeUndefined();
        });
      });
    });

    function initController (tag) {
      $scope = $rootScope.$new();
      $scope.tag = tag;

      $controller('civicaseTagController', {
        $scope: $scope
      });
    }
  });
})(CRM.utils.colorContrast);
