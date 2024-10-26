CRM.$(function($) {
  //14200 notify when copying a mailing
  $('a[title="Copy Mailing"]').click(function() {
    var title = $(this).parents('tr').children('td.crm-mailing-name').text();
    CRM.status('Copying mailing: ' + title + '...');
  });
});
