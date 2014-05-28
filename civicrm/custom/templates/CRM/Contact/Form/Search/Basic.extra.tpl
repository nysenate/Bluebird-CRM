{literal}
<script type="text/javascript">
  cj(".sort_name-section .label label").text('Contact Name');

  //NYSS 7892
  cj(document).ready(function(){
    if ( cj('div.crm-results-block-empty').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });
</script>
{/literal}
