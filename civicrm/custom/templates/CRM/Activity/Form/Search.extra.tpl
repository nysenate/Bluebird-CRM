{*NYSS 8440*}
<div class="activity_details-block">
  {$form.activity_details.label}<br />
  {$form.activity_details.html|crmAddClass:huge}
</div>

{literal}
<script type="text/javascript">
  cj('#searchForm').addClass('activitySearch');
  cj('input#activity_subject').addClass('huge');

  cj('input#activity_subject').parent().append(cj('div.activity_details-block'));

  //NYSS 7892
  cj(document).ready(function(){
    if ( cj('div.messages.status.no-popup').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });
</script>
{/literal}
