CRM.$(function($) {
  $(document).ready(function(){
    //wait for element existence to attach to
    var sortName = setInterval(function(){
      if ($('li#crm-qsearch input[value=sort_name]').length != 0){
        clearInterval(sortName);
        buildQSearchHelp();
      }
    }, 5);

    function buildQSearchHelp() {
      $('li#crm-qsearch input[value=sort_name]')
        .parents('label')
        .append('<i class="fa-question crm-i nyss-qsearch-help" id="nyss-qsearch-help-sort_name" />'
      );

      //first check if the help box already exists; if not, add alert;
      $('#nyss-qsearch-help-sort_name').hover(function(){
        var helpExists = false;
        $('div.ui-notify-message h1').each(function() {
          if ($(this).text() === 'Quicksearch Name Option') {
            helpExists = true;
          }
        });

        if (!helpExists) {
          CRM.alert('Use the Name option to search individual, organization, or household names. When searching for an individual, the sort name will be "last name, first name." For example, to search for John Doe you could begin by typing "doe, j" -- and continue typing additional letters as necessary to refine the results further.', 'Quicksearch Name Option', 'info', {
            unique: true,
            expires: 0
          });
        }
      });
    }

    //NYSS 14379 - overwrite autocomplete.select from crm.menubar.js
    //direct to either contact or case record
    $('#crm-qsearch-input').autocomplete({
      select: function (event, ui) {
        var selectedOpt = $('input[name=quickSearchField]:checked').val();
        if (ui.item.value > 0) {
          var linkUrl = CRM.url('civicrm/contact/view', {reset: 1, cid: ui.item.value});
          if (selectedOpt === 'case_id') {
            linkUrl = CRM.url('civicrm/contact/view/case', {reset: 1, action: 'view', context: 'case', cid: ui.item.value, id: $(this).val()});
          }

          document.location = linkUrl;
        }
        return false;
      }
    });
  });
});
