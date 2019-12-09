{literal}
<script type="text/javascript">
  //6611 lock comm pref if deceased
  if ( cj('div.crm-contact-deceased_message').length ) {
    cj('#crm-communication-pref-content').removeClass('crm-inline-edit');
    cj('#crm-communication-pref-content .crm-edit-help').hide();
  }

  //7401
  function _checkEmailLink(){
    if ( cj('div.crm-contact-deceased_message').length ||
      cj('div.crm-contact-privacy_values:contains("Do not email")').length ||
      cj('div.crm-contact-privacy_values:contains("No Bulk Emails")').length ||
      cj('div.crm-contact_email:contains("On Hold")').length
    ) {
      cj('div.crm-contact_email').each(function(){
        var emailText = cj(this).children('a').text();
        cj(this).children('a').replaceWith(emailText);
      })
    }
  }
  _checkEmailLink();
</script>
{/literal}
