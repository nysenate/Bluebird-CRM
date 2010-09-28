// $Id: apachesolr.js,v 1.1.2.4 2009/07/23 14:12:53 robertDouglass Exp $

Drupal.behaviors.apachesolr = function(context) {
  $('.apachesolr-hidden-facet').hide();
  $('<a href="#" class="apachesolr-showhide"></a>').text(Drupal.t('Show more')).click(function() {
    if ($(this).prev().find('.apachesolr-hidden-facet:visible').length == 0) {
      $(this).prev().find('.apachesolr-hidden-facet').show();
      $(this).text(Drupal.t('Show fewer'));
    }
    else {
      $(this).prev().find('.apachesolr-hidden-facet').hide();
      $(this).text(Drupal.t('Show more'));
    }
    return false;
  }).appendTo($('.block-apachesolr_search:has(.apachesolr-hidden-facet), .block-apachesolr:has(.apachesolr-hidden-facet)'));
}
