<div class="contactCardRight">
    {if $contact_type eq 'Individual' AND $showDemographics}
    <table>
        <tr>
            <td class="label">{ts}Gender{/ts}</td><td>{$gender_display}</td>
        </tr>
        <tr>
            <td class="label">{ts}Date of birth{/ts}</td><td>
            {if $birthDateViewFormat}	 
                {$birth_date_display|crmDate:$birthDateViewFormat}
            {else}
                {$birth_date_display|crmDate}</td>
            {/if} 
        </tr>
        <tr>
        {if $is_deceased eq 1}
           {if $deceased_date}<td class="label">{ts}Date Deceased{/ts}</td>
             <td>
             {if $birthDateViewFormat}          
		{$deceased_date_display|crmDate:$birthDateViewFormat}
             {else}
                {$deceased_date_display|crmDate}
             {/if}
             </td>
           {else}<td class="label" colspan=2><span class="font-red upper">{ts}Contact is Deceased{/ts}</span></td>
        {/if}
         {else}
            <td class="label">{ts}Age{/ts}</td>
            <td>{if $age.y}{ts count=$age.y plural='%count years'}%count year{/ts}{elseif $age.m}{ts count=$age.m plural='%count months'}%count month{/ts}{/if} </td>
         {/if}

  {foreach from=$demographics_viewCustomData item=customValues key=customGroupId}
      {foreach from=$customValues item=cd_edit key=cvID}
           {foreach from=$cd_edit.fields item=element key=field_id}
               {include file="CRM/Contact/Page/View/CustomDataFieldView.tpl"}
           {/foreach}
      {/foreach}
  {/foreach}

    </table>
  {/if}
</div><!-- #contactCardRight -->
