var $ = jQuery.noConflict();
$(document).ready(function() {
  //find anything: add/remove class on focus/unfocus
  $("#civi_text_search").focus(function(){
    var defaultText = $(this).val();
    if(defaultText === 'enter any text'){
      $(this).val('');
      $(this).addClass('input-active');
    }
  });
  $("#civi_text_search").blur(function(){
    var defaultText = $(this).val();
    if(defaultText === ''){
      $(this).val('enter any text');
      $(this).removeClass('input-active');
    }
  });

  //quicksearch: add/remove class on focus/unfocus
  $("#sort_name_navigation").focus(function(){
    var defaultText = $(this).val();
    if(defaultText === 'Name/Email'){
      $(this).val('');
      $(this).addClass('input-active');
    }
  });
  $("#sort_name_navigation").blur(function(){
    var defaultText = $(this).val();
    if(defaultText === ''){
      $(this).val('Name/Email');
      $(this).removeClass('input-active');
    }
  });

  $('body').click(function() {
    $('.primary-link').removeClass('primary-link-active');
  });

  $('.primary-link').click(function(event){
    event.stopPropagation();
  });

  $('.primary-link .main-menu-item').click( function(){
    $('.civi-admin-block-wrapper').hide();
    $('.admin-link a').removeClass('active');
    $('.primary-link').removeClass('primary-link-active');
    $(this).parent().toggleClass('primary-link-active');
  });

  //3674 prevent duplicate form submission with enter key
  //exclude export and report form as it does not redirect and thus the submit buttons should remain active
  var submitted = false;
  $('form').submit(function(e){
    //console.log(e);
    var fname = e.target.name; //alert(fid); //form name
    var faction = e.target.action; //action value
    var factionmatch = faction.search("civicrm/report/"); //-1 if not found
    var fbaseuri = e.target.baseURI;
    if (fbaseuri) {
      var fbaseurimatch = fbaseuri.search("civicrm/report/");
    }

    //prevent undefined error
    if(typeof global_formNavigate === 'undefined'){
      var global_formNavigate;
    }

    if ( submitted &&
      factionmatch == -1 &&
      fbaseurimatch == -1 &&
      fname != 'Select' &&
      fname != 'Map' &&
      fname != 'Label' &&
      fname != 'Builder' &&
      fname != 'ExportPermissions' && //10565
      global_formNavigate != false //5231
    ) {
      return false;
    }
    else {
      //5231 leave submit button unlocked if formNavigate has been triggered and canceled
      if ( global_formNavigate != false ) {
        submitted = true;
      }
      return true;
    }
  });
});
