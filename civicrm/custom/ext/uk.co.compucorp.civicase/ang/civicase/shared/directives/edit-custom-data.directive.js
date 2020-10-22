(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Editable custom data blocks
  module.directive('civicaseEditCustomData', function ($timeout) {
    return {
      restrict: 'A',
      link: function (scope, elem, attrs) {
        var form;

        function close () {
          form.remove();
          elem.show();
          form = null;
        }

        elem
          .addClass('crm-editable-enabled')
          .on('click', function (e) {
            if (!form) {
              var url = CRM.url('civicrm/case/cd/edit', {
                cgcount: 1,
                action: 'update',
                reset: 1,
                type: 'Case',
                entityID: scope.item.id,
                groupID: scope.customGroup.id,
                cid: scope.item.client[0].contact_id,
                subType: scope.item.case_type_id,
                civicase_reload: scope.caseGetParams()
              });
              form = $('<div></div>').html(elem.hide().html());
              form.insertAfter(elem)
                .on('click', '.cancel', close)
                .on('crmLoad', function () {
                  // Workaround bug where href="#" changes the angular route
                  $('a.crm-clear-link', form).removeAttr('href');
                })
                .on('crmFormSuccess', function (e, data) {
                  scope.$apply(function () {
                    scope.pushCaseData(data.civicase_reload[0]);
                    close();
                  });
                });
              CRM.loadForm(url, {target: form});
            }
          });
      }
    };
  });
})(angular, CRM.$, CRM._, CRM);
