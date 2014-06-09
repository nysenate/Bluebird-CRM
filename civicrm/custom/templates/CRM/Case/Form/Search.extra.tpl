{literal}
<script type="text/javascript">
  //NYSS 7892
  cj(document).ready(function(){
    if ( cj('div.messages.status.no-popup').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });
</script>
{/literal}
