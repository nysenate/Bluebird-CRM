<div class="crm-accordion-wrapper crm-ajax-accordion crm-ModifiedDate-accordion collapsed">
  <div class="crm-accordion-header" id="ModifiedDate">Modified Date</div>
  <div class="crm-accordion-body ModifiedDate accordion_wrapper">
    <div class="crm-container-snippet" bgcolor="white">
      <div class="help">Use the start/end dates below to filter contacts by when they were created or modified. A contact is considered to have been modified if the contact record was edited (including address, phone, email, IM, website), or a note, activity, relationship, group, tag, or case was created or modified for the contact.</div>
      <div id="modified_date-search">
        <table class="form-layout">
          <tbody>
          <tr>
            <td class="label"><label for="log_start_date">{$form.log_start_date.label}</label></td>
            <td>{include file="CRM/common/jcalendar.tpl" elementName=log_start_date}</td>
          </tr>
          <tr>
            <td class="label"><label for="log_end_date">{$form.log_end_date.label}</label></td>
            <td>{include file="CRM/common/jcalendar.tpl" elementName=log_end_date}</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{literal}
<script type="text/javascript">
  //2649 improve consistency between AdvSearch popup and full screen
  cj('.crm-advanced_search_form-accordion .crm-accordion-body').addClass('accordion_wrapper');

  //3815 move privacy options note
  var pof = '{/literal}{$form.custom_64.html}{literal}';
  var pon = '<tr><td colspan="2">Privacy Option Notes  ' + pof + '</td></tr>';
  cj('table.search-privacy-options tbody').append(pon);

  //7946
  cj('div.crm-ModifiedDate-accordion').insertBefore(cj('div.crm-search-form-block div.spacer'));
  if (cj('#log_start_date').val() || cj('#log_end_date').val()) {
    cj('div.crm-ModifiedDate-accordion').removeClass('collapsed');
  }

  //7892
  cj(document).ready(function(){
    if (cj('div.crm-results-block-empty').length) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });

  //11446/11440
  cj('div.crm-search_criteria_basic-accordion:first').addClass('collapsed').insertBefore('div.crm-location-accordion');
  cj('div#display-settings td:nth-child(2)').remove();

  //11442 copy reset button to bottom
  var resetHTML = cj('<div />').append($('div.reset-advanced-search').clone()).html();
  cj('table.form-layout span.crm-button_qf_Advanced_refresh').after('<span class="reset-bottom">' + resetHTML + '</span>');
  cj('span.reset-bottom a').removeClass('css_right');
</script>
{/literal}
