CRM.$(function($) {
  $(document).ready(function(){
    var staffGroupJson = CRM.vars.NYSS.staffGroupJson;
    $('table[id^=caseRoles] a.case-miniform').click(function(){
      console.log('input[name=edit_role_contact_id]: ', $('input[name=edit_role_contact_id]'));
      $('input[name=edit_role_contact_id]').attr('data-api-params', staffGroupJson);

    });
  });
});
