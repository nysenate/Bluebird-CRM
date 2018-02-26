{literal}
<script type="text/javascript">
  //2649 improve consistency between AdvSearch popup and full screen
  cj('.crm-advanced_search_form-accordion .crm-accordion-body').addClass('accordion_wrapper');

  //3815 move privacy options note
  var pof = '{/literal}{$form.custom_64.html}{literal}';
  var pon = '<tr><td colspan="2">Privacy Option Notes  ' + pof + '</td></tr>';
  cj('table.search-privacy-options tbody').append(pon);

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
