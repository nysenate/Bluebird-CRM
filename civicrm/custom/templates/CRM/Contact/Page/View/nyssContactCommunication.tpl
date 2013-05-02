<div class="clear"></div>

{*address*}
<div class="contact_panel">
{assign var='locationIndex' value=1}
{if $address}
  {foreach from=$address item=add key=locationIndex}
  <div class="{cycle name=loc values="contactCardLeft,contactCardRight"} crm-address_{$locationIndex} crm-address_type_{$add.location_type}">
    <div class="crm-summary-block crm-address-block" id="address-block-{$locationIndex}" locno="{$locationIndex}">
    {include file="CRM/Contact/Page/Inline/Address.tpl"}
    </div>
  </div>
  {/foreach} {* end of address foreach *}

  {assign var='locationIndex' value=$locationIndex+1}
{/if}

{if $permission EQ 'edit'}
  {if $locationIndex eq 1 or $locationIndex is odd}
    <div class="contactCardLeft crm-address_{$locationIndex} crm-address-block appendAddLink">
  {else}
    <div class="contactCardRight crm-address_{$locationIndex} crm-address-block appendAddLink">
  {/if}

  <div class="crm-summary-block" id="address-block-{$locationIndex}" locno="{$locationIndex}">
    <div class="crm-table2div-layout">
      <div class="crm-clear">
        <a id="edit-address-block-{$locationIndex}" class="crm-link-action empty-address-block-{$locationIndex}" title="{ts}click to add address{/ts}" locno="{$locationIndex}" aid=0>
          <span class="batch-edit"></span>{ts}add address{/ts}
        </a>
      </div>
    </div>
  </div>
</div>
{/if}
</div> <!-- end of contact panel -->

{*left: phone/email; right: employer/job/dob*}
<div class="contact_panel">
  <div class="contactCardLeft">
    <div class="crm-table2div-layout">
      {*phone*}
      <div class="crm-clear crm-summary-phone-block">
        <div class="crm-summary-block" id="phone-block">
        {include file="CRM/Contact/Page/Inline/Phone.tpl"}
        </div>
      </div>

      {*email*}
      <div class="crm-clear crm-summary-email-block">
        <div class="crm-summary-block" id="email-block">
        {include file="CRM/Contact/Page/Inline/Email.tpl"}
        </div>
      </div>

      {*IM*}
      {if $im and $showIM}
        <div class="crm-clear crm-summary-block" id="im-block">
          {foreach from=$im item=item}
            {if $item.name or $item.provider}
              {if $item.name}
                <div class="crm-label">{$item.provider}&nbsp;({$item.location_type})</div>
                <div class="crm-content crm-contact_im {if $item.is_primary eq 1} primary{/if}">{$item.name}</div>
              {/if}
            {/if}
          {/foreach}
        </div>
      {/if}

      {*website*}
      <div class="crm-clear crm-summary-block">
      {foreach from=$website item=item}
        {if !empty($item.url)}
          <div class="crm-label">{$item.website_type} {ts}Website{/ts}</div>
          <div class="crm-content crm-contact_website"><a href="{$item.url}" target="_blank">{$item.url}</a></div>
        {/if}
      {/foreach}
      </div>
    </div>
  </div>

  <div class="contactCardRight">
    <div class="crm-table2div-layout">
      {if $contact_type eq 'Individual' AND $showDemographics}
          <div class="crm-summary-demographic-block">
            <div class="crm-summary-block" id="demographic-block">
            {include file="CRM/Contact/Page/Inline/Demographics.tpl"}
            </div>
          </div>
      {/if}
    </div>
  </div> <!-- contactCardRight -->
</div> <!-- contact panel -->

{if !empty($current_employer) OR !empty($job_title)}
<div class="additional-comm">
  {if $current_employer}
    <div class="crm-label">Employer</div>
    <div class="crm-content html-adjust crm-custom-data"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$current_employer_id`"}" title="{ts}view current employer{/ts}">{$current_employer}</a></div>
  {/if}
  {if $job_title}
    <div class="crm-label">Job Title</div>
    <div class="crm-content html-adjust crm-custom-data">{$job_title}</div>
  {/if}
</div>

{literal}
<script type="text/javascript">
  var addcomm = cj('.additional-comm').html();
  cj('#demographic-block .crm-clear .crm-config-option').after(addcomm);
  cj('.additional-comm').remove();
</script>
{/literal}
{/if}
