{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<div class="view-content">
{if $action eq 4}{* when action is view  *}
    {if $notes}
        <h3>{ts}View Note{/ts}</h3>
        <div class="crm-block crm-content-block crm-note-view-block">
          <table class="crm-info-panel">
            <tr><td class="label">{ts}Subject{/ts}</td><td>{$note.subject}</td></tr>
            <tr><td class="label">{ts}Date:{/ts}</td><td>{$note.modified_date|crmDate}</td></tr>
            <tr><td class="label"></td><td>{$note.note|nl2br}</td></tr>
          </table>
          <div class="crm-submit-buttons"><input type="button" name='cancel' value="{ts}Done{/ts}" onclick="location.href='{crmURL p='civicrm/contact/view' q='action=browse&selectedChild=note'}';"/></div>
        </div>
        {/if}
{elseif $action eq 1 or $action eq 2} {* action is add or update *}
	<div class="crm-block crm-form-block crm-note-form-block">
    <div class="content crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
	<div class="crm-section note-subject-section no-label">

	 	<div class="content">
	 	   {$form.subject.label} {$form.subject.html} 
	 	</div>
	 	<div class="clear"></div> 
	</div>
	<div class="crm-section note-body-section no-label">
	 <div class="content">
	    {$form.note.html}
	 </div>
	 <div class="clear"></div> 
	</div>
	<div class="crm-section note-buttons-section no-label">
	 <div class="content crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
	 <div class="clear"></div> 
	</div>
    </div>
    {* include jscript to warn if unsaved form field changes *}
    {include file="CRM/common/formNavigate.tpl"}
{/if}
{if ($action eq 8)}
<fieldset><legend>{ts}Delete Note{/ts}</legend>
<div class=status>{ts 1=$notes.$id.note}Are you sure you want to delete the note '%1'?{/ts}</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
</fieldset>

{/if}

{if $permission EQ 'edit' AND ($action eq 16 or $action eq 4 or $action eq 8)}
   <div class="action-link">
	 <a accesskey="N" href="{crmURL p='civicrm/contact/view/note' q="cid=`$contactId`&action=add"}" class="button"><span><div class="icon add-icon"></div>{ts}Add Note{/ts}</span></a>
   </div>
   <div class="clear"></div>
{/if}
<div class="crm-content-block">

{if $notes}
<div class="crm-results-block">
    {* show browse table for any action *}
<h3>Notes</h3>
<div id="notes">
    {strip}
    {include file="CRM/common/jsortable.tpl"}
        <table id="options" class="display">
        <thead>
        <tr>
	        <th>{ts}Note{/ts}</th>
	        <th>{ts}Subject{/ts}</th>
	        <th>{ts}Date{/ts}</th>
	        <th>{ts}Created By{/ts}</th>
	        <th></th>
        </tr>
        </thead>
        {foreach from=$notes item=note}
        <tr id="cnote_{$note.id}" class="{cycle values="odd-row,even-row"} crm-note">
            <td class="crm-note-note">
                {$note.note|nl2br|mb_truncate:80:"...":true}
                {* Include '(more)' link to view entire note if it has been truncated *}
                {assign var="noteSize" value=$note.note|count_characters:true}
                {if $noteSize GT 80}
		        <a href="{crmURL p='civicrm/contact/view/note' q="action=view&selectedChild=note&reset=1&cid=`$contactId`&id=`$note.id`"}">{ts}(more){/ts}</a>
                {/if}
            </td>
            <td class="crm-note-subject">{$note.subject}</td>
            <td class="crm-note-modified_date">{$note.modified_date|crmDate}</td>
            <td class="crm-note-createdBy">
                <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$note.contact_id`"}">{$note.createdBy}</a>
            </td>
            <td class="nowrap">{$note.action}</td>
        </tr>
        {/foreach}
        </table>
    {/strip}
 </div>
</div>
{elseif ! ($action eq 1)}
   <div class="messages status">
        <div class="icon inform-icon"></div>
        {capture assign=crmURL}{crmURL p='civicrm/contact/view/note' q="cid=`$contactId`&action=add"}{/capture}
        {ts 1=$crmURL}There are no Notes for this contact. You can <a accesskey="N" href='%1'>add one</a>.{/ts}
   </div>
{/if}
</div>
</div>
