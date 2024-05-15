{literal}
<script type="text/javascript">
  //UI adjustments for delete/restore/delete permanently
  cj('li.crm-contact-restore').addClass('crm-delete-action');
  cj('li.crm-contact-permanently-delete').addClass('crm-delete-action');
  cj('li.crm-contact-permanently-delete span').html('<div class="icon delete-icon"></div>Delete Contact Permanently');

  //4715 remove delete/trash button; moved to action dropdown
  cj('a.delete.button').parent('li.crm-delete-action.crm-contact-delete').remove();

  //7367 move display name inline block; shrink form blocks
  //cj('div.crm-summary-contactname-block').insertBefore('div.contactTopBar');

  //remove move privacy notes set
  cj('div.crm-custom-set-block-8').remove();

  //collapse district info
  cj('div.address div.crm-collapsible').addClass('collapsed');

  //move demographic block to top right
  cj('div.contactTopBar div.contactCardRight').replaceWith(cj('div.crm-summary-demographic-block').parent());

  //move comm pref above file attachments
  //cj('div.crm-summary-comm-pref-block').insertBefore('div.crm-custom-set-block-5');

  //5779 truncate file name
  cj('.crm-fileURL a').each(function(){
    var title = cj(this).text();
    if ( title.length > 30 ) {
      var short = cj.trim(title).substring(0, 30).slice(0, -1) + "...";
      cj(this).text(short);
    }
  });

  //7093 close tag lookup select when clicking tabs
  cj('li.crm-tab-button a').click(function(){
    cj('div.token-input-dropdown-facebook').hide();
  });
</script>
{/literal}

{*check delete permanently permission*}
{if !call_user_func(array('CRM_Core_Permission','check'), 'delete contacts permanently') }
{literal}
<script type="text/javascript">
  cj('li.crm-contact-permanently-delete').remove();
</script>
{/literal}
{/if}

{*5412 apply privacy UI for do_not_trade (as it is repurposed)*}
{if $privacy.do_not_trade}
  {literal}
  <script type="text/javascript">
    if ( cj('div.crm-address-block div.crm-label span.do-not-mail').length == 0 ) {
      cj('div.crm-address-block div.crm-label').
        append('<span class="icon privacy-flag do-not-mail" title="Privacy flag: Do Not Mail"></span>');
    }
  </script>
  {/literal}
{/if}

{*integration*}
{literal}
<script type="text/javascript">
  //remove website profile count
  cj('li#tab_custom_9 a em').remove();
  cj('li#tab_custom_9').removeClass('crm-count-0');//10316
</script>
{/literal}

{include file="CRM/Contact/Page/nyssInlineCommon.tpl"}

{*7889*}
{include file="CRM/Contact/Page/nyssSubscriptions.tpl"}
