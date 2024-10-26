(function(angular, $, _) {

  // Editable titles using ngModel & html5 contenteditable
  angular.module('contactlayout').directive("contactLayoutEditable", function() {
    return {
      restrict: "A",
      require: "ngModel",
      link: function(scope, element, attrs, ngModel) {
        var ts = CRM.ts('contactlayout');

        function read() {
          var htmlVal = element.html();
          if (!htmlVal) {
            htmlVal = ts('Untitled');
            element.html(htmlVal);
          }
          ngModel.$setViewValue(htmlVal);
        }

        ngModel.$render = function() {
          element.html(ngModel.$viewValue || ' ');
        };

        // Special handling for enter and escape keys
        element.on('keydown', function(e) {
          // Enter: prevent line break and save
          if (e.which === 13) {
            e.preventDefault();
            element.blur();
          }
          // Escape: undo
          if (e.which === 27) {
            element.html(ngModel.$viewValue || ' ');
            element.blur();
          }
        });

        element.on("blur change", function() {
          scope.$apply(read);
        });

        element.attr('contenteditable', 'true').addClass('crm-editable-enabled');
      }
    };
  });

})(angular, CRM.$, CRM._);
