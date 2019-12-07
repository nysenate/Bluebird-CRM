(function(angular, $, _) {

  // CaseList directive controller
  function caseViewController($scope, crmApi, formatActivity, formatCase, getActivityFeedUrl, $route, $timeout) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('civicase');
    var caseTypes = CRM.civicase.caseTypes;
    var caseStatuses = $scope.caseStatuses = CRM.civicase.caseStatuses;
    var activityTypes = $scope.activityTypes = CRM.civicase.activityTypes;
    var panelLimit = 5;
    $scope.activityFeedUrl = getActivityFeedUrl;
    $scope.caseTypesLength = _.size(caseTypes);
    $scope.CRM = CRM;
    $scope.item = null;
    $scope.caseGetParams = function() {
      return JSON.stringify(caseGetParams());
    };
    // Categories to show in the summary block
    $scope.upNextCategories = _.cloneDeep(CRM.civicase.activityCategories);
    delete $scope.upNextCategories.alert;
    delete $scope.upNextCategories.system;

    function caseGetParams() {
      return {
        id: $scope.item.id,
        return: ['subject', 'contact_id', 'case_type_id', 'status_id', 'contacts', 'start_date', 'end_date', 'is_deleted', 'activity_summary', 'activity_count', 'category_count', 'tag_id.name', 'tag_id.color', 'tag_id.description', 'tag_id.parent_id', 'related_case_ids'],
        // Related cases by contact
        'api.Case.get.1': {
          contact_id: {IN: "$value.contact_id"},
          id: {"!=": "$value.id"},
          is_deleted: 0,
          return: ['case_type_id', 'start_date', 'end_date', 'status_id', 'contacts', 'subject']
        },
        // Linked cases
        'api.Case.get.2': {
          id: {IN: "$value.related_case_ids"},
          is_deleted: 0,
          return: ['case_type_id', 'start_date', 'end_date', 'status_id', 'contacts', 'subject']
        },
        // For the "recent communication" panel
        'api.Activity.get.1': {
          case_id: "$value.id",
          is_current_revision: 1,
          is_test: 0,
          "activity_type_id.grouping": {LIKE: "%communication%"},
          'status_id.filter': 1,
          options: {limit: panelLimit, sort: 'activity_date_time DESC'},
          return: ['activity_type_id', 'subject', 'activity_date_time', 'status_id', 'target_contact_name', 'assignee_contact_name', 'is_overdue', 'is_star', 'file_id']
        },
        // For the "tasks" panel
        'api.Activity.get.3': {
          case_id: "$value.id",
          is_current_revision: 1,
          is_test: 0,
          "activity_type_id.grouping": {LIKE: "%task%"},
          'status_id.filter': 0,
          options: {limit: panelLimit, sort: 'activity_date_time ASC'},
          return: ['activity_type_id', 'subject', 'activity_date_time', 'status_id', 'target_contact_name', 'assignee_contact_name', 'is_overdue', 'is_star', 'file_id']
        },
        // Custom data
        'api.CustomValue.gettree': {
          entity_id: "$value.id",
          entity_type: 'Case',
          return: ['custom_group.id', 'custom_group.name', 'custom_group.title', 'custom_group.collapse_display', 'custom_field.name', 'custom_field.label', 'custom_value.display']
        },
        // Relationship description field
        'api.Relationship.get': {
          case_id:  "$value.id",
          is_active: 1,
          return: ['id', 'relationship_type_id', 'contact_id_a', 'contact_id_b', 'description'],
        },
        sequential: 1
      };
    }

    function getAllowedCaseStatuses(definition) {
      var ret = _.cloneDeep(caseStatuses);
      if (definition.statuses && definition.statuses.length) {
        _.each(_.cloneDeep(ret), function(status, id) {
          if (definition.statuses.indexOf(status.name) < 0) {
            delete(ret[id]);
          }
        });
      }
      return ret;
    }

    function getAvailableActivityTypes(activityCount, definition) {
      var ret = [],
        exclude = ['Change Case Status', 'Change Case Type'];
      _.each(definition.activityTypes, function(actSpec) {
        if (exclude.indexOf(actSpec.name) < 0) {
          var actTypeId = _.findKey(activityTypes, {name: actSpec.name});
          if (!actSpec.max_instances || !activityCount[actTypeId] || (actSpec.max_instances < activityCount[actTypeId])) {
            ret.push($.extend({id: actTypeId}, activityTypes[actTypeId]));
          }
        }
      });
      return _.sortBy(ret, 'label');
    }

    $scope.tabs = [
      {name: 'summary', label: ts('Summary')},
      {name: 'activities', label: ts('Activities')},
      {name: 'people', label: ts('People')},
      {name: 'files', label: ts('Files')}
    ];

    $scope.selectTab = function(tab) {
      $scope.activeTab = tab;
      if (typeof $scope.isFocused === 'boolean') {
        $scope.isFocused = true;
      }
    };

    $scope.$watch('isFocused', function() {
      $timeout(function() {
        var $actHeader = $('.act-feed-panel .panel-header'),
        $actControls = $('.act-feed-panel .act-list-controls');

        if($actHeader.hasClass('affix')) {
            $actHeader.css('width',$('.act-feed-panel').css('width'));
        }
        else {
          $actHeader.css('width', 'auto');
        }

        if($actControls.hasClass('affix')) {
            $actControls.css('width',$actHeader.css('width'));
        }
        else {
          $actControls.css('width', 'auto');
        }
      },1500);
      
    });

    function formatAct(act) {
      return formatActivity(act, $scope.item.id);
    }

    function formatCaseDetails(item) {
      formatCase(item);
      item.definition = caseTypes[item.case_type_id].definition;
      item.relatedCases = _.each(_.cloneDeep(item['api.Case.get.1'].values), formatCase);
      // Add linked cases
      _.each(_.cloneDeep(item['api.Case.get.2'].values), function(linkedCase) {
        var existing = _.find(item.relatedCases, {id: linkedCase.id});
        if (existing) {
          existing.is_linked = true;
        } else {
          linkedCase.is_linked = true;
          item.relatedCases.push(formatCase(linkedCase));
        }
      });
      delete(item['api.Case.get.1']);
      delete(item['api.Case.get.2']);
      // Recent communications
      item.recentCommunication = _.each(_.cloneDeep(item['api.Activity.get.1'].values), formatAct);
      delete(item['api.Activity.get.1']);
      // Tasks
      item.tasks = _.each(_.cloneDeep(item['api.Activity.get.3'].values), formatAct);
      delete(item['api.Activity.get.3']);
      // Custom fields
      item.customData = item['api.CustomValue.gettree'].values || [];
      _.each(item.customData, function(customGroup, index) {
        customGroup.collapse_display = customGroup.collapse_display === '1';
        // Maintain collapse state
        if ($scope.item && $scope.item.customData) {
          customGroup.collapse_display = $scope.item.customData[index].collapse_display;
        }
      });
      delete(item['api.CustomValue.gettree']);
      return item;
    }

    $scope.gotoCase = function(item, $event) {
      if ($event && $($event.target).is('a, a *, input, button, button *')) {
        return;
      }
      var cf = {
        case_type_id: [caseTypes[item.case_type_id].name],
        status_id: [caseStatuses[item.status_id].name]
      };
      var p = angular.extend({}, $route.current.params, {caseId: item.id, cf: JSON.stringify(cf)});
      $route.updateParams(p);
    };

    $scope.pushCaseData = function(data) {
      // If the user has already clicked through to another case by the time we get this data back, stop.
      if ($scope.item && data.id === $scope.item.id) {
        // Maintain the reference to the variable in the parent scope.
        delete($scope.item.tag_id);
        _.assign($scope.item, formatCaseDetails(data));
        $scope.allowedCaseStatuses = getAllowedCaseStatuses($scope.item.definition);
        $scope.availableActivityTypes = getAvailableActivityTypes($scope.item.activity_count, $scope.item.definition);
        $scope.$broadcast('updateCaseData');
      }
    };

    $scope.refresh = function(apiCalls) {
      if (!_.isArray(apiCalls)) apiCalls = [];
      apiCalls.push(['Case', 'getdetails', caseGetParams()]);
      crmApi(apiCalls, true).then(function(result) {
        $scope.pushCaseData(result[apiCalls.length - 1].values[0]);
      });
    };

    // Create activity when changing case subject
    $scope.onChangeSubject = function(newSubject) {
      CRM.api3('Activity', 'create', {
        case_id: $scope.item.id,
        activity_type_id: 'Change Case Subject',
        subject: newSubject,
        status_id: 'Completed'
      });
    };

    $scope.markCompleted = function(act) {
      $scope.refresh([['Activity', 'create', {id: act.id, status_id: act.is_completed ? 'Scheduled' : 'Completed'}]]);
    };

    $scope.getActivityType = function(name) {
      return _.findKey(activityTypes, {name: name});
    };

    $scope.newActivityUrl = function(actType) {
      var path = 'civicrm/case/activity',
        args = {
          action: 'add',
          reset: 1,
          cid: $scope.item.client[0].contact_id,
          caseid: $scope.item.id,
          atype: actType.id,
          civicase_reload: $scope.caseGetParams()
        };
      // CiviCRM requires nonstandard urls for a couple special activity types
      if (actType.name === 'Email') {
        path = 'civicrm/activity/email/add';
        args.context = 'standalone';
        delete args.cid;
      }
      if (actType.name === 'Print PDF Letter') {
        path = 'civicrm/activity/pdf/add';
        args.context = 'standalone';
      }
      return CRM.url(path, args);
    };

    $scope.editActivityUrl = function(id) {
      return CRM.url('civicrm/case/activity', {
        action: 'update',
        reset: 1,
        cid: $scope.item.client[0].contact_id,
        caseid: $scope.item.id,
        id: id,
        civicase_reload: $scope.caseGetParams()
      });
    };

    $scope.viewActivityUrl = function(id) {
      return CRM.url('civicrm/case/activity', {
        action: 'update',
        reset: 1,
        cid: $scope.item.client[0].contact_id,
        caseid: $scope.item.id,
        id: id,
        civicase_reload: $scope.caseGetParams()
      });
    };

    $scope.addTimeline = function(name) {
      $scope.refresh([['Case', 'addtimeline', {case_id: $scope.item.id, 'timeline': name}]]);
    };

    // Copied from ActivityList.js - used by the Recent Communication panel
    $scope.isSameDate = function(d1, d2) {
      return d1 && d2 && (d1.slice(0, 10) === d2.slice(0, 10));
    };

    $scope.panelPlaceholders = function(num) {
      return _.range(num > panelLimit ? panelLimit : num);
    };

    $scope.$watch('item', function() {
      // Fetch extra info about the case
      if ($scope.item && $scope.item.id && !$scope.item.definition) {
        crmApi('Case', 'getdetails', caseGetParams()).then(function (info) {
          $scope.pushCaseData(info.values[0]);
        });
      }
    });
  }

  angular.module('civicase').directive('civicaseView', function() {
    return {
      restrict: 'A',
      template:
        '<div class="panel panel-default civicase-view-panel">' +
          '<div class="panel-header" ng-if="item" ng-include="\'~/civicase/CaseViewHeader.html\'"></div>' +
          '<div class="panel-body case-view-body" ng-if="item" ng-include="\'~/civicase/CaseTabs.html\'"></div>' +
          '<div ng-if="!item" class="case-view-placeholder-panel" ng-include="\'~/civicase/CaseViewPlaceholder.html\'"></div>' +
        '</div>' +
        '<div class="panel panel-primary civicase-view-other-cases-panel" ng-if="item && item.relatedCases.length && activeTab === \'summary\'" ng-include="\'~/civicase/CaseViewOtherCases.html\'"></div>',
      controller: caseViewController,
      scope: {
        activeTab: '=civicaseTab',
        isFocused: '=civicaseFocused',
        item: '=civicaseView'
      }
    };
  })
  .directive('caseTabAffix', function($timeout) {
    return {
      scope: {},
      link: function(scope, $el, attrs) {

        $timeout(function() {
          $caseNavigation = $('.civicase-view-tab-bar'),
          $civicrmMenu = $('#civicrm-menu'),
          $casePanelBody = $('.civicase-view-panel > .panel-body');

          $caseNavigation.affix({
            offset: {
              top: $casePanelBody.offset().top - 87
            }
          })
          .on('affixed.bs.affix', function() {
            $caseNavigation.css('top', $civicrmMenu.height());
          })
          .on('affixed-top.bs.affix', function() {
            $caseNavigation.css('top','auto');
          });
        });
      }
    };
  });

})(angular, CRM.$, CRM._);
