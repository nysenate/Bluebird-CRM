{literal}
<script type="text/javascript">
  //4980 on hold select
  cj(document).ready(function(){
    cj('select[id$="_on_hold"]').each(function(){
      cj(this).children('option:first').text('- Active -');
    })
    cj('input.email').removeClass('big').addClass('twenty');
  });
</script>
{/literal}
