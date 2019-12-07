(function(angular, $, _) {

  angular.module('civicase').directive('civicaseActivityFilters', function($timeout, crmUiHelp) {

    function activityFilters($scope, element, attrs) {
      var ts = $scope.ts = CRM.ts('civicase');

      function mapSelectOptions(opt, id) {
        return {
          id: id,
          text: opt.label,
          color: opt.color,
          icon: opt.icon
        };
      }

      $timeout(function() {

        var $actHeader = $('.act-feed-panel .panel-header'),
          $actControls = $('.act-feed-panel .act-list-controls'),
          $civicrmMenu = $('#civicrm-menu'),
          $feedActivity = $('.act-feed-view-activity'),
          $casePanelBody = $('.civicase-view-panel > .panel-body'),
          $casePanel = $('.civicase-view-panel');


        if($casePanel.length < 1) {
          $casePanel = $('.dashboard-activites');
          $casePanelBody = $('.dashboard-activites');
        }

        $feedActivity.affix({
          offset: {
            top: $casePanelBody.offset().top - 73,
            bottom: $(document).height() - ($casePanel.offset().top + $casePanel.height()) + 18
          }
        })
        .on('affixed.bs.affix', function() {
          $feedActivity.css('top',$civicrmMenu.height() + $actHeader.height() + $actControls.height() + 59);
        })
        .on('affixed-top.bs.affix', function() {
          $feedActivity.css('top','auto');
        });

        $actHeader.affix({offset: {top: $casePanelBody.offset().top - 73} })
          .css('top', $civicrmMenu.height() + 53)
          .css('width',$('.act-feed-panel').css('width'))
          .on('affixed.bs.affix', function() {
            $actHeader.css('width',$('.act-feed-panel').css('width'));
            $actHeader.css('top', $civicrmMenu.height() + 53);
          })
          .on('affixed-top.bs.affix', function() {
            $actHeader.css('width','auto');
          });
        
        $actControls.affix({offset: {top: $casePanelBody.offset().top - 73} })
          .css('width',$actHeader.css('width'))
          .on('affixed.bs.affix', function() {
            $actControls.css('width',$actHeader.css('width'));
            $actControls.css('top',$civicrmMenu.height() + $actHeader.height() + 53);
          })
          .on('affixed-top.bs.affix', function() {
            $actControls.css('width','auto');
            $actControls.css('top', 'auto');
          });

        $scope.$watchCollection('[filters, exposedFilters]', function(){
          $timeout(function() {
            $actControls.css('top',$civicrmMenu.height() + $actHeader.height() + 53);
            $feedActivity.not('.cc-zero-w')
              .height($(window).height() - ($civicrmMenu.height() + $actHeader.height() + $actControls.height()))
              .css('top',$civicrmMenu.height() + $actHeader.height() + $actControls.height() + 53);
          });
        });
      });

      $scope.availableFilters = [
        {
          name: 'activity_type_id',
          label: ts('Activity type'),
          html_type: 'Select',
          options: _.map(CRM.civicase.activityTypes, mapSelectOptions)
        },
        {
          name: 'status_id',
          label: ts('Status'),
          html_type: 'Select',
          options: _.map(CRM.civicase.activityStatuses, mapSelectOptions)
        },
        {
          name: 'target_contact_id',
          label: ts('With'),
          html_type: 'Autocomplete-Select',
          entity: 'Contact'
        },
        {
          name: 'assignee_contact_id',
          label: ts('Assigned to'),
          html_type: 'Autocomplete-Select',
          entity: 'Contact'
        },
        {
          name: 'tag_id',
          label: ts('Tagged'),
          html_type: 'Autocomplete-Select',
          entity: 'Tag',
          api_params: {used_for: {LIKE: '%civicrm_activity%'}}
        },
        {
          name: 'text',
          label: ts('Contains text'),
          html_type: 'Text'
        }
      ];
      if (_.includes(CRM.config.enableComponents, 'CiviCampaign')) {
        $scope.availableFilters.push({
          name: 'campaign_id',
          label: ts('Campaign'),
          html_type: 'Autocomplete-Select',
          entity: 'Campaign'
        });
      }
      if (CRM.checkPerm('administer CiviCRM')) {
        $scope.availableFilters.push({
          name: 'is_deleted',
          label: ts('Deleted Activities'),
          html_type: 'Select',
          options: [{id: 1, text: ts('Deleted')}, {id: 0, text: ts('Normal')}]
        },
        {
          name: 'is_test',
          label: ts('Test Activities'),
          html_type: 'Select',
          options: [{id: 1, text: ts('Test')}, {id: 0, text: ts('Normal')}]
        });
      }
      $scope.availableFilters = $scope.availableFilters.concat(CRM.civicase.customActivityFields);

      // Default exposed filters
      $scope.exposedFilters = {
        activity_type_id: true,
        status_id: true,
        assignee_contact_id: true,
        tag_id: true,
        text: true
      };
      // Ensure set filters are also exposed
      _.each($scope.filters, function(filter, key) {
        $scope.exposedFilters[key] = true;
      });

      $scope.exposeFilter = function(field, $event) {
        var shown = !$scope.exposedFilters[field.name];
        if (shown) {
          // Focus search element when selecting
          $timeout(function () {
            var $span = $('[data-activity-filter=' + field.name + ']', element);
            if ($('[crm-entityref], [crm-ui-select]', $span).length) {
              $('[crm-entityref], [crm-ui-select]', $span).select2('open');
            } else {
              $('input:first', $span).focus();
            }
          }, 50);
        } else {
          // Keep menu open when deselecting
          $event.stopPropagation();
          delete $scope.filters[field.name];
        }
      };

      $scope.hasFilters = function hasFilters() {
        var result = false;
        _.each($scope.filters, function(value){
          if (!_.isEmpty(value)) result = true;
        });
        return result;
      };

      $scope.clearFilters = function clearFilters() {
        _.each(_.keys($scope.filters), function(key){
          delete $scope.filters[key];
        });
      };
    }

    return {
      restrict: 'A',
      scope: {
        filters: '=civicaseActivityFilters'
      },
      templateUrl: '~/civicase/ActivityFilters.html',
      link: activityFilters,
      transclude: true
    };
  });

})(angular, CRM.$, CRM._);
