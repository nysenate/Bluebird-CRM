CRM.$(function($) {
  $(document).ready(function() {
    //open tag tree main parent on load
    var interval_id = setInterval(function(){
      if ($('li#291').length != 0){
        clearInterval(interval_id);
        $('div.tag-tree').jstree('open_node', $('li#291'));
      }
    }, 5);

    //13121
    var intKeywords = setInterval(function(){
      if ($('div#tagset-296 div.crm-submit-buttons span').length != 0) {
        $('div#tagset-296 div.crm-submit-buttons span').each(function () {
          var html = $(this).html();
          $(this).html(html.replace('Add Tag', 'Add Keyword'));
        });
        clearInterval(intKeywords);
      }
    }, 5);
  });
});
