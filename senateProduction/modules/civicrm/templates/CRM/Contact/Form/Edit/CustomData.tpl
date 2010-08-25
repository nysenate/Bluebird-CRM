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

<script type="text/javascript">var showTab = Array( );</script>

{foreach from=$groupTree item=cd_edit key=group_id}    
	<div class="crm-accordion-wrapper crm-address-accordion crm-accordion-closed">
		<div class="crm-accordion-header">
			<div id="custom{$group_id}" class="icon crm-accordion-pointer"></div> 
			{$cd_edit.title}
			</div><!-- /.crm-accordion-header -->
			
			<div id="customData{$group_id}" class="crm-accordion-body">
				{include file="CRM/Custom/Form/CustomData.tpl" formEdit=true}
			</div>
		<script type="text/javascript">
			{if $cd_edit.collapse_display eq 0 }
				var eleSpan          = "span#custom{$group_id}";
				var eleDiv           = "div#customData{$group_id}";
				showTab[{$group_id}] = {literal}{"spanShow":eleSpan,"divShow":eleDiv}{/literal};
			{else}
				showTab[{$group_id}] = {literal}{"spanShow":""}{/literal};
			{/if}
		</script>
	</div>
{/foreach}

{include file="CRM/common/customData.tpl"}
