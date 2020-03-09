CRM.$(function($) {
  //13339 adjust iframe height/location
  var hash = window.location.hash;

  var checkExist = setInterval(function () {
    //if this is not a mailing page, clear interval/exit now
    if (hash.indexOf('mailing') === 0) {
      clearInterval(checkExist);
    }

    var iframeObj = $('iframe[crm-ui-iframe="model.body_html"]');
    if (iframeObj.length) {
      var h = $('div#crm-container').height();
      //console.log('h: ', h);

      h = h - 120;
      iframeObj.height(h + 'px');

      //move location of modal
      $('div.ui-dialog').css('top', '410px');

      clearInterval(checkExist);
    }
  }, 100); // check every 100ms
});
