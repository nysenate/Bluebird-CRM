function frie(viewModel) {
  viewModel.editImage = function(src) {

    var ondone = function(result) {
      //console.log('ondone: result: ', result);
      src(result);
    };

    editImageFR(src(), ondone);
  }
}

function editImageFR(src, ondoneCallback) {
  //console.log('editImageFR: src: ', src);

  const onBeforeComplete = function(data) {
    //console.log('onBeforeComplete data: ', data);

    var canvas = document.getElementById(data.canvas.id);
    //console.log('canvas: ', canvas);

    var dataURL = canvas.toDataURL();
    //console.log('dataURL: ', dataURL);

    //store file via AJAX call
    $.ajax({
      type: "POST",
      url: "/civicrm/mosaicoimageditor/storefrie",
      data: {
        image: dataURL,
        url: src
      }
    }).done(function(result) {
      //console.log('result: ', result);

      //update image src upon closing FRIE
      ondoneCallback(result);
    });
  }

  const ImageEditor = new FilerobotImageEditor({
    elementId: 'mie-image-editor-download',
    translations: {
      en: {
        'toolbar.download': 'Update'
      }
    }
  }, {
    onBeforeComplete
  });

  ImageEditor.open(src);
}
