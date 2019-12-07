CRM.$(function($) {
  //3815 move privacy options note
  var pon = '<span id="bb-privacy-option-notes"><label>Privacy Option Notes</label><br />' + CRM.vars.NYSS.bbPrivacyOptionNotes_Html + '</span>';
  if ($('#bb-privacy-option-notes').length == 0) {
    $('#privacyOptionNotes_cell').html(pon);
  }

  //7892
  $(document).ready(function(){
    if ($('div.crm-results-block-empty').length) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning');
    }
  });

  //11446/11440
  $('div#display-settings td:nth-child(2)').text('');
  $('div#display-settings').parents('div.crm-search_criteria_basic-accordion').addClass('collapsed');

  //13008 Mailings
  $('#mailingForm input[name=mailing_reply_status]').parent('td').html('');
  $('#mailingForm input#mailing_forward').parent('td').html('');

  //13006 dynamically resize tag fields when browser width decreases
  $(document).ready(function() {
    var $window = $(window);

    function checkWidth() {
      var windowsize = $window.width();
      if (windowsize < 1000) {
        $('div.crm-tag-ref').css('width', '260px');
      }
    }
    // Execute on load
    checkWidth();
    // Bind event listener
    $(window).resize(checkWidth);
  });
});
