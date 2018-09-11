CRM.$(function($) {
  if ($('#bb-recentitems').is(':empty')) {
    $('#bb-recentitems').append(CRM.vars.NYSS.recentItemsList);
  }

  //recent items list; expose when clicked; hide when anything else is clicked;
  $('div#nyss-recentitems i').click(function(e){
    $('ul#nyss-recentitems-list').toggle();
    e.stopPropagation();
  });
  $(document).click(function(e){
    $('ul#nyss-recentitems-list').hide();
  });
});
