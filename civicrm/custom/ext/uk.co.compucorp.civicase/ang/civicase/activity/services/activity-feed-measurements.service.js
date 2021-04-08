(function ($, _, angular) {
  var module = angular.module('civicase');

  module.service('ActivityFeedMeasurements', function () {
    this.setScrollHeightOf = setScrollHeightOf;

    /**
     * Returns the offset of feed body from top
     *
     * @returns {number} top offset
     */
    function getTopOffset () {
      var $feedBody = $('.civicase__activity-feed__body');
      var $feedPanelBody = $('.civicase__activity-feed>.panel-body');
      var feedPanelBodyPaddingTop = parseInt($feedPanelBody.css('padding-top'));
      var topOffset = $feedBody.offset().top + feedPanelBodyPaddingTop;

      return topOffset;
    }

    /**
     * Returns the collective height of all contact tab children
     *
     * $('.crm-contact-tabs-list').height() cannot be used to calculate the
     * height of the tabs, because $('.crm-contact-tabs-list') gets the height
     * of the right side panel(activity feed). Initially the height of the
     * activity feed is very long(depends on how many activity it has),
     * so the left tab also gets same height.
     * So If that height is fetched and again set to activity feed,
     * means nothing has changed, Thats why calculating child height is necessary.
     *
     * @returns {number} tab height
     */
    function getCivicrmContactTabHeight () {
      var height = 0;
      var $civicrmContactTabChildren = $('.crm-contact-tabs-list').children();

      _.each($civicrmContactTabChildren, function (child) {
        height += $(child).height();
      });

      return height;
    }

    /**
     * Sets height of the given element for scrolling
     *
     * @param {object} $element element
     */
    function setScrollHeightOf ($element) {
      var $civicrmContactTabs = $('.crm-contact-tabs-list');

      if ($civicrmContactTabs.length) {
        var height = getCivicrmContactTabHeight() +
          $civicrmContactTabs.offset().top - getTopOffset();
        $element.height(height);
      } else {
        $element.height('calc(100vh - ' + getTopOffset() + 'px)');
      }
    }
  });
})(CRM.$, CRM._, angular);
