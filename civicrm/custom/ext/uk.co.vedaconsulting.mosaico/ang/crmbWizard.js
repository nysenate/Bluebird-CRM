(function(angular, $, _) {
  // Declare a list of dependencies.
  angular.module('crmbWizard', [
    'crmUi', 'crmUtil'
  ]);

  // Top-level container for a wizard. For examples, see `crmbWizard.md`.
  angular.module('crmbWizard').directive('crmbWizard', function() {
      return {
        restrict: 'EA',
        scope: {
          crmbWizard: '@',
          crmbWizardCtrl: '=',
          crmbWizardNavClass: '@' // string, A list of classes that will be added to the nav items
        },
        templateUrl: '~/crmbWizard/layout.html',
        transclude: true,
        controllerAs: 'crmbWizardCtrl',
        controller: function($scope, $parse) {
          var steps = $scope.steps = []; // array<$scope>
          var crmbWizardCtrl = this;
          var maxVisited = 0;
          var selectedIndex = null;
          //NYSS
          $scope.checkPerm = CRM.checkPerm;

          var findIndex = function() {
            var found = null;
            angular.forEach(steps, function(step, stepKey) {
              if (step.selected) found = stepKey;
            });
            return found;
          };

          /// @return int the index of the current step
          this.$index = function() { return selectedIndex; };
          /// @return bool whether the currentstep is first
          this.$first = function() { return this.$index() === 0; };
          /// @return bool whether the current step is last
          this.$last = function() { return this.$index() === steps.length -1; };
          this.$maxVisit = function() { return maxVisited; };
          this.$validStep = function() {
            return steps[selectedIndex] && steps[selectedIndex].isStepValid();
          };
          //NYSS
          this.workFlowEnabled = function() {
            return CRM.crmMailing.workflowEnabled;
          };
          this.check = function() {
            if (this.$index() == 1 || this.$index() == 0) {
              return !(CRM.crmMailing.workflowEnabled && !CRM.checkPerm('create mailings') && !CRM.checkPerm('access CiviMail'));
            }
            if (this.$index() == 2) {
              return !(CRM.crmMailing.workflowEnabled && !CRM.checkPerm('schedule mailings') && !CRM.checkPerm('access CiviMail'));
            }
            else {
              return true;
            }
          }
          this.iconFor = function(index) {
            if (index < this.$index()) return '√';
            if (index === this.$index()) return '»';
            return ' ';
          };
          this.isSelectable = function(step) {
            var stepIndex;
            for (stepIndex = 0; steps[stepIndex] !== step; stepIndex++) {}

            if (stepIndex <= selectedIndex) return true;
            for (var i = 0; i < stepIndex; i++) {
              //NYSS
              if (!steps[i].isStepValid() || (i == 1 && (CRM.crmMailing.workflowEnabled && !CRM.checkPerm('create mailings') && !CRM.checkPerm('access CiviMail')))) return false;
            }
            return true;
          };
          /*** @param Object step the $scope of the step */
          this.isStepDone = function(step) {
            var stepIndex;
            for (stepIndex = 0; steps[stepIndex] !== step; stepIndex++) {}

            if (stepIndex < selectedIndex) {
              return true;
            }
            return false;
          };

          /*** @param Object step the $scope of the step */
          this.select = function(step) {
            angular.forEach(steps, function(otherStep, otherKey) {
              otherStep.selected = (otherStep === step);
              if (otherStep === step && maxVisited < otherKey) maxVisited = otherKey;
            });
            selectedIndex = findIndex();
          };
          /*** @param Object step the $scope of the step */
          this.add = function(step) {
            if (steps.length === 0) {
              step.selected = true;
              selectedIndex = 0;
            }
            steps.push(step);
            steps.sort(function(a,b){
              return a.crmbWizardStep - b.crmbWizardStep;
            });
            selectedIndex = findIndex();
          };
          this.remove = function(step) {
            var key = null;
            angular.forEach(steps, function(otherStep, otherKey) {
              if (otherStep === step) key = otherKey;
            });
            if (key !== null) {
              steps.splice(key, 1);
            }
          };
          this.goto = function(index) {
            if (index < 0) index = 0;
            if (index >= steps.length) index = steps.length-1;
            this.select(steps[index]);
          };
          this.previous = function() { this.goto(this.$index()-1); };
          this.next = function() { this.goto(this.$index()+1); };
          if ($scope.crmbWizard) {
            $parse($scope.crmbWizard).assign($scope.$parent, this);
          }

          $scope.crmbWizardCtrl = this;
        },
        link: function (scope, element, attrs) {
          scope.ts = CRM.ts(null);
        }
      };
    });

  // Place a button in the wizard's button bar. For examples, see `crmbWizard.md`.
  angular.module('crmbWizard').directive('crmbWizardButtonPosition', function() {
    return {
      require: '^crmbWizard',
      restrict: 'EA',
      scope: {
        crmbWizardButtonPosition: '@'
      },
      template: '<span ng-transclude></span>',
      transclude: true,
      link: function (scope, element, attrs, crmbWizardCtrl) {
        var pos = scope.crmbWizardButtonPosition;
        var realButtonsEl = $(element).closest('.crmb-wizard').find('.crmb-wizard-button-' + pos);
        if (pos === 'right') realButtonsEl.append(' ');
        element.appendTo(realButtonsEl);
        if (pos === 'left') realButtonsEl.append(' ');
      }
    };
  });

  // Add a step to the wizard. For examples, see `crmbWizard.md`.
  angular.module('crmbWizard').directive('crmbWizardStep', function() {
    var nextWeight = 1;
    return {
      require: ['^crmbWizard', 'form'],
      restrict: 'EA',
      scope: {
        crmTitle: '@', // expression, evaluates to a printable string
        crmbWizardStep: '@', // int, a weight which determines the ordering of the steps
        crmbWizardStepClass: '@' // string, A list of classes that will be added to the template
      },
      template: '<div class="crmb-wizard-step {{crmbWizardStepClass}}" ng-show="selected" ng-transclude/></div>',
      transclude: true,
      link: function (scope, element, attrs, ctrls) {
        var crmbWizardCtrl = ctrls[0], form = ctrls[1];
        if (scope.crmbWizardStep) {
          scope.crmbWizardStep = parseInt(scope.crmbWizardStep);
        } else {
          scope.crmbWizardStep = nextWeight++;
        }
        scope.isStepValid = function() {
          return form.$valid;
        };
        crmbWizardCtrl.add(scope);
        scope.$on('$destroy', function(){
          crmbWizardCtrl.remove(scope);
        });
      }
    };
  });

})(angular, CRM.$, CRM._);
