{if $contactType eq 'Individual'}

  <div id="inline-contact_source" class="crm-summary-row">
    <div class="crm-label">
      <label>Contact Source</label>
    </div>
    <div class="crm-content">
      {$form.custom_60.html}
    </div>
  </div>
  <div id="inline-indiv_category" class="crm-summary-row">
    <div class="crm-label">
      <label>Individual Category</label>
    </div>
    <div class="crm-content">
      {$form.custom_42.html}
    </div>
  </div>

  {literal}
  <script type="text/javascript">
    cj('#inline-contact_source').appendTo(cj('#ContactInfo .crm-inline-edit-form .crm-clear'));
    cj('#inline-indiv_category').appendTo(cj('#ContactInfo .crm-inline-edit-form .crm-clear'));
    cj('label[for=contact_source]').text('Other Source');
  </script>
  {/literal}

{/if}
