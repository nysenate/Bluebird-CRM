CRM.$(function($) {
  //replace home link
  $('li.menumain.crm-link-home').html('<a href="/civicrm/dashboard?reset=1"><i class="nyss-i fa-home"></i></a>');
  $('a.crm-logout-link').parents('ul.innerbox').remove();

  //add title to logout
  $('i.fa-sign-out').prop('title', 'Log Out');
});
