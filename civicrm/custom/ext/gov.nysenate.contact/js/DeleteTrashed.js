CRM.$(function($) {
  $('#processTrashed').click(function(){
    var result = confirm('Are you sure you want to permanently delete all trashed contacts?');
    if (result !== true) {
      return;
    }

    $('#output').show();
    $('#delete-action').hide();

    //trigger data load
    $.ajax({
      url: CRM.vars.NYSS.processUrl,
      success: function(data, textStatus, jqXHR){
        $('div.final').html(data);
      },
      error: function( jqXHR, textStatus, errorThrown ) {
        return false;
      }
    });
  });
});
