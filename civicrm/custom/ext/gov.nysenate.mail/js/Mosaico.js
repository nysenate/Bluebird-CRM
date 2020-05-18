$(document).ready(function() {
  //12884 image gallery text
  var uploadExists = setInterval(function () {
    var imgUploadDiv = $('div.mo-uploadzone');
    if (imgUploadDiv.length) {
      imgUploadDiv.children('span').text('Click or drag files here to add to gallery');
      clearInterval(uploadExists);
    }
  }, 100);

  //13305 change close button text
  var closeExists = setInterval(function () {
    var closeBtn = $('a[title="Save template"] span.ui-button-text');
    if (closeBtn.length) {
      closeBtn.text(closeBtn.text().replace('Close', 'Save and Continue'));
      clearInterval(uploadExists);
    }
  }, 100);

  //13305 change test button text
  var testExists = setInterval(function () {
    var testBtn = $('a[title="Show preview and send test"]');
    if (testBtn.length) {
      $('div#tooltabs ul').append(testBtn);
      testBtn.wrap('<li></li>').removeClass().addClass('ui-tabs-anchor');
      clearInterval(testExists);

      testBtn.click(function() {
        console.log('test clicked');
      });
    }
  }, 100);
});
