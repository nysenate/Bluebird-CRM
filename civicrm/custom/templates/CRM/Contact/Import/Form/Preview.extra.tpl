{literal}
<script type="text/javascript">
  cj('div#new-tag div.crm-accordion-header').text('Create a new keyword and assign it to imported records.');
  cj('div#existing-tags').remove();

  //groups
  cj('div#existing-groups div.crm-accordion-body table').addClass('form-layout-compressed');
  cj('div#existing-groups').addClass('collapsed');
  cj('div#existing-groups select#groups').css('width', '300').prop('size', '10');
  cj('div#existing-groups div.crm-accordion-body td:first').
    addClass('description label').
    html('<label>Select group(s)</label>').
    css('width', '');
</script>
{/literal}
