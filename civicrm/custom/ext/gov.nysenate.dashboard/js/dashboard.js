CRM.$(function($) {
  var bbNewsUrl =  CRM.vars.NYSS.bbNewsUrl;
  $('h1.page-title').append('<span class="bb-news-url">&raquo; ' + bbNewsUrl + '</span>');

  $(document).ready(function(){
    var checkExist = setInterval(function() {
      if ($('div#dashlets-header-col-0').length) {
        $('div#dashlets-header-col-0').text('Content');
        clearInterval(checkExist);
      }
    }, 100);
  });
});
