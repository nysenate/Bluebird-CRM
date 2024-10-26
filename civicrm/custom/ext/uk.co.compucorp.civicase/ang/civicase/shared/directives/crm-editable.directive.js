(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  // Angular binding for CiviCRM's jQuery-based crm-editable
  module.directive('crmEditable', function ($timeout) {
    return {
      restrict: 'A',
      link: crmEditableLink,
      scope: {
        model: '=crmEditable',
        lineLimit: '@'
      }
    };

    /**
     * Link function for crmEditable directive
     *
     * @param {object} scope scope of the directive
     * @param {object} elem element
     * @param {object} attrs attributes
     */
    function crmEditableLink (scope, elem, attrs) {
      CRM.loadScript(CRM.config.resourceBase + 'js/jquery/jquery.crmEditable.js')
        .done(function () {
          var textarea = elem.data('type') === 'textarea';
          var field = elem.data('field');
          elem
            .html(
              textarea
                ? nl2br(getHTMLToShow(scope, elem, attrs))
                : _.escape(getHTMLToShow(scope, elem, attrs))
            )
            .on('crmFormSuccess', function (e, value) {
              $timeout(function () {
                scope.$apply(function () {
                  scope.model[field] = value;
                });
                applyLineLimitIfApplicableWithTimeout(scope, elem);
              });
            })
            .crmEditable();
          scope.$watchCollection('model', function (model) {
            elem.html(
              textarea
                ? nl2br(getHTMLToShow(scope, elem, attrs))
                : _.escape(getHTMLToShow(scope, elem, attrs)));

            applyLineLimitIfApplicableWithTimeout(scope, elem);
          });

          applyLineLimitIfApplicableWithTimeout(scope, elem);
        });
    }

    /**
     * Converts New Line to HTML Break markup
     *
     * @param {string} string string to convert
     * @returns {string} converted string
     */
    function nl2br (string) {
      return (string + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
    }

    /**
     * Retuns the text to be shown as HTML,
     * if the model value is null or empty string, retuns the placeholder
     *
     * @param {object} scope scope object
     * @param {object} elem element
     * @param {object} attrs attributes
     * @returns {string} html to show
     */
    function getHTMLToShow (scope, elem, attrs) {
      var field = elem.data('field');
      var placeholder = attrs.placeholder;

      return (scope.model[field] && scope.model[field] !== '')
        ? scope.model[field]
        : placeholder;
    }

    /**
     * Applies line limit if applicable with a timeout,
     * so that UI is rendered first
     *
     * @param {object} scope scope object
     * @param {object} elem element
     */
    function applyLineLimitIfApplicableWithTimeout (scope, elem) {
      $timeout(function () {
        applyLineLimitIfApplicable(scope, elem);
      });
    }

    /**
     * Applies line limit if applicable
     *
     * @param {object} scope scope object
     * @param {object} elem element
     */
    function applyLineLimitIfApplicable (scope, elem) {
      elem.siblings('.civicase__show-more-button').remove();
      elem.removeClass('civicase__show-more-block');

      if (scope.lineLimit) {
        unTruncateBlock(scope, elem);
        elem.addClass('civicase__show-more-block');

        var LINE_HEIGHT = parseInt(elem.css('line-height'));
        var elementHeight = elem.height();
        var linesOfTextVisible = elementHeight / LINE_HEIGHT;

        if (linesOfTextVisible > scope.lineLimit) {
          var seeMoreElement = '<a class="civicase__show-more-button">See More</span>';
          $(elem).after(seeMoreElement);

          truncateBlock(scope, elem, LINE_HEIGHT);

          elem.siblings('.civicase__show-more-button').click(function () {
            if ($(this).text() === 'See More') {
              unTruncateBlock(scope, elem);
              $(this).text('Hide');
            } else {
              truncateBlock(scope, elem, LINE_HEIGHT);
              $(this).text('See More');
            }
          });
        }
      }
    }

    /**
     * Truncates the Block
     *
     * @param {object} scope scope object
     * @param {object} elem element
     * @param {object} lineHeight height of each line
     */
    function truncateBlock (scope, elem, lineHeight) {
      elem.css('max-height', (scope.lineLimit * lineHeight) + 'px');
      elem.css('overflow', 'hidden');
    }

    /**
     * Untruncates the Block
     *
     * @param {object} scope scope object
     * @param {object} elem element
     */
    function unTruncateBlock (scope, elem) {
      elem.css('max-height', 'initial');
      elem.css('overflow', 'auto');
    }
  });
})(angular, CRM.$, CRM._, CRM);
