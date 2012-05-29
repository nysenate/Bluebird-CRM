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
  
    <div class="crm-section">
        <div class="label"></div>
        <div class="content">
          {$form.buttons.html}
        </div>
        <div class="clear"></div>
    </div>

</div>

</div>  