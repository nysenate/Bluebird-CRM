(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.config(function ($provide) {
    $provide.decorator('crmUiSelectDirective', function ($delegate) {
      var directive = $delegate[0];
      var link = directive.link;

      directive.compile = function () {
        return function (scope, element, attrs) {
          link.apply(this, arguments);

          /**
           * The logic is for disabling chrome autofills. New chrome version needs auto complete to be set to 'new-password'.
           * Refer - https://stackoverflow.com/questions/15738259/disabling-chrome-autofill
           * This should be the part of select 2 library implementation and till this is not implemented in the select2 library,
           * this should be kept here.
           *
           * Todo -
           * Move this logic into crmUiSelect Directive so that this can be implemented for all input single select elements.
           */
          element.siblings('.select2-container').find('input[autocomplete]').attr('autocomplete', 'new-password');
        };
      };

      return $delegate;
    });
  });
})(angular, CRM.$, CRM._, CRM);
