<div id="inline-current-employer-job">
  <div class="crm-label">
    <label>{$form.current_employer.label}</label>
  </div>
  <div class="crm-content">
    {assign var=formtexttwenty value='form-text twenty'}
    {$form.current_employer.html|crmReplace:class:$formtexttwenty}
    <div id="employer_address" style="display:none;"></div>
  </div>

  <div class="crm-label">
    <label>{$form.job_title.label}</label>
  </div>
  <div class="crm-content">
    {$form.job_title.html}
  </div>
</div>

<div id="inline-religion">
  <div class="crm-label">
    <label>Religion</label>
  </div>
  <div class="crm-content">
    {$form.custom_63.html}
  </div>
</div>

{*gender is actually part of the top block; leaving in case we change*}
{*<div id="inline-other-gender" style="display:none; margin-top: 5px;">
  {$form.custom_45.html}
</div>*}

{literal}
<script type="text/javascript">
  cj('#inline-current-employer-job').prependTo(cj('#Demographics .crm-inline-edit-form .crm-clear'));
  cj('#inline-religion').appendTo(cj('#Demographics .crm-inline-edit-form .crm-clear'));
  cj('#Demographics span.crm-clear-link a').text('clear');

  //hide gender
  cj('input#civicrm_gender_Female_1').parent('.crm-content').prev('.crm-label').hide();
  cj('input#civicrm_gender_Female_1').parent('.crm-content').hide();

  //current employer handling in this context
  var dataUrl        = "{/literal}{$employerDataURL}{literal}";
  var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
  cj('#current_employer').autocomplete( dataUrl, {
    width        : 250,
    selectFirst  : false,
    matchCase    : true,
    matchContains: true
  }).result( function(event, data, formatted) {
      var foundContact   = ( parseInt( data[1] ) ) ? cj( "#current_employer_id" ).val( data[1] ) : cj( "#current_employer_id" ).val('');
      if ( ! foundContact.val() ) {
        cj('div#employer_address').html(newContactText).show();
      } else {
        cj('div#employer_address').html('').hide();
      }
    }).bind('change blur', function() {
      if ( !cj( "#current_employer_id" ).val( ) ) {
        cj('div#employer_address').html(newContactText).show();
      }
    });

  // remove current employer id when current employer removed.
  cj("form").submit(function() {
    if ( !cj('#current_employer').val() ) cj( "#current_employer_id" ).val('');
  });

  //current employer default setting
  var employerId = "{/literal}{$currentEmployer}{literal}";
  if ( employerId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/rest' h=0 q="className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1&id=" }{literal}" + employerId ;
    cj.ajax({
      url     : dataUrl,
      async   : false,
      success : function(html){
        //fixme for showing address in div
        htmlText = html.split( '|' , 2);
        cj('input#current_employer').val(htmlText[0]);
        cj('input#current_employer_id').val(htmlText[1]);
      }
    });
  }

  cj("input#current_employer").click( function( ) {
    cj("input#current_employer_id").val('');
  });

  //6803 alter comm pref when deceased
  //when dem block is saved, IF is_deceased is checked, alter UI
  cj('#_qf_Demographics_upload').click(function(){
    if ( cj('#is_deceased').is(':checked') ) {
      cj('div.crm-contact-privacy_values').html('Do not phone<br \>Do not email<br \>Do not postal mail<br \>Do not sms<br \>Undeliverable: Do not mail<br \>No Bulk Emails (User Opt Out)');
      cj('div.crm-contact-preferred_communication_method_display').html('');
      window.alert('Communication preferences have been updated to reflect the contact is deceased.');
    }
  });

</script>
{/literal}
