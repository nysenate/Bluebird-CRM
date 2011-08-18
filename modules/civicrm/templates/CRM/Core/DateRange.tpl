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
{*this is included inside the table*}
{assign var=relativeName   value=$fieldName|cat:"_relative"}
<td >{$form.$relativeName.html}</td>
<td>   
    <span id="absolute_{$relativeName}"> 
        {assign var=fromName   value=$fieldName|cat:"_from"}
        {$form.$fromName.label}
        {include file="CRM/common/jcalendar.tpl" elementName=$fromName} 
        {assign var=toName   value=$fieldName|cat:"_to"}
        {$form.$toName.label}
        {include file="CRM/common/jcalendar.tpl" elementName=$toName} 
    </span>   
            
</td>
{literal}
<script type="text/javascript">
    var val       = document.getElementById("{/literal}{$relativeName}{literal}").value;
    var fieldName = "{/literal}{$relativeName}{literal}";
    showAbsoluteRange( val, fieldName );

    function showAbsoluteRange( val, fieldName ) {
        if ( val == "0" ) {
            cj('#absolute_'+ fieldName).show();
        } else {
            cj('#absolute_'+ fieldName).hide();
        }
    }
</script>
{/literal}        
