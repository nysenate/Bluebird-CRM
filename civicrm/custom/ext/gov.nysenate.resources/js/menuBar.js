CRM.$(function($) {
  $(document).ready(function(){
    //wait for element existence to attach to
    var sortName = setInterval(function(){
      if ($('li#crm-qsearch input[value=sort_name]').length != 0){
        clearInterval(sortName);
        buildQSearchHelp();
      }
    }, 5);

    //14379 wait for quick search results the first time
    var caseId = setInterval(function(){
      if ($('li#crm-qsearch input[value=case_id]').length > 0 && $('ul.crm-quickSearch-results div.ui-menu-item-uiMenuItemWrapper a').length > 0){
        clearInterval(caseId);
        buildCaseLink();

        $('input[name=case_id]').on('input', function() {
          setTimeout(function () {
            buildCaseLink();
          }, 1500);
        });
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

    //14379
    function buildCaseLink() {
      var caseId = $('input[name=case_id]').val();
      //console.log('caseId: ', caseId);

      $('ul.crm-quickSearch-results div.ui-menu-item-uiMenuItemWrapper a').each(function() {
        var href = $(this).prop('href');
        //console.log('href: ', href);

        var urlParams = new URLSearchParams(href);
        var cid = urlParams.get('cid');
        //console.log('cid: ', cid);

        $(this).prop('href', '/civicrm/contact/view/case?reset=1&id=' + caseId + '&cid=' + cid + '&action=view&context=case');
      })
    }
  });
});
