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

{if $context eq 'caselist'}
  {literal}
  <script type="text/javascript">
    cj(document).ready(function(){
      cj('table.caseSelector th:first').after('<th></th>');
      cj('div.crm-case-search-form-block').hide();
      cj('div.crm-content-block').before('<div><h3>Cases for: {/literal}{$display_name}{literal}</h3></div>')
    });
  </script>
  {/literal}
{/if}
