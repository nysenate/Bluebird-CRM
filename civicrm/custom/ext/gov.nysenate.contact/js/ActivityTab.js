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


        var checkExist2 = setInterval(function() {
          if ($('table.contact-activity-selector-activity tbody tr#' + id + ' td.crmf-subject div.crm-editable')) {
            if (subject && url) {
              $('table.contact-activity-selector-activity tbody tr#' + id + ' td.crmf-subject')
                .html('<a href="' + url + '" class="crm-popup" title="View Activity">' + subject + '</a>');
            }
          }
        }, 100);
      });

      clearInterval(checkExist);
    }
  }, 100);
});
