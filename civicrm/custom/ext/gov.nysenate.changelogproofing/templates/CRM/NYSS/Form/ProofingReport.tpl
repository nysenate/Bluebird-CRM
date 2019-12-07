{literal}
  <style type="text/css">
    div.tag-section label {
      width: 17%;
      display: inline-block;
      text-align: right;
      padding-right: 1.6%;
    }
  </style>
  <script type="text/javascript">
    CRM.$(function($) {
      $('#proofing-report input.crm-form-submit').click(function(){
        $('div.ui-notify-message a.ui-notify-close').click();
        $('span.crm-error').remove();
      });
    });
  </script>
{/literal}

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

    <div class="crm-section tag-section">
      <div class="crm-section tag-section contact-issue-codes">
        <label>Issue Code(s)</label>
        {$form.tag.html}
      </div>

      {include file="CRM/common/Tagset.tpl"}
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

    <div class="clear"></div>
  </div>
</div>
