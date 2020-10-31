(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Angular binding for CiviCRM's jQuery-based crm-editable
  module.directive('crmEditable', function ($timeout) {
    return {
      restrict: 'A',
      link: crmEditableLink,
      scope: {
        model: '=crmEditable'
      }
    };

    /**
     * Link function for crmEditable directive
     *
     * @param {object} scope
     * @param {object} elem
     * @param {object} attrs
     */
    function crmEditableLink (scope, elem, attrs) {
      CRM.loadScript(CRM.config.resourceBase + 'js/jquery/jquery.crmEditable.js').done(function () {
        var textarea = elem.data('type') === 'textarea';
        var field = elem.data('field');
        elem
          .html(textarea ? nl2br(getHTMLToShow(scope, elem, attrs)) : _.escape(getHTMLToShow(scope, elem, attrs)))
          .on('crmFormSuccess', function (e, value) {
            $timeout(function () {
              scope.$apply(function () {
                scope.model[field] = value;
              });
            });
          })
          .crmEditable();
        scope.$watchCollection('model', function (model) {
          elem.html(textarea ? nl2br(getHTMLToShow(scope, elem, attrs)) : _.escape(getHTMLToShow(scope, elem, attrs)));
        });
      });
    }

    /**
     * Converts New Line to HTML Break markup
     *
     * @param {String} str
     * @return {String}
     */
    function nl2br (str) {
      return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
    }

    /**
     * Retuns the text to be shown as HTML,
     * if the model value is null or empty string, retuns the placeholder
     *
     * @param {object} scope
     * @param {object} elem
     * @param {object} attrs
     * @return {String}
     */
    function getHTMLToShow (scope, elem, attrs) {
      var field = elem.data('field');
      var placeholder = attrs.placeholder;

      return (scope.model[field] && scope.model[field] !== '') ? scope.model[field] : placeholder;
    }
  });
})(angular, CRM.$, CRM._, CRM);
