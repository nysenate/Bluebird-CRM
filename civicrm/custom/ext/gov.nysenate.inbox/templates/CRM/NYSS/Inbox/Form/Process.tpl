{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
<div id='mainTabContainer'>
  <ul>
    <li id='tab_process-reassign'><a href='#process-reassign' title='Reassign Contact'>Reassign Contact</a></li>
    <li id='tab_process-tags'><a href='#process-tags' title='Tag'>Tag</a></li>
    <li id='tab_process-activities'><a href='#process-activities' title='Edit Activity'>Edit Activity</a></li>
  </ul>
  <div id="process-reassign" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
    <div class="crm-section">
      <div class="label">{$form.assignee.label}</div>
      <div class="content">{$form.assignee.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">Currently Matched to:</div>
      <div class="content">{$details.matched_to}</div>
      <div class="clear"></div>
    </div>
  </div>

  <div id="process-tags" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
  </div>

  <div id="process-activities" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
  </div>
</div>

<p></p>

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
