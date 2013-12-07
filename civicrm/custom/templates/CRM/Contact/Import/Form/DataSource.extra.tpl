<div id="choose-import-job-name" class="form-item" style="display: none;">
  <h3>{ts}Choose Import Job Name{/ts}</h3>
  <table class="form-layout">
    <tr>
      <td class="label">{$form.import_job_name.label}</td>
      <td>{$form.import_job_name.html}</td>
    </tr>
  </table>
</div>

{literal}
<script type="text/javascript">
  cj('div#data-source-form-block div#choose-import-job-name').show();
</script>
{/literal}
