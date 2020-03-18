CRM.$(function($) {
  $(document).ready(function() {
    var checkExists;

    //13339 readjust iframe size
    function testPreview(checkExists) {
      //13339 adjust iframe height/location
      checkExists = setInterval(function () {
        var iframe = $('iframe[crm-ui-iframe="model.body_html"]');
        if (iframe.length) {
          var h = $(window).height();
          h = h * 0.8;
          iframe.height(h);

          //move location of modal
          $('div.ui-dialog').css('top', '25px').css('position', 'fixed');

          clearInterval(checkExists);
        }
      }, 100); // check every 100ms
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

          clearInterval(checkPreview);
        }
      }, 100);
    }
  });
});
