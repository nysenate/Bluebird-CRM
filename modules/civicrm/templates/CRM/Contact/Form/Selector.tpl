{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{include file="CRM/common/pager.tpl" location="top"}

{include file="CRM/common/pagerAToZ.tpl"}
<a href="#" onclick=" return toggleContactSelection( 'resetSel', 'civicrm search {$qfKey}', 'reset' );">{ts}Reset all selections{/ts}</a>

<table summary="{ts}Search results listings.{/ts}" class="selector row-highlight">
  <thead class="sticky">
    <tr>
      <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
      {if $context eq 'smog'}
          <th scope="col">
            {ts}Status{/ts}
          </th>
      {/if}
      {foreach from=$columnHeaders item=header}
        <th scope="col">
        {if $header.sort}
          {assign var='key' value=$header.sort}
          {$sort->_response.$key.link}
        {else}
          {$header.name}
        {/if}
        </th>
      {/foreach}
    </tr>
  </thead>

  {counter start=0 skip=1 print=false}

  { if $id }
      {foreach from=$rows item=row}
        <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td>
            {if $context eq 'smog'}
              {if $row.status eq 'Pending'}<td class="status-pending"}>
              {elseif $row.status eq 'Removed'}<td class="status-removed">
              {else}<td>{/if}
              {$row.status}</td>
            {/if}
            <td>{$row.contact_type}</td>
            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`&key=`$qfKey`&context=`$context`"}">{$row.sort_name}</a></td>
            {foreach from=$row item=value key=key} 
               {if ($key neq "checkbox") and ($key neq "action") and ($key neq "contact_type") and ($key neq "contact_type_orig") and ($key neq "status") and ($key neq "sort_name") and ($key neq "contact_id") and ($key neq "contact_sub_type")}
                <td>
                {if $key EQ "household_income_total" }
                    {$value|crmMoney}
		        {elseif strpos( $key, '_date' ) !== false }
                    {$value|crmDate}
                {else}
                    {$value}
                {/if}
                     &nbsp;
                 </td>
               {/if}
            {/foreach}
            <td>{$row.action|replace:'xx':$row.contact_id}</td>
        </tr>
     {/foreach}
  {else}
      {foreach from=$rows item=row}
         <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}" title="{ts}Click contact name to view a summary. Right-click anywhere in the row for an actions menu.{/ts}">
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td>
            {if $context eq 'smog'}
                {if $row.status eq 'Pending'}<td class="status-pending"}>
                {elseif $row.status eq 'Removed'}<td class="status-removed">
                {else}<td>{/if}
                {$row.status}</td>
            {/if}
            <td>{$row.contact_type}</td>	
            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`&key=`$qfKey`&context=`$context`"}">{if $row.is_deleted}<del>{/if}{$row.sort_name}{if $row.is_deleted}</del>{/if}</a></td>
            {if $action eq 512 or $action eq 256}
              {if !empty($columnHeaders.street_address)}
	        <td><span title="{$row.street_address}">{$row.street_address|mb_truncate:22:"...":true}</span></td>
	      {/if}
	      {if !empty($columnHeaders.city)}
                <td>{$row.city}</td>
	      {/if}
	      {if !empty($columnHeaders.state_province)}
                <td>{$row.state_province}</td>
              {/if}
              {if !empty($columnHeaders.postal_code)}
                <td>{$row.postal_code}</td>
              {/if}
	      {if !empty($columnHeaders.country)}
                <td>{$row.country}</td>
              {/if}
              <td>
                {if $row.email}
                    <span
                        {if $row.on_hold} class="status-hold" title="{ts}This email is on hold (probably due to bouncing).{/ts}"
                        {elseif $row.do_not_email} class="do-not-email" title="{ts}Do Not Email{/ts}"
                        {else} title="{$row.email}"{/if}>
                        {$row.email|mb_truncate:17:"...":true}
                        {if $row.on_hold}&nbsp;(On Hold){/if}
                    </span>
                {/if}
              </td>
              <td>
                {if $row.phone}
                    <span{if $row.do_not_phone} class="do-not-phone" title="{ts}Do Not Phone{/ts}" {/if}>{$row.phone}</span>
                {/if}
              </td>
           {else}
              {foreach from=$row item=value key=key}
                {if ($key neq "checkbox") and ($key neq "action") and ($key neq "contact_type") and ($key neq "contact_sub_type") and ($key neq "status") and ($key neq "sort_name") and ($key neq "contact_id")}
                 <td>{$value}&nbsp;</td>
                {/if}   
              {/foreach}
            {/if}
            <td style='width:125px;'>{$row.action|replace:'xx':$row.contact_id}</td>
         </tr>
    {/foreach}
  {/if}
</table>

<!-- Context Menu populated as per component and permission-->
<ul id="contactMenu" class="contextMenu">
{foreach from=$contextMenu item=value key=key}
  <li class="{$value.ref}"><a href="#{$value.key}">{$value.title}</a></li>
{/foreach}
</ul>
<script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 {literal}
cj(document).ready( function() {
var url         = "{/literal}{crmURL p='civicrm/contact/view/changeaction' q='reset=1&action=add&cid=changeid&context=changeaction' h=0}{literal}";
var activityUrl = "{/literal}{crmURL p='civicrm/contact/view' q="action=browse&selectedChild=activity&reset=1&cid=changeid&context=`$context`" h=0}{literal}";
var emailUrl    = "{/literal}{crmURL p='civicrm/contact/view/activity' q="atype=3&action=add&reset=1&cid=changeid&context=`$context`" h=0}{literal}";
var contactUrl  = "{/literal}{crmURL p='civicrm/contact/changeaction' q="reset=1&cid=changeid&key=`$qfKey`&context=`$context`" h=0}{literal}";
var tagUrl  = "{/literal}{crmURL p='civicrm/contact/view' q="selectedChild=tag&reset=1&cid=changeid&key=`$qfKey`" h=0}{literal}";

// Show menu when contact row is right clicked
cj(".selector tr").contextMenu({
      menu: 'contactMenu'
    }, function( action, el ) { 
        var contactId = el.attr('id').substr(5);
        switch (action) {
          case 'activity':
          case 'email':
            var qfKey = "{/literal}{$qfKey}{literal}";
            emailUrl = emailUrl+'&key='+qfKey;
            eval( 'locationUrl = '+action+'Url;');
            break;
          case 'add':
            contactId += '&action=update';
          case 'view':
            locationUrl = contactUrl.replace( /changeaction/g, action );
            break;
          case 'tag':
            locationUrl = tagUrl;
            break;
          default:
            locationUrl = url.replace( /changeaction/g, action );
            break;
        }
        eval( 'locationUrl = locationUrl.replace( /changeid/, contactId );');
        // we do not need civicrmDestination for edit and view links (edit goes to view and then search results breadcrumb is available)
        if ( action == 'add' || action == 'view') {
            window.location = locationUrl;            
        } else {
            var destination = "{/literal}{crmURL q="qfKey=`$qfKey`" h=0}{literal}";
            window.location = locationUrl + '&civicrmDestination=' + encodeURIComponent(destination);            
        }
   });
  {/literal}
  {foreach from=$selectedContactIds item=selectedContactId}{literal}
   	cj("#mark_x_{/literal}{$selectedContactId}{literal}").attr('checked', 'checked');
  {/literal} 
  {/foreach}
    
  {foreach from=$unselectedContactIds item=unselectedContactId}{literal}
        cj("#mark_x_{/literal}{$unselectedContactId}{literal}").removeAttr('checked');{/literal}
  {/foreach}

  {literal}
    var formName = "{/literal}{$form.formName}{literal}";	
    on_load_init_checkboxes(formName);
    toggleTaskAction( false );
});

cj('ul#contactMenu').mouseup( function(e){ 
   if( e.button !=0 ) {
    //when right or middle button clicked fire default right click popup
   }
});

function countSelections( ){
  var Url =  "{/literal}{crmURL p='civicrm/ajax/markSelection' h=0}{literal}";	
  var key =  'civicrm search {/literal}{$qfKey}{literal}';
  var arg =  "qfKey="+key+"&action=countSelection";
  var count = 0;
  cj.ajax({
      "url":   Url,
      "type": "POST", 
      "data":  arg,
      "async"    : false,
      "dataType": 'json',
      "success": function(data){
           count  =  data.getCount;
      }
    });

   return count;
}

function toggleContactSelection( name, qfKey, selection ){
  var Url  = "{/literal}{crmURL p='civicrm/ajax/markSelection' h=0}{literal}";	
      	  
  if ( selection == 'multiple' ) {
     var rowArr = new Array( );
     {/literal}{foreach from=$rows item=row  key=keyVal}
		 {literal}rowArr[{/literal}{$keyVal}{literal}] = '{/literal}{$row.checkbox}{literal}';
     {/literal}{/foreach}{literal}
     var elements = rowArr.join('-');      

     if ( cj('#' + name).is(':checked') ){
            cj.post( Url, { name: elements , qfKey: qfKey , variableType: 'multiple' } );
     } else {
    	   cj.post( Url, { name: elements , qfKey: qfKey , variableType: 'multiple' , action: 'unselect' } );
     }
  } else if ( selection == 'single' ) {	 
     if ( cj('#' + name).is(':checked') ){
     	   cj.post( Url, { name: name , qfKey: qfKey } );
     } else {
      	   cj.post( Url, { name: name , qfKey: qfKey , state: 'unchecked' } );
     }
  } else if ( name == 'resetSel' && selection == 'reset' ) {

     cj.post( Url, {  qfKey: qfKey , variableType: 'multiple' , action: 'unselect' } );
     {/literal}
     {foreach from=$rows item=row}{literal}
             cj("#{/literal}{$row.checkbox}{literal}").removeAttr('checked');{/literal}
     {/foreach}
     {literal}
     cj("#toggleSelect").removeAttr('checked');
     var formName = "{/literal}{$form.formName}{literal}";	
     on_load_init_checkboxes(formName);
  }
}
{/literal}
</script>
{include file="CRM/common/pager.tpl" location="bottom"}
