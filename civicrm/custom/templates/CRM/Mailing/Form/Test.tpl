{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
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

{*NYSS 3880*}
<script src="{$config->resourceBase}../../../default/themes/Bluebird/scripts/jquery.iframe-auto-height.plugin.js" type="text/javascript"></script>

<div class="crm-block crm-form-block crm-mailing-test-form-block">
{include file="CRM/common/WizardHeader.tpl"}
<div class="help">
    {ts}It's a good idea to test your mailing by sending it to yourself and/or a selected group of people in your organization. You can also view your content by clicking (+) Preview Mailing.{/ts} {help id="test-intro"}
</div>

{include file="CRM/Mailing/Form/Count.tpl"}

<fieldset>
  <legend>Test Mailing</legend>
  <table class="form-layout">
    <tr class="crm-mailing-test-form-block-test_email"><td class="label">{$form.test_email.label}</td><td>{$form.test_email.html} {ts}(filled with your contact's token values){/ts}</td></tr>
    <tr class="crm-mailing-test-form-block-test_group"><td class="label">{$form.test_group.label}</td><td>{$form.test_group.html}</td></tr>
    <tr><td></td><td>{$form.sendtest.html}</td>
  </table>
</fieldset>

<div class="crm-accordion-wrapper crm-plain_text_email-accordion collapsed">
    <div class="crm-accordion-header">

        {ts}Preview Mailing{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
        <table class="form-layout">
          <tr class="crm-mailing-test-form-block-subject"><td class="label">{ts}Subject:{/ts}</td><td>{$subject}</td></tr>
    {if $preview.attachment}
          <tr class="crm-mailing-test-form-block-attachment"><td class="label">{ts}Attachment(s):{/ts}</td><td>{$preview.attachment}</td></tr>
    {/if}
          {if $preview.text_link}{*NYSS alter preview width*}
          <tr><td class="label">{ts}Text Version:{/ts}</td><td><iframe src="{$preview.text_link}" width="700px"><a href="{$preview.text_link}" onclick="window.open(this.href); return false;">{ts}Text Version{/ts}</a></iframe></td></tr>
          {/if}
          {if $preview.html_link}
          <tr><td class="label">{ts}HTML Version:{/ts}</td><td><iframe src="{$preview.html_link}" width="700px"><a href="{$preview.html_link}" onclick="window.open(this.href); return false;">{ts}HTML Version{/ts}</a></iframe></td></tr>
          {/if}
        </table>
    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>

</div><!-- / .crm-form-block -->

{*NYSS*}
<div id="send-confirm" title="Please confirm that you want to send this test message" style="display:none;">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span class="message"></span></p>
</div>
{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}
{literal}
<script type="text/javascript">
cj(function() {
  //NYSS
  cj('div.crm-plain_text_email-accordion').removeClass('collapsed');

  //NYSS 7345
  $('iframe').iframeAutoHeight({heightOffset: 20});

  //NYSS-6957
  cj( "#sendtest" ).hide();
  cj( "#send-confirm" ).dialog({
    modal: true,
    width: 500,
    autoOpen: false,
    resizable: false,
    draggable: false,
    buttons: {
      Cancel: function() {
        cj( this ).dialog( "close" );
      },
      "Send Message Anyway": function() {
         cj( this ).dialog( "close" );
         cj('#sendtest').click();
      }
    }
  });
  cj('#checktest').live('click', function() {
    var groupid = cj('#test_group').val();
    if(groupid == null || groupid == ''){
      cj('#sendtest').click();
    }else{
      cj.ajax({
        url: '/civicrm/NYSS/AJAX/Mailing',
        data: {
          group: groupid
        },
        success: function(data,status) {
          if(data != null || data != ''){
            results = cj.parseJSON(data);
            if (results.code == "WARN") {
                cj("#send-confirm .message").html(results.message);
                cj("#send-confirm").dialog('open');
            }else{
              cj('#sendtest').click();
            }
          }
        }
      });
    }
  });
});
</script>
{/literal}

