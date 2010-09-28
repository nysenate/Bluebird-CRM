// $Id: ldapdata.admin.js,v 1.1 2009/07/28 14:03:05 miglius Exp $

/**
 * Behaviours are bound to the Drupal namespace.
 */
Drupal.behaviors.ldapdata = function(context) {
  $('#edit-test').click(function(event) {
    $('#test-message').hide();
    $('#test-spinner').show();
    var url = window.location.href + '/test';
    $.post(url, { binddn: $('#edit-ldapdata-binddn').val(), bindpw: bindpw = $('#edit-ldapdata-bindpw').val(), bindpw_clear: bindpw_clear = $('#edit-ldapdata-bindpw-clear').val() },
      function(data){
        $('#test-spinner').hide();
        $('#test-message').show().removeClass('status error').addClass(data.status ? 'status' : 'error').html(data.message);
      }, "json");
    return false;
  });
}

