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
{if $form.address.$blockId.geo_code_1 && $form.address.$blockId.geo_code_2}
    {capture assign=docLink}{docURL page="Mapping and Geocoding"}{/capture}
   <tr>
      <td colspan="2">
          {$form.address.$blockId.geo_code_1.label},&nbsp;{$form.address.$blockId.geo_code_2.label}<br />
          {$form.address.$blockId.geo_code_1.html},&nbsp;{$form.address.$blockId.geo_code_2.html}<br />
          <span class="description font-italic">
            {ts}Latitude and longitude may be automatically populated by enabling a Mapping Provider.{/ts} {$docLink}
          </span>
      </td>
   </tr>
{/if}