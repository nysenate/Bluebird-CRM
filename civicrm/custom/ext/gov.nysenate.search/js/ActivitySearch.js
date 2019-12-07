CRM.$(function($) {
  $(document).ready(function(){
    $('input#activity_text').addClass('huge');
    $('div#s2id_activity_tags').css('width', '333px');

    //NYSS 7892
    $(document).ready(function(){
      if ($('div.messages.status.no-popup').length) {
        CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning');
      }
    });
  });
});
