(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.directive('civicaseTagsSelector', function () {
    return {
      restrict: 'E',
      templateUrl: '~/civicase-base/directives/tags-selector.directive.html',
      scope: {
        model: '=',
        allTags: '='
      },
      controller: civicaseTagsSelectorController
    };
  });

  module.controller('civicaseTagsSelectorController', civicaseTagsSelectorController);

  /**
   * @param {object} $scope the controller's scope
   * @param {object} Select2Utils select 2 utility service
   * @param {Function} isTruthy service to check if value is truthy
   */
  function civicaseTagsSelectorController ($scope, Select2Utils, isTruthy) {
    $scope.formatTags = formatTags;
    $scope.tags = {
      genericTags: '',
      tagSets: {}
    };

    (function init () {
      $scope.genericTags = prepareGenericTags($scope.allTags);
      $scope.tagSets = prepareTagSetsTree($scope.allTags);

      if ($scope.model.length > 0) {
        $scope.tags = prepareTagsForEditing($scope.model);
      }

      $scope.$watch('tags', function (tags) {
        $scope.model = prepareTagsForSave(tags);
      }, true);

      $scope.$watch('model', function (model) {
        $scope.tags = prepareTagsForEditing(model);
      }, true);
    }());

    /**
     * Format Tags to add indentation
     *
     * @param {object} item tag object
     * @returns {string} markup for the tags
     */
    function formatTags (item) {
      var tagColorElement = '<span class="crm-select-item-color civicase__tags-selector__item-color" style="background-color: ' + item.color + '"></span>';

      return '<span style="margin-left:' + (item.indentationLevel * 4) + 'px">' + tagColorElement + item.text + '</span>';
    }

    /**
     * Recursive function to prepare the generic tags
     *
     * @param {Array} tags tags
     * @param {string} parentID id of the parent tag
     * @param {number} level level of tag
     * @returns {Array} tags list
     */
    function prepareGenericTags (tags, parentID, level) {
      var returnArray = [];

      level = typeof level !== 'undefined' ? level : 0;
      parentID = typeof parent !== 'undefined' ? parentID : undefined;

      var filteredTags = _.filter(tags, function (child) {
        return child.parent_id === parentID && !isTruthy(child.is_tagset);
      });

      if (_.isEmpty(filteredTags)) {
        return [];
      }

      _.each(filteredTags, function (tag) {
        tag.text = tag.name;
        returnArray.push(tag);
        tag.indentationLevel = level;
        returnArray = returnArray.concat(prepareGenericTags(tags, tag.id, level + 1));
      });

      return returnArray;
    }

    /**
     * Prepares the tag sets tree
     *
     * @param {Array} tags list of tags
     * @returns {Array} tags tree
     */
    function prepareTagSetsTree (tags) {
      var returnArray = [];

      var filteredTags = _.filter(tags, function (child) {
        return !child.parent_id && isTruthy(child.is_tagset);
      });

      if (_.isEmpty(filteredTags)) {
        return [];
      }

      _.each(filteredTags, function (tag) {
        var children = _.filter(tags, function (child) {
          if (child.parent_id === tag.id && !isTruthy(child.is_tagset)) {
            child.text = child.name;
            return true;
          }
        });

        if (children.length > 0) {
          tag.children = children;
        }

        returnArray.push(tag);
      });

      return returnArray;
    }

    /**
     * Prepare Tags for Editing
     *
     * @param {Array} tags list of all tags
     * @returns {object} tags object
     */
    function prepareTagsForEditing (tags) {
      return {
        genericTags: getTagIDFromGivenList(tags, $scope.genericTags),
        tagSets: prepareTagSetsForEdit(tags, $scope.tagSets)
      };
    }

    /**
     * Searches the tag ids which belongs to the sent list of tags
     *
     * @param {Array} listOfTagIDs list of tag ids
     * @param {Array} tagsArrayToSearchFrom tags array to search from
     * @returns {Array} list of tag ids which are found in sent tags
     */
    function getTagIDFromGivenList (listOfTagIDs, tagsArrayToSearchFrom) {
      var tagIds = [];

      _.each(tagsArrayToSearchFrom, function (tag) {
        var tagID = _.find(listOfTagIDs, function (id) {
          return parseInt(id) === parseInt(tag.id);
        });

        if (tagID) {
          tagIds.push(tagID);
        }
      });

      return tagIds;
    }

    /**
     * Prepare Tags Sets for Editing
     *
     * @param {object} listOfTagIDs list of tag ids
     * @param {Array} tagsSetsToSearchFrom tags sets array to search from
     * @returns {object} tags object
     */
    function prepareTagSetsForEdit (listOfTagIDs, tagsSetsToSearchFrom) {
      var returnObj = {};

      _.each(tagsSetsToSearchFrom, function (tagSet) {
        returnObj[tagSet.id] = getTagIDFromGivenList(listOfTagIDs, tagSet.children);
      });

      return returnObj;
    }

    /**
     * Prepare Selected tags to be saved in the Backend
     *
     * @param {object} tags tags object
     * @returns {Array} list of tag ids
     */
    function prepareTagsForSave (tags) {
      var tagIds = Select2Utils.getSelect2Value(tags.genericTags);

      _.each(tags.tagSets, function (tagSet) {
        tagIds = tagIds.concat(Select2Utils.getSelect2Value(tagSet));
      });

      return tagIds;
    }
  }
})(angular, CRM.$, CRM._);
