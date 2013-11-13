{if $validRowCount}
  <div id="choose-import-job-name" class="form-item">
    <fieldset>
      <h3>{ts}Dedupe New Contacts{/ts}</h3>
      <table class="form-layout">
        <tr>
          <td class="label">{$form.dedupeRules.label}</td>
          <td>{$form.dedupeRules.html}</td>
        </tr>
        <tr>
          <td class="label">Show dupes in new window</td>
          <td><input type="button" value="Find" id="findDedupeButton"/></td>
        </tr>
      </table>
    </fieldset>
  </div>
{/if}

{literal}
<script type="text/javascript">
  cj('div#choose-import-job-name').insertAfter('table#summary-counts');

  cj(document).ready(function() {
    var ruleSelector = cj('#dedupeRules');
    var gid = {/literal}{$importGroupId}{literal};
    cj('#findDedupeButton').click(function() {
      var rgid = ruleSelector.val();
      window.open('/civicrm/contact/dedupefind?reset=1&action=update&rgid='+rgid+'&gid='+gid);
      return false;
    });
  });
</script>
{/literal}
