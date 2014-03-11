<div class="crm-block">

<div class="crm-form-block" id="proofing-report">

  <div id="help">
    <p>This tool is used to generate changelog proofing reports.</p>
  </div>

  <div class="crm-section">
    <div class="label"><label>{$form.jobID.label}</label></div>
    <div class="content">
      {$form.jobID.html}
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label"><label>{$form.alteredBy.label}</label></div>
    <div class="content">
      {$form.alteredBy.html}
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section tag-section contact-issue-codes">
    <div class="label">
      <label>{$form.contact_tags.label}</label>
    </div>
    <div class="content">
      {$form.contact_tags.html}
      {literal}
        <script type="text/javascript">
          cj("select#contact_tags").crmasmSelect({
            addItemTarget: 'bottom',
            animate: false,
            highlight: true,
            sortable: true,
            respectParents: true
          });
        </script>
      {/literal}
    </div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.start_date.label}</div>
    <div class="content">
      {include file="CRM/common/jcalendar.tpl" elementName=start_date}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.end_date.label}</div>
    <div class="content">
      {include file="CRM/common/jcalendar.tpl" elementName=end_date}
    </div>
    <div class="clear"></div>
  </div>

  <div id="selectPdfFormat" class="crm-section">
    <div class="label"><label>{$form.pdf_format_id.label}</label></div>
    <div class="content">
      {$form.pdf_format_id.html}
    </div>
    <div class="clear"></div>
  </div>

  <div id="mergeHouse" class="crm-section">
    <div class="label"><label>{$form.merge_house.label}</label></div>
    <div class="content">
      {$form.merge_house.html}
    </div>
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
