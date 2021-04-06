(function (_) {
  describe('ActivityFeedMeasurements', function () {
    var ActivityFeedMeasurements;

    beforeEach(module('civicase'));

    beforeEach(inject(function (_ActivityFeedMeasurements_) {
      ActivityFeedMeasurements = _ActivityFeedMeasurements_;
    }));

    describe('setScrollHeightOf()', function () {
      var expectedTopOffset, elementToSetHeightTo;
      var originaljQueryHeightFn = CRM.$.fn.height;

      beforeEach(function () {
        spyOn(CRM.$.fn, 'height').and.callThrough();
      });

      afterEach(function () {
        removeAdditionalMarkup();
        CRM.$.fn.height = originaljQueryHeightFn;
      });

      describe('when used outside of contacts tab', function () {
        beforeEach(function () {
          addAdditionalMarkup();

          CRM.$('.activity-feed-measurement-test-markup')
            .append('<div class="element-to-set-height-to"></div>');
          elementToSetHeightTo = CRM.$('.element-to-set-height-to');

          var $feedPanelBody = CRM.$('.civicase__activity-feed>.panel-body');
          var feedPanelBodyPaddingTop = parseInt($feedPanelBody.css('padding-top'));

          ActivityFeedMeasurements.setScrollHeightOf(elementToSetHeightTo);
          expectedTopOffset =
            CRM.$('.civicase__activity-feed__body').offset().top +
            feedPanelBodyPaddingTop;
        });

        it('sets the height of the sent element', function () {
          // as the height is using 'calc', actual height could not be measured
          expect(CRM.$.fn.height).toHaveBeenCalledWith('calc(100vh - ' + expectedTopOffset + 'px)');
        });
      });

      describe('when used inside of contacts tab', function () {
        beforeEach(function () {
          addAdditionalMarkup();
          addContactsTabMarkup();

          CRM.$('.activity-feed-measurement-test-markup')
            .append('<div class="element-to-set-height-to"></div>');
          elementToSetHeightTo = CRM.$('.element-to-set-height-to');

          var $feedPanelBody = CRM.$('.civicase__activity-feed>.panel-body');
          var feedPanelBodyPaddingTop = parseInt($feedPanelBody.css('padding-top'));

          ActivityFeedMeasurements.setScrollHeightOf(elementToSetHeightTo);
          expectedTopOffset =
            CRM.$('.civicase__activity-feed__body').offset().top +
            feedPanelBodyPaddingTop;
        });

        it('sets the height of the sent element', function () {
          expect(elementToSetHeightTo.height())
            .toBe((30 + 60) + CRM.$('.crm-contact-tabs-list').offset().top - expectedTopOffset);
        });
      });

      /**
       * Add aditional markup
       */
      function addAdditionalMarkup () {
        var markup = `<div class='civicase__activity-feed'>
          <div class='panel-body' style='padding-top: 24px'>
            <div class='civicase__activity-feed__body'></div>
          </div>
        </div>`;

        var testMarkup = '<div class="activity-feed-measurement-test-markup">' + markup + '</div>';

        CRM.$(testMarkup).appendTo('body');
      }

      /**
       * Add Contacts Tab markup
       */
      function addContactsTabMarkup () {
        var contactTabMarkup = `<div class="crm-contact-tabs-list">
          <div style="height: 30px"></div>
          <div style="height: 60px"></div>
        </div>`;

        CRM.$(contactTabMarkup).appendTo('.activity-feed-measurement-test-markup');
      }

      /**
       * Remove aditional markup
       */
      function removeAdditionalMarkup () {
        CRM.$('.activity-feed-measurement-test-markup').remove();
      }
    });
  });
})(CRM._);
