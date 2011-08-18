{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
{* template for search builder *}
 <div id="map-field">
  {strip}
     {section start=1 name=blocks loop=$blockCount}
       {assign var="x" value=$smarty.section.blocks.index}
       <div class="crm-search-block">
    <h3>{if $x eq 1}{ts}Include contacts where{/ts}{else}{ts}Also include contacts where{/ts}{/if}</h3>
	<table>
        {section name=cols loop=$columnCount[$x]}
            {assign var="i" value=$smarty.section.cols.index}
            <tr>
                <td class="form-item even-row">
                    {$form.mapper[$x][$i].html}
                    {$form.operator[$x][$i].html}
                    &nbsp;&nbsp;{$form.value[$x][$i].html}
                </td>
            </tr>
        {/section}
    
         <tr>
           <td class="form-item even-row underline-effect">
               {$form.addMore[$x].html}
           </td>
         </tr>            
       </table>
      </div>
    {/section}
    <div class="underline-effect">{$form.addBlock.html}</div> 
  {/strip}
 </div>
