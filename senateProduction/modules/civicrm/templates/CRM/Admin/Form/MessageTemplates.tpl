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
{* this template is used for adding/editing/deleting Message Templates *}
{capture assign=tokenDocsRepeated}{docURL page="Mail-merge Tokens for Contact Data" text="token documentation"}{/capture}
{if $action neq 8}
<div id="help">
    {ts}Use this form to add or edit re-usable message templates.{/ts} {help id="id-msgTplIntro"}
</div>
{/if}
<div class="crm-block crm-form-block"> 
<div class="form-item" id="message_templates">
<h3>{if $action eq 1}{ts}New Message Template{/ts}{elseif $action eq 2}{ts}Edit Message Template{/ts}{else}{ts}Delete Message Template{/ts}{/if}</h3>
{if $action eq 8}
   <div class="messages status">
       <div class="icon inform-icon"></div>
       {ts}Do you want to delete this message template?{/ts}
   </div>
{else}
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
        <table class="form-layout-compressed">
        <tr>
            <td class="label">{$form.msg_title.label}</td>
            <td>{$form.msg_title.html}
                <br /><span class="description html-adjust">{ts}Descriptive title of message - used for template selection.{/ts}
            </td>
        </tr>
        <tr>
            <td class="label">{$form.msg_subject.label}</td>
            <td>
                  {$form.msg_subject.html|crmReplace:class:huge}
	              <a href="#" onClick="return showToken('Subject', 3);">{$form.token3.label}</a>
	              {help id="id-token-text" file="CRM/Contact/Form/Task/Email.hlp"}
                  <div id='tokenSubject' style="display:none">
	                   <input  style="border:1px solid #999999;" type="text" id="filter3" size="20" name="filter3" onkeyup="filter(this, 3)"/><br />
	                   <span class="description">{ts}Begin typing to filter list of tokens{/ts}</span><br/>
	                   {$form.token3.html}
                  </div>
	           <br /><span class="description">{ts}Subject for email message.{/ts} {ts 1=$tokenDocsRepeated}Tokens may be included (%1).{/ts}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                        <div class="accordion ui-accordion ui-widget ui-helper-reset">
                            <span class="helpIcon" id="helptext">
	                          <a href="#" onClick="return showToken('Text', 1);">{$form.token1.label}</a>
	                          {help id="id-token-text" file="CRM/Contact/Form/Task/Email.hlp"}
	                          <div id='tokenText' style="display:none">
	                            <input  style="border:1px solid #999999;" type="text" id="filter1" size="20" name="filter1" onkeyup="filter(this, 1)"/><br />
	                            <span class="description">{ts}Begin typing to filter list of tokens{/ts}</span><br/>
	                              {$form.token1.html}
	                          </div>
                            </span>
                            <h3 class="head"> 
	                            <span class="ui-icon ui-icon-triangle-1-s" id='text'></span><a href="#">{ts}Text Message{/ts}</a>
                            </h3>
                            <div class='text'>
                              {$form.msg_text.html|crmReplace:class:huge}<br />
                              <span class="description">{ts}Text formatted message.{/ts} {ts 1=$tokenDocsRepeated}Tokens may be included (%1).{/ts}
                            </div>
                        </div>
            </td>          
        </tr>            
        <tr>               
            <td colspan="2">
                        <div class="accordion ui-accordion ui-widget ui-helper-reset">
                            <span class="helpIcon" id="helphtml">
	                          <a href="#" onClick="return showToken('Html', 2);">{$form.token2.label}</a> 
	                          {help id="id-token-html" file="CRM/Contact/Form/Task/Email.hlp"}
	                          <div id='tokenHtml' style="display:none">
	                            <input style="border:1px solid #999999;" type="text" id="filter2" size="20" name="filter2" onkeyup="filter(this, 2)"/><br />
	                            <span class="description">{ts}Begin typing to filter list of tokens{/ts}</span><br/>
	                              {$form.token2.html}
	                          </div>
                            </span>
                            <h3 class="head"> 
	                            <span class="ui-icon ui-icon-triangle-1-e" id='html'></span><a href="#">{ts}HTML Message{/ts}</a>
                            </h3>
                            <div class='html'>
                                {$form.msg_html.html|crmReplace:class:huge}<br />
                                <span class="description">{ts}You may optionally create an HTML formatted version of this message. It will be sent to contacts whose Email Format preference is 'HTML' or 'Both'.{/ts} {ts 1=$tokenDocsRepeated}Tokens may be included (%1).{/ts}
                            </div>  
                        </div>
            </td>
        </tr>
        {if !$workflow_id}
          <tr>
            <td class="label">{$form.is_active.label}</td>
            <td>{$form.is_active.html}</td>
          </tr>
        {/if}
    </table> 
  {/if}
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
  <br clear="all" />
</div>
</div> <!-- end of crm-form-block -->
{include file="CRM/Mailing/Form/InsertTokens.tpl"}
