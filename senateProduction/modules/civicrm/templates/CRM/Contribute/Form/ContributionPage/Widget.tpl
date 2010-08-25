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
{include file="CRM/common/WizardHeader.tpl"}
{if $widget_id} {* If we have a widget for this page, construct the embed code.*}
    {capture assign=widgetVars}serviceUrl={$config->userFrameworkResourceURL}packages/amfphp/gateway.php&amp;contributionPageID={$id}&amp;widgetID=1{/capture}
    {capture assign=widget_code}
<div style="text-align: center; width:260px">
	<object type="application/x-shockwave-flash" data="{$config->userFrameworkResourceURL}extern/Widget/widget.swf" width="220" height="220" id="civicontribute-widget" align="middle" pluginspage="http://www.macromedia.com/go/getflashplayer">
    <param name="flashvars" value="{$widgetVars}">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="{$config->userFrameworkResourceURL}extern/Widget/widget.swf" />
	<param name="quality" value="high" />
	<param name="bgcolor" value="#ffffff" />
	</object>
</div>{/capture}
{/if}

<div id="form" class="crm-block crm-form-block crm-contribution-contributionpage-widget-form-block">
    <fieldset><legend>{ts}Configure Widget{/ts}</legend>
    <div id="help">
        {ts}CiviContribute widgets allow you and your supporters to easily promote this fund-raising campaign. Widget code can be added to any web page - and will provide a real-time display of current contribution results, and a direct link to this contribution page.{/ts} {help id="id-intro"}
    </div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout-compressed">
    	<tr class="crm-contribution-contributionpage-widget-form-block-is_active"><td style="width: 12em;">&nbsp;</td><td class="font-size11pt">{$form.is_active.html}&nbsp;{$form.is_active.label}</td></tr>
    </table>
    <div class="spacer"></div>
    
    <div id="widgetFields">
        <table class="form-layout-compressed">
         <tr class="crm-contribution-contributionpage-widget-form-block-title"><td class="label">{$form.title.label}<span class="marker"> *</span></td><td>{$form.title.html}</td></tr>
   	 <tr class="crm-contribution-form-block-url_logo"><td class="label">{$form.url_logo.label}</span></td><td>{$form.url_logo.html}</td></tr>  
 	 <tr class="crm-contribution-contributionpage-widget-form-block-button_title"><td class="label">{$form.button_title.label}</td><td>{$form.button_title.html}</td></tr>  
	 <tr class="crm-contribution-contributionpage-widget-form-block-about"><td class="label">{$form.about.label}<span class="marker"> *</span></td><td>{$form.about.html}
<br /><span class="description">{ts}Enter content for the about message. You may include HTML formatting tags. You can also include images, as long as they are already uploaded to a server - reference them using complete URLs.{/ts}</span>
</td></tr>  
	 <tr class="crm-contribution-contributionpage-widget-form-block-url_homepage"><td class="label">{$form.url_homepage.label}<span class="marker"> *</span></td><td>{$form.url_homepage.html}</td></tr>  
        </table>
        
        <div id="id-get_code">
            <fieldset>
            <legend>{ts}Preview Widget and Get Code{/ts}</legend>
            <div class="col1">
                {if $widget_id}
                    <div class="description">
                        {ts}Click <strong>Save & Preview</strong> to save any changes to your settings, and preview the widget again on this page.{/ts}
                    </div>
                    {$widget_code}<br />
                {else}
                    <div class="description">
                        {ts}Click <strong>Save & Preview</strong> to save your settings and preview the widget on this page.{/ts}<br />
                    </div>
                {/if}
                <div style="text-align: center;width:260px">{$form._qf_Widget_refresh.html}</div>
            </div>
            <div class="col2">
                {* Include "get widget code" section if widget has been created for this page and is_active. *}
                {if $widget_id}
                    <div class="description">
                        {ts}Add this widget to any web page by copying and pasting the code below.{/ts}
                    </div>
                    <textarea rows="8" cols="50" name="widget_code" id="widget_code">{$widget_code}</textarea>
                    <br />
                    <strong><a href="#" onclick="Widget.widget_code.select(); return false;">&raquo; Select Code</a></strong>
                {else}
                    <div class="description">
                        {ts}The code for adding this widget to web pages will be displayed here after you click <strong>Save and Preview</strong>.{/ts}
                    </div>
                {/if}
            </div>
            </fieldset>
        </div>

        
        <div id="id-colors-show" class="section-hidden section-hidden-border" style="clear: both;">
            <a href="#" onclick="hide('id-colors-show'); show('id-colors'); return false;"><img src="{$config->userFrameworkResourceURL}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Edit Widget Colors{/ts}</label><br />
        </div>
        <div id="id-colors" class="section-shown">
        <fieldset>
        <legend><a href="#" onclick="hide('id-colors'); show('id-colors-show'); return false;"><img src="{$config->userFrameworkResourceURL}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Widget Colors{/ts}</legend>
        <div class="description">
            {ts}Enter colors in hexadecimal format prefixed with <em>0x</em>. EXAMPLE: <em>0xFF0000</em> = Red. You can do a web search on 'hexadecimal colors' to find a chart of color codes.{/ts}
        </div>
        <table class="form-layout-compressed">
        {foreach from=$colorFields item=field key=fieldName}
          <tr><td class="label">{$form.$fieldName.label}<span class="marker"> *</span></td><td>{$form.$fieldName.html}</td></tr>
        {/foreach}
        </table>
        </fieldset>
        </div>

    </div>

    <div id="crm-submit-buttons">
        <table id="preview" class"form-layout-compressed">
	   <tr>
	      <td>{$form._qf_Widget_refresh.html}</td>
	      </td>
	   </tr>
	</table>  
    </div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    </fieldset>

</div>      
{include file="CRM/common/showHide.tpl"}

{literal}
<script type="text/javascript">
	var is_act = document.getElementsByName('is_active');
  	if ( ! is_act[0].checked) {
           hide('widgetFields');
	   hide('preview');
	} 
    function widgetBlock(chkbox) {
        if (chkbox.checked) {
	      show('widgetFields');
	      show('preview');
	      return;
        } else {
	      hide('widgetFields');
	      hide('preview');
              return;
	   }
    }
</script>
{/literal}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}
