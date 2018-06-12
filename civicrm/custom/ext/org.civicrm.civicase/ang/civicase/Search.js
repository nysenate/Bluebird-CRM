(function(angular, $, _) {

  // Case search directive controller
  function searchController($scope, $location, $timeout) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('civicase');

    function mapSelectOptions(opt) {
      return {
        id: opt.value || opt.name,
        text: opt.label || opt.title,
        color: opt.color,
        icon: opt.icon
      };
    }

    var caseTypes = CRM.civicase.caseTypes,
      caseStatuses = CRM.civicase.caseStatuses;

    $scope.caseTypeOptions = _.map(caseTypes, mapSelectOptions);
    $scope.caseStatusOptions = _.map(caseStatuses, mapSelectOptions);
    $scope.customGroups = CRM.civicase.customSearchFields;
    $scope._ = _;
    $scope.checkPerm = CRM.checkPerm;

    $scope.filters = angular.extend({}, $scope.defaults);
    $scope.$watchCollection('filters', function(){
      if (!$scope.expanded) {
        $scope.doSearch();
      }
    });

    var $customScroll = $('.case-list-panel .custom-scroll-wrapper'),
      $tableHeader = $('.case-list-panel .inner table thead');

    $scope.$watch('expanded',function(){
      $timeout(function(){
        $($customScroll,$tableHeader).data('bs.affix').options.offset.top =  $('.case-list-panel').offset().top - 50;
      });
    });

    $scope.showMore = function() {
      $scope.expanded = true;
    };

    $scope.isEnabled = function(field) {
      return !$scope.hiddenFilters || !$scope.hiddenFilters[field];
    };

    $scope.setCaseManager = function() {
      $scope.filters.case_manager = $scope.caseManagerIsMe() ? null : [CRM.config.user_contact_id];
    };

    $scope.caseManagerIsMe = function() {
      return $scope.filters.case_manager && $scope.filters.case_manager.length === 1 && parseInt($scope.filters.case_manager[0], 10) === CRM.config.user_contact_id;
    };

    function formatSearchFilters(inp) {
      var search = {};
      _.each(inp, function(val, key) {
        if (!_.isEmpty(val) || (typeof val === 'number' && val) || typeof val === 'boolean' && val) {
          search[key] = val;
        }
      });
      return search;
    }
    $scope.doSearch = function() {
      $scope.filterDescription = buildDescription();
      $scope.expanded = false;
      $scope.$parent.$eval($scope.onSearch, {
        selectedFilters: formatSearchFilters($scope.filters)
      });
    };

    $scope.clearSearch = function() {
      $scope.filters = {};
      $scope.doSearch();
    };

    // Describe selected filters when collapsed
    var allSearchFields = {
      id: {
        label: ts('Case ID'),
        html_type: 'Number'
      },
      contact_id: {
        label: ts('Case Client')
      },
      case_manager: {
        label: ts('Case Manager')
      },
      start_date: {
        label: ts('Start Date')
      },
      end_date: {
        label: ts('End Date')
      },
      is_deleted: {
        label: ts('Deleted Cases')
      },
      tag_id: {
        label: ts('Tags')
      }
    };
    _.each(CRM.civicase.customSearchFields, function(group) {
      _.each(group.fields, function(field) {
        allSearchFields['custom_' + field.id] = field;
      });
    });
    function buildDescription() {
      var des = [];
      _.each($scope.filters, function(val, key) {
        var field = allSearchFields[key];
        if (field) {
          var d = {label: field.label};
          if (field.options) {
            var text = [];
            _.each(val, function(o) {
              text.push(_.findWhere(field.options, {key: o}).value);
            });
            d.text = text.join(', ');
          } else if (key === 'case_manager' && $scope.caseManagerIsMe()) {
            d.text = ts('Me');
          } else if ($.isArray(val)) {
            d.text = ts('%1 selected', {'1': val.length});
          } else if ($.isPlainObject(val)) {
            if (val.BETWEEN) {
              d.text = val.BETWEEN[0] + ' - ' + val.BETWEEN[1];
            } else if (val['<=']) {
              d.text = '≤ ' + val['<='];
            } else if (val['>=']) {
              d.text = '≥ ' + val['>='];
            } else {
              var k = _.findKey(val, function() {return true;});
              d.text = k + ' ' + val[k];
            }
          } else if (typeof val === 'boolean') {
            d.text = val ? ts('Yes') : ts('No');
          } else {
            d.text = val;
          }
          des.push(d);
        }
      });
      return des;
    }
    $scope.filterDescription = buildDescription();
  }

  angular.module('civicase').directive('civicaseSearch', function() {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/Search.html',
      controller: searchController,
      scope: {
        defaults: '=civicaseSearch',
        hiddenFilters: '=',
        onSearch: '@',
        expanded: '='
      }
    };
  });

})(angular, CRM.$, CRM._);
