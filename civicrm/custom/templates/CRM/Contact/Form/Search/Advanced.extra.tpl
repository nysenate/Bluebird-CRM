<div id="all_tag_types">{$form.all_tag_types.html} {$form.all_tag_types.label}</div>

{literal}
<script type="text/javascript">
  //2649 improve consistency between AdvSearch popup and full screen
  cj('.crm-advanced_search_form-accordion .crm-accordion-body').addClass('accordion_wrapper');

  //3815 move privacy options note
  var pof = '{/literal}{$form.custom_64.html}{literal}';
  var pon = '<tr><td colspan="2">Privacy Option Notes  ' + pof + '</td></tr>';
  cj('table.search-privacy-options tbody').append(pon);

  //5556
  cj('#all_tag_types').appendTo(cj('.contact-tagset-296-section .content'));

  //6383 repeat reset form button
  var rfb = cj('div.reset-advanced-search').clone();
  cj('input#_qf_Advanced_refresh').after(rfb);

  //5647 default open address block
  cj('#location').trigger('click');
  cj('.crm-location-accordion').removeClass('crm-accordion-closed').addClass('crm-accordion-open');
</script>
{/literal}
