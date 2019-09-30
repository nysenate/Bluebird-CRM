{literal}
<script type="text/javascript">
  cj('#searchForm').addClass('activitySearch');
  cj('input#activity_text').addClass('huge');

  //NYSS 7892
  cj(document).ready(function(){
    if ( cj('div.messages.status.no-popup').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });
</script>
{/literal}
