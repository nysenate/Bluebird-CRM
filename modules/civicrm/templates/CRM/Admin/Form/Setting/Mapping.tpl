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
<div class="crm-block crm-form-block crm-map-form-block">
<div id="help">
    {ts}CiviCRM includes plugins for Google and Yahoo mapping services which allow your users to display contact addresses on a map. To enable this feature, select your mapping provider and obtain a 'key' for your site from that provider.{/ts} {help id='map-key'}
</div>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout-compressed">
         <tr class="crm-map-form-block-mapProvider">
             <td>{$form.mapProvider.label}</td>
             <td>{$form.mapProvider.html}<br />
             <span class="description">{ts}Choose the provider that has the best coverage for the majority of your contact addresses.{/ts}</span></td>
         </tr>
         <tr class="crm-map-form-block-mapAPIKey">
             <td>{$form.mapAPIKey.label}</td>
             <td>{$form.mapAPIKey.html|crmReplace:class:huge}<br />
             <span class="description">{ts}Enter your Google API Key OR your Yahoo Application ID.{/ts} {help id='map-key2'}</span></td>
         </tr>
    </table>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
