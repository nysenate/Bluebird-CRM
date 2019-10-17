CRM.$(function($) {
  $(document).ready(function(){
    //this isn't working, so we modify in templates/CRM/CaseForm/CaseView.js for now
    var staffGroupJson = CRM.vars.NYSS.staffGroupJson;
    var checkExist = setInterval(function() {
      if ($('table[id^=caseRoles] tbody tr').length) {
        $('table[id^=caseRoles] a.case-miniform').click(function(){
          var checkExistFld = setInterval(function() {
            if ($('input[name=edit_role_contact_id]').length) {
              //$('input[name=edit_role_contact_id]').data('api-params', staffGroupJson);
              //$('input[name=edit_role_contact_id]').attr('data-api-params', staffGroupJson);

              clearInterval(checkExistFld);
            }
          }, 100);
        });

        clearInterval(checkExist);
      }
    }, 100);
  });
});
