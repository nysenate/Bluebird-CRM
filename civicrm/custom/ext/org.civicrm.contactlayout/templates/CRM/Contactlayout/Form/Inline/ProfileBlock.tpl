<div class="crm-inline-edit-form">
  <div class="crm-inline-button">
    {include file="CRM/common/formButtons.tpl"}
  </div>
  {if $help_pre}
    <div class="messages help">{$help_pre}</div>
  {/if}
  {foreach from=$fields item=field key=fieldName}
    {if $field.skipDisplay}
      {continue}
    {/if}
    {assign var="profileID" value=$field.group_id}
    {assign var=n value=$field.name}
    {if $field.field_type eq "Formatting"}
      {$field.help_pre}
    {elseif $n}
      {if $field.help_pre && $action neq 4 && $form.$n.html}
        <div class="crm-section helprow-{$n}-section helprow-pre" id="helprow-{$n}">
          <div class="content description">{$field.help_pre}</div>
        </div>
      {/if}
      {if $field.options_per_line}
        <div class="crm-summary-row editrow_{$n}-section">
          <div class="crm-label">{$form.$n.label}</div>
          <div class="crm-content edit-value">
            {assign var="count" value="1"}
            {strip}
              <table class="form-layout-compressed">
                <tr>
                  {foreach name=outer key=key item=item from=$form.$n}
                  {* There are both numeric and non-numeric keys mixed in here, where the non-numeric are metadata that aren't arrays with html members. *}
                  {if is_array($item) && array_key_exists('html', $item)}
                  <td class="labels font-light">{$form.$n.$key.html}</td>
                  {if $count == $field.options_per_line}
                </tr>
                <tr>
                  {assign var="count" value="1"}
                  {else}
                  {assign var="count" value=$count+1}
                  {/if}
                  {/if}
                  {/foreach}
                </tr>
              </table>
            {/strip}
          </div>
          <div class="clear"></div>
        </div>{* end of main edit section div*}
      {else}
        <div class="crm-summary-row editrow_{$n}-section">
          <div class="crm-label">
            {$form.$n.label}
          </div>
          <div class="crm-content">
            {if $n|str_starts_with:'im-'}
              {assign var="provider" value=$n|cat:"-provider_id"}
              {$form.$provider.html}&nbsp;
            {/if}
            {if $n eq 'email_greeting' or  $n eq 'postal_greeting' or $n eq 'addressee'}
              {include file="CRM/Profile/Form/GreetingType.tpl"}
            {elseif ( $n eq 'tag' && $form.tag )}
              {include file="CRM/Contact/Form/Edit/TagsAndGroups.tpl" type=$n context='' tableLayout=0}
            {elseif ( $form.$n.name eq 'image_URL' )}
              {$form.$n.html}
              {if !empty($imageURL)}
                <div class="crm-section contact_image-section">
                  <div class="content">
                    {include file="CRM/Contact/Page/ContactImage.tpl"}
                  </div>
                </div>
              {/if}
            {elseif $n|str_starts_with:'phone'}
              {assign var="phone_ext_field" value=$n|replace:'phone':'phone_ext'}
              {$form.$n.html}
              {if $form.$phone_ext_field.html}
                &nbsp;{$form.$phone_ext_field.html}
              {/if}
            {else}
              {$form.$n.html}
              {if $field.html_type eq 'Autocomplete-Select'}
                {if $field.data_type eq 'ContactReference'}
                  {include file="CRM/Custom/Form/ContactReference.tpl" element_name = $n}
                {/if}
              {/if}
            {/if}
          </div>
          <div class="clear"></div>
        </div>

        {if $form.$n.type eq 'file'}
          <div class="crm-section file_displayURL-section file_displayURL{$n}-section"><div class="content">{$customFiles.$n.displayURL}</div></div>
          <div class="crm-section file_deleteURL-section file_deleteURL{$n}-section"><div class="content">{$customFiles.$n.deleteURL}</div></div>
        {/if}
      {/if}

      {* Show explanatory text for field if not in 'view' mode *}
      {if $field.help_post && $form.$n.html}
        <div class="crm-section helprow-{$n}-section helprow-post" id="helprow-{$n}">
          <div class="content description">{$field.help_post}</div>
        </div>
      {/if}
    {/if}{* end of main if field name if *}
  {/foreach}
  {if $help_post}
    <div class="messages help">{$help_post}</div>
  {/if}
</div>
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      var $form = $('form.{/literal}{$form.formClass}{literal}');
      $('[name=is_deceased]', $form).change(function() {
        $('.editrow_deceased_date-section', $form).toggle($(this).is(':checked'));
      }).change();
      $('[name=is_deceased]', $form).change(function() {
        if ($(this).is(':checked')) {
          $('[name=deceased_date] + input', $form).focus();
        } else {
          $('[name=deceased_date]', $form).val('').change();
        }
      });
    });
  </script>
{/literal}
