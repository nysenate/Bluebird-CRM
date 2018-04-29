{* HEADER *}

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
<div id='mainTabContainer'>
  <ul>
    <li id='tab_process-reassign'><a href='#process-reassign' title='Reassign Contact'>Reassign Contact</a></li>
    <li id='tab_process-tags_contact'><a href='#process-tagscontact' title='Tag Contact'>Tag Contact</a></li>
    <li id='tab_process-groups_contact'><a href='#process-groupscontact' title='Add to Group'>Add to Group(s)</a></li>
    <li id='tab_process-tags_activity'><a href='#process-tagsactivity' title='Tag Activity'>Tag Activity</a></li>
    <li id='tab_process-activity'><a href='#process-activity' title='Edit Activity'>Edit Activity</a></li>
  </ul>
  <div id="process-reassign" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
    <div class="crm-section" id="current-assignee">
      <div class="label">Current Match{if $is_multiple}(es){/if}</div>
      <div class="content">{if $is_multiple}{$multiple_count} Contact(s){else}{$message_details[0].details.matched_to_display}{/if}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.assignee.label}</div>
      <div class="content">{$form.assignee.html}</div>
      <div class="clear"></div>
    </div>
    {if !$is_multiple}
      <div class="crm-section" id="matched-contacts">
        <div class="label">&nbsp;</div>
        <div class="content"></div>
        <div class="clear"></div>
      </div>
    {/if}
  </div>

  <div id="process-tagscontact" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
    <div class="crm-section tag-keywords">
      <div class="label">{$form.contact_keywords.label}</div>
      <div class="content">{$form.contact_keywords.html}</div>
      <div class="clear"></div>
    </div>

    {*inject tagtree*}
    <div class="crm-section tag-tree">
      <div class="label">{$form.tag.label}</div>
      <div class="content">{$form.tag.html}</div>
      <div class="clear"></div>
    </div>

    <div class="crm-section tag-positions">
      <div class="label">{$form.contact_positions.label}</div>
      <div class="content">{$form.contact_positions.html}</div>
      <div class="clear"></div>
    </div>
  </div>

  <div id="process-groupscontact" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
    <div class="crm-section group-select">
      <div class="label">{$form.group_id.label}</div>
      <div class="content">{$form.group_id.html}</div>
      <div class="clear"></div>
    </div>
  </div>

  <div id="process-tagsactivity" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
    <div class="crm-section">
      <div class="label">{$form.activity_keywords.label}</div>
      <div class="content">{$form.activity_keywords.html}</div>
      <div class="clear"></div>
    </div>
  </div>

  <div id="process-activity" class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
    <div class="crm-section">
      <div class="label">{$form.activity_assignee.label}</div>
      <div class="content">{$form.activity_assignee.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.activity_status.label}</div>
      <div class="content">{$form.activity_status.html}</div>
      <div class="clear"></div>
    </div>
  </div>
</div>

<p></p>

{*display message*}
<div class="message-details-wrapper">
{foreach from=$message_details item=message}
  <div class="crm-accordion-wrapper crm-accordion {if $is_multiple}collapsed{/if} message-details">
    <div class="crm-accordion-header">{$message.details.sender_name}: {$message.details.subject}</div>
    <div class="crm-accordion-body">
      <div class="crm-section">
        <div class="label">From</div>
        <div class="content">{$message.details.sender_name} ({$message.details.sender_email})</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">Subject</div>
        <div class="content">{$message.details.subject}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">Forwarded By</div>
        <div class="content">{$message.details.forwarded_by}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">Forwarded Date</div>
        <div class="content">{$message.details.updated_date}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">Matched To</div>
        <div class="content">{$message.details.matched_to_display}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section message-body-section">
        <div class="message-body">{$message.details.body}</div>
        <div class="clear"></div>
      </div>
    </div>
  </div>
{/foreach}
</div>

{* FOOTER *}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
