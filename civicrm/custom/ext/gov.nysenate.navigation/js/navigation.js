CRM.$(function($) {
  //quick search: prepend icon (should be fixed in future core)
  if ($('input#crm-qsearch-input').prop('placeholder') === 'Quick Search') {
    $('input#crm-qsearch-input').prop('placeholder', ' Quick Search');
  }

  //add titles
  $('i.fa-home').prop('title', 'Bluebird Home');
  $('i.fa-sign-out').prop('title', 'Log Out');
});
