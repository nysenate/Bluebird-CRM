{* HEADER *}
<div class="description">Select or create a contact to assign this message to.</div>
<p></p>

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

{*display message*}
<div>
  <h3>Message Details</h3>
  <div class="crm-section">
    <div class="label">From</div>
    <div class="content">{$details.sender_name} ({$details.sender_email})</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">Subject</div>
    <div class="content">{$details.subject}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">Date</div>
    <div class="content">{$details.date_email}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">Forwarded By</div>
    <div class="content">{$details.forwarded_by}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">Forwarded Date</div>
    <div class="content">{$details.updated_date}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">Body</div>
    <div class="content message-body">{$details.body}</div>
    <div class="clear"></div>
  </div>
</div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
