<div class="crm-block">

<div class="crm-form-block" id="dupe-address">

  <div id="help">
    <p>This tool is used to remove duplicate addresses for contacts. Click continue to proceed with the duplicate cleanup process. Each contact record will be examined for duplicate address blocks. The older of the two blocks will be retained.</p>
  </div>

  <div class="crm-section">
    <div class="label"></div>
    <div class="content">There {if $dupeCount eq 1} is <strong>1</strong> contact {else} are <strong>{$dupeCount}</strong> contacts{/if} with duplicate addresses.</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label"></div>
    <div class="content">
      {$form.buttons.html}
    </div>
    <div class="clear"></div>
  </div>

</div>
</div>  
