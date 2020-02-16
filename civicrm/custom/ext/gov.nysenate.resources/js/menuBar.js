CRM.$(function($) {
  $(document).ready(function(){
    if ($('li#crm-qsearch input[value=sort_name]').length) {
      $('li#crm-qsearch input[value=sort_name]')
        .parents('label')
        .append('<i class="fa-lightbulb-o crm-i nyss-qsearch-help" id="nyss-qsearch-help-sort_name" />'
      );

      $('#nyss-qsearch-help-sort_name').hover(function(){
        CRM.alert('Use the Name option to search individual, organization, or household names. When searching for an individual, the sort name will be "last name, first name." For example, to search for John Doe you could begin by typing "doe, j" -- and continue typing additional letters as necessary to refine the results further.', 'Quicksearch Name Option', 'info', {unique:true, expires:0});
      });
    }
  });
});
