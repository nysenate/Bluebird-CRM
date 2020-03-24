$(document).ready(function() {
  //12884 image gallery text
  var uploadExists = setInterval(function () {
    var imgUploadDiv = $('div.mo-uploadzone');
    if (imgUploadDiv.length) {
      imgUploadDiv.children('span').text('Click or drag files here to add to gallery');
      clearInterval(uploadExists);
    }
  }, 100);
});
