<div class="activity_details-block">
  {$form.activity_details.label}<br />
  {$form.activity_details.html}
</div>

{literal}
<script type="text/javascript">
  cj('#searchForm').addClass('activitySearch');

  //NYSS 7892
  cj(document).ready(function(){
    if ( cj('div.messages.status.no-popup').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });

  //NYSS 8440
  cj('div.activity_details-block').insertAfter('#activity_subject');
  cj('input#activity_details').addClass('huge');
</script>
{/literal}
