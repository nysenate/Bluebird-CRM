/* eslint-env jasmine */

((_) => {
  describe('civicaseTagsSelector', () => {
    let $controller, TagsMockData, $rootScope, $scope;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((_$controller_, _$rootScope_, _TagsMockData_) => {
      $controller = _$controller_;
      TagsMockData = _TagsMockData_.get();

      $rootScope = _$rootScope_;

      $scope = $rootScope.$new();
    }));

    describe('when controller is initialised without any preselected values', () => {
      beforeEach(() => {
        initController();
      });

      it('shows blank tag dropdowns', () => {
        expect($scope.tags).toEqual({
          genericTags: '',
          tagSets: {}
        });
      });

      describe('generic tags', function () {
        describe('generic tags does not include tagsets', function () {
          var genericTagsHasTagSets;

          beforeEach(function () {
            genericTagsHasTagSets = false;

            $scope.genericTags.forEach(function (tag) {
              if (tag.is_tagset !== '0') {
                genericTagsHasTagSets = true;
              }
            });
          });

          it('does not include tagsets as generic tags', function () {
            expect(genericTagsHasTagSets).toBe(false);
          });
        });

        describe('child tags are indented in the UI', function () {
          var tagWithOneLevelOfParent, tagWithTwoLevelofParent;

          beforeEach(function () {
            tagWithOneLevelOfParent = _.find($scope.genericTags, function (tag) {
              return tag.name === 'L1';
            });
            tagWithTwoLevelofParent = _.find($scope.genericTags, function (tag) {
              return tag.name === 'L2';
            });
          });

          it('child tags are indented', function () {
            expect(tagWithOneLevelOfParent.indentationLevel).toBe(1);
            expect(tagWithTwoLevelofParent.indentationLevel).toBe(2);
          });
        });
      });

      describe('tagsets', function () {
        describe('tagsets does not include generic tags', function () {
          var tagSetsHasGenericTags;

          beforeEach(function () {
            tagSetsHasGenericTags = false;

            $scope.tagSets.forEach(function (tag) {
              if (tag.is_tagset === '0') {
                tagSetsHasGenericTags = true;
              }
            });
          });

          it('does not include generic tags as tagsets', function () {
            expect(tagSetsHasGenericTags).toBe(false);
          });
        });

        describe('each tag set has its child tags set as child', function () {
          var eachTagSetHasItsOwnChild = true;

          beforeEach(function () {
            _.each($scope.tagSets, function (parentTag) {
              _.each(parentTag.children, function (tag) {
                if (tag.parent_id !== parentTag.id) {
                  eachTagSetHasItsOwnChild = false;
                }
              });
            });
          });

          it('saves every child tag as its parent\'s child', function () {
            expect(eachTagSetHasItsOwnChild).toBe(true);
          });
        });
      });

      describe('when tags are selected on the UI', () => {
        beforeEach(() => {
          initController();

          $scope.tags = {
            genericTags: ['1', '12'],
            tagSets: {
              14: ['15']
            }
          };

          $rootScope.$digest();
        });

        it('prepares a list of selected tags in a falt structure which can be saved in backend', () => {
          expect($scope.model).toEqual(['1', '12', '15']);
        });
      });
    });

    describe('when controller is initialised with preselected values', () => {
      beforeEach(() => {
        initController(['1', '12', '15']);
      });

      it('shows the selected tags in the UI', () => {
        expect($scope.tags).toEqual({
          genericTags: ['1', '12'],
          tagSets: {
            6: [],
            14: ['15']
          }
        });
      });
    });

    describe('when tags are reselected', () => {
      beforeEach(() => {
        initController(['1', '12', '15']);
        $rootScope.$digest();
        $scope.model = ['1'];
        $rootScope.$digest();
      });

      it('resets the selected tags in the UI', () => {
        expect($scope.tags).toEqual({
          genericTags: ['1'],
          tagSets: {
            6: [],
            14: []
          }
        });
      });
    });

    describe('when displaying tags', () => {
      let tagMarkup;

      beforeEach(() => {
        initController();
        tagMarkup = $scope.formatTags({
          color: '#fff',
          text: 'Tag Name',
          indentationLevel: '1'
        });
      });

      it('returns all the case statuses', () => {
        expect(tagMarkup).toEqual('<span style="margin-left:4px"><span class="crm-select-item-color civicase__tags-selector__item-color" style="background-color: #fff"></span>Tag Name</span>');
      });
    });

    /**
     * Initializes the controller.
     *
     * @param {Array} model model for the tags
     */
    function initController (model) {
      $scope.model = model || [];
      $scope.allTags = TagsMockData;

      $controller('civicaseTagsSelectorController', {
        $scope: $scope
      });
    }
  });
})(CRM._);
