CRM.$(function($) {
  $(document).ready(function() {
    //open tag tree main parent on load
    var interval_id = setInterval(function(){
      if ($('li#291').length != 0){
        clearInterval(interval_id)
        $('div.tag-tree').jstree('open_node', $('li#291'))
      }
    }, 5);
  });
});
