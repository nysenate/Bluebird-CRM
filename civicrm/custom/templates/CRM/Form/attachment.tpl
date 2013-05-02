{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{*NYSS 3331*}
{if $form.attachFile_1 OR $currentAttachmentInfo}
{if $action EQ 4 AND $currentAttachmentInfo} {* For View action we exclude the form fields and just show any current attachments. *}
    <tr>
        <td class="label"><label>{ts}Current Attachment(s){/ts}</label></td>
      <td class="view-value">
        {foreach from=$currentAttachmentInfo key=attKey item=attVal}
          <div id="attachStatusMesg" class="status hiddenElement"></div>
          <div id="attachFileRecord_{$attVal.fileID}">
            <strong><a href="{$attVal.url}">{$attVal.cleanName}</a></strong>
            <br/>
          </div>
        {/foreach}
      </td>
    </tr>
{elseif $action NEQ 4}
  {if $context EQ 'pcpCampaign'}
        {capture assign=attachTitle}{ts}Include a Picture or an Image{/ts}{/capture}
        {assign var=openCloseStyle value='crm-accordion-open'}
  {else}
        {capture assign=attachTitle}{ts}Attachment(s){/ts}{/capture}
        {assign var=openCloseStyle value='crm-accordion-closed'}
  {/if}

  {if !$noexpand}
    <div class="crm-accordion-wrapper crm-accordion_title-accordion {$openCloseStyle}">
 		<div class="crm-accordion-header">
  	  <div class="icon crm-accordion-pointer"></div>
  		{$attachTitle}
		</div><!-- /.crm-accordion-header -->
 		<div class="crm-accordion-body">    
 	{/if}

    <div id="attachments">
      <table class="form-layout-compressed">
        {if $context EQ 'pcpCampaign'}
            <div class="description">{ts}You can upload a picture or image to include on your page. Your file should be in .jpg, .gif, or .png format. Recommended image size is 250 x 250 pixels. Maximum size is 360 x 360 pixels.{/ts}</div>
        {/if}
            <tr>
                <td class="label">{$form.attachFile_1.label}</td>
                <td>{$form.attachFile_1.html}<span class="crm-clear-link">(<a href="javascript:clearAttachment( '#attachFile_1' );">{ts}clear{/ts}</a>)</span><br />
                    <span class="description">{ts}Browse to the <strong>file</strong> you want to upload.{/ts}{if $numAttachments GT 1} {ts 1=$numAttachments}You can have a maximum of %1 attachment(s).{/ts}{/if} Each file must be less than {$config->maxFileSize}MB in size.</span>{*NYSS 5396*}
                </td>
            </tr>
    {section name=attachLoop start=2 loop=$numAttachments+1}
        {assign var=index value=$smarty.section.attachLoop.index}
        {assign var=attachName value="attachFile_"|cat:$index}
            <tr>
                <td class="label"></td>
                <td>{$form.$attachName.html}<span class="crm-clear-link">(<a href="javascript:clearAttachment( '#{$attachName}' );">{ts}clear{/ts}</a>)</span></td>
            </tr>
    {/section}
    {if $currentAttachmentInfo}
        <tr>
            <td class="label">{ts}Current Attachment(s){/ts}</td>
          <td class="view-value">
            {foreach from=$currentAttachmentInfo key=attKey item=attVal}
              <div id="attachStatusMesg" class="status hiddenElement"></div>
              <div id="attachFileRecord_{$attVal.fileID}">
                <strong><a href="{$attVal.url}">{$attVal.cleanName}</a></strong>
                {if $attVal.deleteURLArgs}
                  &nbsp;&nbsp;<a href="javascript:showDelete('{$attVal.cleanName}', '{$attVal.deleteURLArgs}', {$attVal.fileID})" title="{ts}Delete this attachment{/ts}"><span class="icon red-icon delete-icon" style="margin:0px 0px -5px 20px" title="{ts}Delete this attachment{/ts}"></span></a>
                {/if}
                <br/>
              </div>
            {/foreach}
          </td>
        </tr>
        <tr>
            <td class="label">&nbsp;</td>
            <td>{$form.is_delete_attachment.html}&nbsp;{$form.is_delete_attachment.label}<br />
              <span class="description">{ts}Click the red X next to a file name to delete a specific attachment. If you want to delete ALL attachments, check the box above and click Save.{/ts}</span>
            </td>
        </tr>
    {/if}
        </table>
    </div>
	</div><!-- /.crm-accordion-body -->
	</div><!-- /.crm-accordion-wrapper -->

{if !$noexpand}
    {literal}
    <script type="text/javascript">
		cj(function() {
		   cj().crmaccordions(); 
		});
    </script>
    {/literal}
{/if}

    {literal}
    <script type="text/javascript">
        function clearAttachment( element ) {
            cj(element).val('');
        }
    </script>
    {/literal}
{/if} {* edit/add if*}

{if $currentAttachmentInfo}
<script type="text/javascript">
  {literal}
  function hideStatus( ) {
    cj( '#attachStatusMesg' ).hide( );
  }
  function showDelete( fileName, postURLData, fileID ) {
    var confirmMsg = '{/literal}{ts escape="js"}Are you sure you want to delete attachment: {/ts}{literal}' + fileName + '&nbsp; <a href="javascript:deleteAttachment( \'' + postURLData + '\',' + fileID + ' );" style="text-decoration: underline;">{/literal}{ts}Yes{/ts}{literal}</a>&nbsp;&nbsp;&nbsp;<a href="javascript:hideStatus( );" style="text-decoration: underline;">{/literal}{ts}No{/ts}{literal}</a>';
    cj( '#attachStatusMesg' ).show( ).html( confirmMsg );
  }
  function deleteAttachment( postURLData, fileID ) {
    var postUrl = {/literal}"{crmURL p='civicrm/file/delete' h=0 }"{literal};
    cj.ajax({
      type: "GET",
      data:  postURLData,
      url: postUrl,
      success: function(html){
        var resourceBase   = {/literal}"{$config->resourceBase}"{literal};
        var successMsg = '{/literal}{ts escape="js"}The selected attachment has been deleted.{/ts}{literal} &nbsp;&nbsp;<a href="javascript:hideStatus( );"><img title="{/literal}{ts}close{/ts}{literal}" src="' +resourceBase+'i/close.png"/></a>';
        cj( '#attachFileRecord_' + fileID ).hide( );
        cj( '#attachStatusMesg' ).show( ).html( successMsg );
      }
    });
  }
  {/literal}
</script>
{/if}
{/if} {* top level if *}
