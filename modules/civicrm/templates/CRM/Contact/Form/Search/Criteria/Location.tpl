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
<div id="location" class="form-item">
    <table class="form-layout">
	<tr>
        <td>
		{$form.location_type.label}<br />
        {$form.location_type.html} 
        <div class="description" >
            {ts}Location search uses the PRIMARY location for each contact by default.{/ts}<br /> 
            {ts}To search by specific location types (e.g. Home, Work...), check one or more boxes above.{/ts}
        </div> 
        </td>
        <td colspan="2">{$form.street_address.label}<br />
            {$form.street_address.html|crmReplace:class:big}<br />
            {$form.city.label}<br />
            {$form.city.html}
  	    </td>	   
    </tr>
           
    <tr>
        <td>
        {if $form.postal_code.html}
		<table class="inner-table">
		   <tr>
			<td>
				{$form.postal_code.label}<br />
                {$form.postal_code.html}
			</td>
			<td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label>{ts}OR{/ts}</label>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
			<td><label>{ts}Postal Code{/ts}</label>
				{$form.postal_code_low.label|replace:'-':'<br />'}
		                &nbsp;&nbsp;{$form.postal_code_low.html|crmReplace:class:six}
                {$form.postal_code_high.label}
                		&nbsp;&nbsp;{$form.postal_code_high.html|crmReplace:class:six}
			</td>
		    </tr>
			<tr rowspan="3"><td>&nbsp;</td></tr>
			<tr>
				<td colspan="2">{$form.address_name.label}<br />
					{$form.address_name.html|crmReplace:class:medium}
				</td>        
				<td>{$form.world_region.label}<br />
					{$form.world_region.html}&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan="2">{$form.county.label}<br />
					{$form.county.html|crmReplace:class:big}&nbsp;
				</td>        
				<td>{$form.country.label}<br />
					{$form.country.html|crmReplace:class:big}&nbsp;
				</td>
			</tr>
		</table>
        {/if}&nbsp;
        </td>
        <td>{$form.state_province.label}<br />
            {$form.state_province.html|crmReplace:class:bigSelect}
        </td>
    </tr>
    {if $addressGroupTree}
        <tr>
	    <td colspan="2">
	        {include file="CRM/Custom/Form/Search.tpl" groupTree=$addressGroupTree showHideLinks=false}
            </td>
        </tr>
    {/if}
    </table>
</div>

