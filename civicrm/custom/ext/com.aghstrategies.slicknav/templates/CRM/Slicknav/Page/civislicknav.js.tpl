// <script> Generated {$smarty.now|date_format:'%d %b %Y %H:%M:%S'}
{literal}
CRM.$(function($) {
  if ($('.slicknav_menu', '#civicrm-menu').length < 1) {
    var navMarkup = {/literal}{$navigation|@json_encode}{literal};
    $('<ul>' + navMarkup + '</ul>').slicknav({
      label: '<img src="' + CRM.config.resourceBase + 'i/logo_sm.png">',
      removeClasses: 'true',
      removeStyles: 'true',
      appendTo: '#civicrm-menu',
      closeOnClick: 'true',
      beforeOpen: function () {
        window.scrollTo(0, 0);
        $('#civicrm-menu').addClass('crm-slickmenu-open');

      },
      beforeClose: function () {
        $('#civicrm-menu').removeClass('crm-slickmenu-open');
      }
    });
  }
});
{/literal}
