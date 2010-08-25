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
{* Check for Status message for the page (stored in session->getStatus). Status is cleared on retrieval. *}

{if $session->getStatus(false)}
    {assign var="status" value=$session->getStatus(true)}
    <div class="messages status">
    	<div class="icon inform-icon"></div>&nbsp;
        {if is_array($status)}
            {foreach name=statLoop item=statItem from=$status}
                {if $smarty.foreach.statLoop.first}
                    {if $statItem}<h3>{$statItem}</h3><div class='spacer'></div>{/if}
                {else}               
                   <ul><li>{$statItem}</li></ul>
                {/if}                
            {/foreach}
        {else}
            {$status}
        {/if}
    </div>
{/if}

{if ! $urlIsPublic AND $config->debug}
    <div class="messages status">
      <div class="icon inform-icon"></div>
        &nbsp;{ts}WARNING: Debug is currently enabled in Global Settings.{/ts} {docURL page="Debugging"}
    </div>
{/if}
