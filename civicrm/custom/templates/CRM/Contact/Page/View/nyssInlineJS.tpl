<script type="text/javascript">
  //wrap inline rows in a div
  cj('.crm-summary-block .crm-table2div-layout .crm-label').each(function(){
    cj(this).next('.crm-content').andSelf().wrapAll('<div class="bb-row-wrap"/>');
  });
</script>
