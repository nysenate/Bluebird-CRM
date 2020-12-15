CRM.$(function($) {
  $(document).ready(function() {
    var checkExists;
    var checkDeleteExists;

    //13339 readjust iframe size
    function testPreview(checkExists) {
      //13339 adjust iframe height/location
      checkExists = setInterval(function () {
        var iframe = $('iframe[crm-ui-iframe="model.body_html"]');
        if (iframe.length) {
          var h = $(window).height();
          h = h * 0.8;
          iframe.height(h);

          //move location of modal and fix to the window
          $('div.ui-dialog')
            .draggable({containment: 'window'})
            .css('position', 'fixed')
            .css('top', '25px');

          clearInterval(checkExists);
        }
      }, 100); // check every 100ms
    }

    function deleteDraft(checkDeleteExists) {
      checkDeleteExists = setInterval(function() {
        var modalDelete = $('div.ui-dialog span.ui-dialog-title').text();

        //legacy
        if (modalDelete.length && modalDelete === 'Delete Draft') {
          $('div.ui-dialog')
            .draggable({containment: 'window'})
            .css('position', 'fixed')
            .css('top', '25px');

          clearInterval(checkDeleteExists);
        }
      }, 100);
    }

    //13339 check if this is mailing; check that preview link is present; trigger iframe resize
    if (window.location.hash.indexOf('mailing') !== 0) {
      var checkPreview = setInterval(function () {
        var prevA = $('div.preview-popup a');
        var prevB = $('div.form-group button.btn-primary');

        //legacy
        if (prevA.length > 0) {
          prevA.click(function() {
            testPreview(checkExists);
          });

          clearInterval(checkPreview);
        }

        //mosaico
        if (prevB.length > 0) {
          prevB.click(function() {
            testPreview(checkExists);
          });

          //13021 keep checking as we may need to reload/resize after the test dialog is closed and reopened
          //clearInterval(checkPreview);
        }
      }, 100);

      var checkDelete = setInterval(function () {
        var btnDel = $('button[crm-icon=fa-trash]');

        if (btnDel.length) {
          btnDel.click(function() {
            deleteDraft(checkDeleteExists);
          });

          clearInterval(checkDelete);
        }
      }, 100);

      //13426 when in mosaico, move notification popup higher up tree so it can be displayed
      var checkNotificationContainer = setInterval(function () {
        var notCont = $('div#crm-notification-container');

        if (notCont.length && $('body > div#crm-notification-container').length === 0) {
          notCont.insertBefore('body div.ui-widget-overlay');
        }
      }, 100);

      //13554 - relabel wizard steps
      var checkWizardNumber = setInterval(function () {
        if ($('.crm_wizard__title__number').length) {
          $('.crm_wizard__title__number').each(function() {
            var stepNumber = $(this).text();
            //console.log('stepNumber: ', stepNumber);

            var stepText = $(this).closest('a')[0];
            //console.log('text: ', stepText.lastChild.nodeValue.trim());

            if (stepText.lastChild.nodeValue.trim() === 'Design') {
              //console.log('stepText: ', stepText);
              stepText.lastChild.nodeValue = 'Create';

            }
            else if (stepText.lastChild.nodeValue.trim() === 'Options') {
              //console.log('stepText: ', stepText);
              stepText.lastChild.nodeValue = 'Schedule';
            }
          });

          clearInterval(checkWizardNumber);
        }
      }, 100);

      if (CRM.vars.NYSS.schedulerOnly) {
        var checkSchedulerJump = setInterval(function () {
          if ($('button.btn-primary[title="Next step"]').length) {
            //console.log('$(button.btn-primary): ', $('button.btn-primary'));
            $('button.btn-primary[title="Next step"]').trigger('click');
            clearInterval(checkSchedulerJump);
          }
        }, 100);
      }
    }
  });
});
