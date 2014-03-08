{*<pre>{$contactDetails|@print_r}</pre>*}

{*friend of senator class*}
{if $contactDetails.custom_19}
  {literal}
  <script type="text/javascript">
    cj('#contact-summary').addClass('friend-of-senator');
  </script>
  {/literal}
{/if}

{literal}
<script type="text/javascript">
  /*
   * the following code moves custom fields out of the default custom block and inserts them in the appropriate
   * existing subblocks. in some cases, we add new rows. in other cases we append to existing fields.
   */
  //remove custom fields used in top bar
  cj('#custom-set-content-1 div.crm-summary-row').each(function(){
    var labelText1 = cj(this).children('.crm-label').text();
    var content1 = cj(this).children('.crm-content').text();
    var removeList1 = ['Contact Source', 'Individual Category', 'Ethnicity', 'Other Ethnicity', 'Other Gender', 'Religion'];
    if ( cj.inArray(labelText1,removeList1) != -1 ) {
      cj(this).remove();
    }
  });

  //contact source
  if ( cj('span.nyss-contactSource').length == 0 ) {
    cj('div.crm-contact_source').parent().children('div.crm-label').text('Contact Source');
    var contactSource = '{/literal}{$contactDetails.custom_60}{literal}';
    if ( contactSource ) {
      if ( cj('div.crm-contact_source').text() ) {
        cj('div.crm-contact_source').append('<br />');
      }
      cj('div.crm-contact_source').append('<span class="nyss-contactSource">' + contactSource + '</span>');
    }
  }

  //individual category
  if ( cj('div.nyss-indivCategory').length == 0 ) {
    var indivCat = '{/literal}{$contactDetails.custom_42}{literal}';
    if ( indivCat ) {
      cj('div#crm-contactinfo-content div.crm-inline-block-content').
        append('<div class="crm-summary-row nyss-indivCategory"><div class="crm-label">Individual Category</div><div class="crm-content crm-custom_42">' + indivCat + '</div></div>');
    }
  }

  //religion
  if ( cj('div.nyss-religion').length == 0 ) {
    var religion = '{/literal}{$contactDetails.custom_63}{literal}';
    if ( religion ) {
      cj('div#crm-demographic-content div.crm-inline-block-content').
        append('<div class="crm-summary-row nyss-religion"><div class="crm-label">Religion</div><div class="crm-content crm-custom_63">' + religion + '</div></div>');
    }
  }

  //ethnicity
  if ( cj('div.nyss-ethnicity').length == 0 ) {
    var ethnicity = '{/literal}{', '|implode:$contactDetails.custom_58}{literal}';
    var ethnicityOther = '{/literal}{$contactDetails.custom_62}{literal}';
    if ( ethnicity || ethnicityOther ) {
      var br = '';
      if ( ethnicity && ethnicityOther ) {
        br = '<br />';
      }
      cj('div#crm-demographic-content div.crm-inline-block-content').
        append('<div class="crm-summary-row nyss-ethnicity"><div class="crm-label">Ethnicity</div><div class="crm-content crm-custom_58_62">' + ethnicity + br + ethnicityOther + '</div></div>');
    }
  }

  //other gender
  if ( cj('span.nyss-otherGender').length == 0 ) {
    var otherGender = '{/literal}{$contactDetails.custom_45}{literal}';
    if ( otherGender ) {
      if ( cj('div.crm-contact-gender_display').text() ) {
        cj('div.crm-contact-gender_display').append('<br />');
      }
      cj('div.crm-contact-gender_display').append('<span class="nyss-otherGender">' + otherGender + '</span>');
    }
  }

  //privacy note
  if ( cj('div.nyss-privacyNote').length == 0 ) {
    var privacyNote = '{/literal}{$contactDetails.custom_64|escape}{literal}';
    if ( privacyNote ) {
      cj('div.crm-contact-privacy_values').
        parent().
        after('<div class="crm-summary-row nyss-privacyNote"><div class="crm-label">Privacy Note</div><div class="crm-content crm-custom_64">' + privacyNote + '</div></div>');
    }
  }

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
