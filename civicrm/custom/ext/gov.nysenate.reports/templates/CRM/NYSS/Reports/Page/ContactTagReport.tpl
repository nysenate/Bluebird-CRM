<p><strong>
  {if $tag}
    {ts}Showing constituents tagged with{/ts} '{$tag.label}'
  {else}
    {ts}Tag not found{/ts}
  {/if}
</strong></p>
<crm-angular-js modules="afsearchConstituentTagSearch">
  <form id="bootstrap-theme">
    <afsearch-constituent-tag-search options='{$afformOptions|@json_encode}'></afsearch-constituent-tag-search>
  </form>
</crm-angular-js>