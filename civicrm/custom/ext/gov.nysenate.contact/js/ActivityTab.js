CRM.$(function($) {
  var checkExist = setInterval(function() {
    if ($('table.contact-activity-selector-activity tbody tr').length) {
      $('table.contact-activity-selector-activity tbody tr').each(function() {
        var id = $(this).prop('id');
        var subject = $('table.contact-activity-selector-activity tbody tr#' + id + ' td.crmf-subject').text();
        //console.log('subject: ', subject);

        var url = '';
        $(this).find('a.action-item').each(function(){
          if ($(this).text() === 'View') {
            url = $(this).prop('href');
          }
          //console.log('item: ', $(this).text());
        });
        //console.log('url: ', url);

        //determine if there are attachments
        var att = '';
        CRM.api3('Attachment', 'get', {
          "sequential": 1,
          "entity_table": "civicrm_activity",
          "entity_id": id
        }).then(function(result) {
          if (result.count) {
            att = ' <i class="crm-i fa-paperclip"></i>';
          }
        }, function(error) {
        });

        var checkExist2 = setInterval(function() {
          if ($('table.contact-activity-selector-activity tbody tr#' + id + ' td.crmf-subject div.crm-editable')) {
            if (subject && url) {
              $('table.contact-activity-selector-activity tbody tr#' + id + ' td.crmf-subject')
                .html('<a href="' + url + '" class="crm-popup" title="View Activity">' + subject + att + '</a>');
            }
          }
        }, 100);
      });

      clearInterval(checkExist);
    }
  }, 100);
});
