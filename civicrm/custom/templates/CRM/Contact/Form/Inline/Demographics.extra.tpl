<div id="inline-religion" class="crm-summary-row">
  <div class="crm-label">
    <label>Religion</label>
  </div>
  <div class="crm-content">
    {$form.custom_63.html}
  </div>
</div>
<div id="inline-ethnicity" class="crm-summary-row">
  <div class="crm-label">
    <label>Ethnicity</label>
  </div>
  <div class="crm-content">
    {$form.custom_58.html}<br />
    {$form.custom_62.html}
  </div>
</div>

{literal}
<script type="text/javascript">
  cj('#inline-religion').appendTo(cj('#Demographics .crm-inline-edit-form .crm-clear'));
  cj('#inline-ethnicity').appendTo(cj('#Demographics .crm-inline-edit-form .crm-clear'));

  //gender other
  cj('input[name=gender_id]').parent().append('<span class="other-gender"><br />{/literal}{$form.custom_45.html}{literal}</span>');
  function _checkOtherGender() {
    if ( cj('input#civicrm_gender_Other_4').is(':checked') ) {
      cj('span.other-gender').show();
    }
    else {
      cj('span.other-gender').hide();
    }
  }
  _checkOtherGender();
  cj('input[name=gender_id]').click(function(){
    _checkOtherGender();
  });


  //6803 alter comm pref when deceased
  //when dem block is saved, IF is_deceased is checked, alter UI
  cj('#_qf_Demographics_upload').click(function(){
    if ( cj('#is_deceased').is(':checked') ) {
      cj('div.crm-contact-privacy_values').
        html('Do not phone<br \>Do not email<br \>Do not postal mail<br \>Do not sms<br \>Undeliverable: Do not mail<br \>No Bulk Emails (User Opt Out)');
      cj('div.crm-contact-preferred_communication_method_display').html('');
      CRM.alert('Communication preferences have been updated to reflect the contact is deceased.', ts('Contact Marked Deceased'), 'alert');
    }
  });

  //7401
  _checkEmailLink();
</script>
{/literal}
