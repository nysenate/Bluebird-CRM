CRM.$(function($) {
  // NYSS #13163 and #12035
  var nyss_customize_activities = function(trElement) {
    try {

      var id = $(trElement).prop('id');
      var subject = $(trElement).find('td.crmf-subject').text().trim();

      var url = '';
      $(trElement).find('a.action-item').each(function () {
        if ($(this).text() === 'View') {
          url = $(this).prop('href');
        }
        //console.log('item: ', $(this).text());
      });

      if (subject && url) {
        $(trElement).find('td.crmf-subject').html('<a href="' + url + '" class="crm-popup" title="View Activity">' + subject + '</a>');
      }

      CRM.api3('Attachment', 'get', {
        "sequential": 1,
        "entity_table": "civicrm_activity",
        "entity_id": id
      }).then(function(result) {
        if (result.count) {
          if ($(trElement).find('td.crmf-subject i.crm-i').length === 0) {
            $(trElement).find('td.crmf-subject a').last().after('&nbsp;<i class="crm-i fa-paperclip"></i>');
          }
          //att = ' ';
        }
      }, function(error) {
      });

    } catch(e) {
      console.error('MutationObserver error:', e);
    }
  };

  var callback = function(mutationsList, observer) {
    for (var mutation of mutationsList) {
        // be very specific about which elements we take action on
        // to avoid too much repetitive processing.
        if (mutation.target instanceof HTMLElement &&
            mutation.target.classList.contains('crm-editable-enabled') &&
            $(mutation.target).parent().hasClass('crmf-subject'))
        {
          nyss_customize_activities($(mutation.target).closest('tr'));
        }
    }
  };

  var observer = new MutationObserver(callback);
  observer.observe(document.getElementById('ui-id-3'), {childList: true, subtree:true });

});
