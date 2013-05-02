<div class="contactCardLeft">
  <table>
  {if $nick_name}
    <tr>
      <td class="label">Nickname</td>
      <td class="content">{$nick_name}</td>
    </tr>
  {/if}
  {if $custom_60.field_value || $source}
    <tr>
      <td class="label">{if $custom_60.field_value}{$custom_60.field_title}{else}Source{/if}</td>
      <td>{$custom_60.field_value}{if $custom_60.field_value && $source}<br />{/if}
        {if $source}{$source}{/if}
      </td>
    </tr>
  {/if}
  {if $legal_name}
    <tr>
      <td class="label">{ts}Legal Name{/ts}</td>
      <td>{$legal_name}</td>
    </tr>
  {/if}
  {if $sic_code}
    <tr>
      <td class="label">{ts}SIC Code{/ts}</td>
      <td>{$sic_code}</td>
    </tr>
  {/if}
    <tr>
      <td class="label">{ts}Contact Type{/ts}
        {if $custom_42.field_value}<br />{$custom_42.field_title}{/if}
      </td>
      <td>{$contact_type_label}
        {if $custom_42.field_value}<br />{$custom_42.field_value}{/if}
      </td>
    </tr>
  </table>
</div>
<div class="contactCardRight">
  <table>
  {if $custom_58.field_value}{*Ethnicity*}
    <tr>
      <td class="label">
        {$custom_58.field_title}
      </td>
      <td>
        {$custom_58.field_value}
          {if $custom_62.field_value && $custom_58.field_value}<br />{/if}
        {$custom_62.field_value}
      </td>
    </tr>
  {/if}
  {if $gender_display}
    <tr>
      <td class="label">
        Gender
      </td>
      <td>
        {$gender_display}
          {if $custom_45.field_value && $gender_display}<br />{/if}
        {$custom_45.field_value}
      </td>
  <tr>
  {/if}
  {if $preferred_language}
    <tr>
      <td class="label">{ts}Preferred Language{/ts}</td>
      <td>{$preferred_language}</td>
    </tr>
  {/if}
  </table>
</div>
<div class="clear"></div>
