{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{* CiviCase -  view case screen*}

{* here we are showing related cases w/ jquery dialog *}
<div class="crm-block crm-form-block crm-case-caseview-form-block">
{if $showRelatedCases} 
    <table class="report">
      <tr class="columnheader">
    	  <th>{ts}Client Name{/ts}</th>
    	  <th>{ts}Case Type{/ts}</th>
	  <th></th>
      </tr>
      
      {foreach from=$relatedCases item=row key=caseId}
      <tr>
      	 <td class="crm-case-caseview-client_name label">{$row.client_name}</td>
	 <td class="crm-case-caseview-case_type label">{$row.case_type}</td>
	 <td class="label">{$row.links}</td>
      </tr>	
      {/foreach}
   </table>

{else}
<h3>{ts}Case Summary{/ts}</h3>
    <table class="report">
	{if $multiClient}
	<tr class="crm-case-caseview-client">
		<td colspan="4" class="label">
		{ts}Clients:{/ts} 
		{foreach from=$caseRoles.client item=client name=clients}
		  <a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$client.contact_id`"}" title="view contact record">{$client.display_name}</a>{if not $smarty.foreach.clients.last}, &nbsp; {/if}
        {/foreach}
		<a href="#" title="{ts}add new client to the case{/ts}" onclick="addClient( );return false;">
			<span class="icon edit-icon"></span>
		</a>
	     {if $hasRelatedCases}
        	<div class="crm-block relatedCases-link"><a href='#' onClick='viewRelatedCases( {$caseID}, {$contactID} ); return false;'>{ts}Related Cases{/ts}</a></div>
        {/if}
        </td>
	</tr>
	{/if}
        <tr>
	    {if not $multiClient}
             <td>
    		 <table class="form-layout-compressed" border="1">
    		 {foreach from=$caseRoles.client item=client}
          	   <tr class="crm-case-caseview-display_name">
    		     <td class="label-left" style="padding: 0px">{$client.display_name}</td>
    		   </tr>
    	       {if $client.phone}
        		   <tr class="crm-case-caseview-phone">
        		     <td class="label-left description" style="padding: 0px">{$client.phone}</td>
        		   </tr>
    		   {/if}
               {if $client.birth_date}
            	   <tr class="crm-case-caseview-birth_date">
                         <td class="label-left description" style="padding: 0px">{ts}DOB{/ts}: {$client.birth_date|crmDate}</td>
                    </tr>
               {/if}
             {/foreach}
    	     </table>
    	     {if $hasRelatedCases}
             	<div class="crm-block relatedCases-link"><a href='#' onClick='viewRelatedCases( {$caseID}, {$contactID} ); return false;'>{ts}Related Cases{/ts}</a></div>
             {/if}
             </td>
	    {/if}
        <td class="crm-case-caseview-case_type label">
            <span class="crm-case-summary-label">{ts}Case Type{/ts}:</span>&nbsp;{$caseDetails.case_type}&nbsp;<a href="{crmURL p='civicrm/case/activity' q="action=add&reset=1&cid=`$contactId`&caseid=`$caseId`&selectedChild=activity&atype=`$changeCaseTypeId`"}" title="Change case type (creates activity record)"><span class="icon edit-icon"></span></a>
        </td>
        <td class="crm-case-caseview-case_status label">
            <span class="crm-case-summary-label">{ts}Status{/ts}:</span>&nbsp;{$caseDetails.case_status}&nbsp;<a href="{crmURL p='civicrm/case/activity' q="action=add&reset=1&cid=`$contactId`&caseid=`$caseId`&selectedChild=activity&atype=`$changeCaseStatusId`"}" title="Change case status (creates activity record)"><span class="icon edit-icon"></span></a>
        </td>
        <td class="crm-case-caseview-case_start_date label">
            <span class="crm-case-summary-label">{ts}Start Date{/ts}:</span>&nbsp;{$caseDetails.case_start_date|crmDate}&nbsp;<a href="{crmURL p='civicrm/case/activity' q="action=add&reset=1&cid=`$contactId`&caseid=`$caseId`&selectedChild=activity&atype=`$changeCaseStartDateId`"}" title="Change case start date (creates activity record)"><span class="icon edit-icon"></span></a>
        </td>
        <td class="crm-case-caseview-{$caseID} label">
            <span class="crm-case-summary-label">{ts}Case ID{/ts}:</span>&nbsp;{$caseID}
        </td>
    </tr>
    </table>
    {if $hookCaseSummary}
      <div id="caseSummary">
      {foreach from=$hookCaseSummary item=val key=div_id}
        <div id="{$div_id}"><label>{$val.label}</label><div class="value">{$val.value}</div></div>
      {/foreach}
      </div>
    {/if}

    <table class="form-layout">
        <tr class="crm-case-caseview-form-block-activity_type_id">
            <td>{$form.activity_type_id.label}<br />{$form.activity_type_id.html}&nbsp;<input type="button" accesskey="N" value="Go" name="new_activity" onclick="checkSelection( this );"/></td>
	    {if $hasAccessToAllCases}	
            <td>
                <span class="crm-button"><div class="icon print-icon"></div><input type="button"  value="Print Case Report" name="case_report_all" onclick="printCaseReport( );"/></span>
            </td> 
        </tr>
        <tr>
            <td class="crm-case-caseview-form-block-timeline_id">{$form.timeline_id.label}<br />{$form.timeline_id.html}&nbsp;{$form._qf_CaseView_next.html}</td> 
            <td class="crm-case-caseview-form-block-report_id">{$form.report_id.label}<br />{$form.report_id.html}&nbsp;<input type="button" accesskey="R" value="Go" name="case_report" onclick="checkSelection( this );"/></td> 
        {else}
            <td></td>
	    {/if}
        </tr>

	{if $mergeCases}
    	<tr class="crm-case-caseview-form-block-merge_case_id">
    	   <td colspan='2'><a href="#" onClick='cj("#merge_cases").toggle( ); return false;'>{ts}Merge Case{/ts}</a>	
    	        <span id='merge_cases' class='hide-block'>
    	            {$form.merge_case_id.html}&nbsp;{$form._qf_CaseView_next_merge_case.html}
    	        </span>
    	   </td>
    	</tr>
	{/if}

	{if call_user_func(array('CRM_Core_Permission','giveMeAllACLs'))}
    	<tr class="crm-case-caseview-form-block-change_client_id">
    	   <td colspan='2'><a href="#" onClick='cj("#change_client").toggle( ); return false;'>{ts}Assign to Another Client{/ts}</a>	
    	    <span id='change_client' class='hide-block'>
    	        {$form.change_client_id.html|crmReplace:class:twenty}&nbsp;{$form._qf_CaseView_next_edit_client.html}
    	    </span>
    	   </td>
    	</tr>
	{/if}
    </table>

<div id="view-related-cases">
     <div id="related-cases-content"></div>
</div>

<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-case-roles-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Case Roles{/ts}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
    <span id="restmsg" class="msgok" style="display:none"></span>
 
    {if $hasAccessToAllCases}
    <div class="crm-submit-buttons">
      <a class="button" href="#" onClick="Javascript:addRole();return false;"><span><div class="icon add-icon"></div>{ts}Add new role{/ts}</span></a>
    </div>
    {/if}

    <table class="report-layout">
    	<tr class="columnheader">
    		<th>{ts}Case Role{/ts}</th>
    		<th>{ts}Name{/ts}</th>
    	   	<th>{ts}Phone{/ts}</th>
            <th>{ts}Email{/ts}</th>
            {if $relId neq 'client' and $hasAccessToAllCases}
    		    <th>{ts}Actions{/ts}</th>
    		{/if}
    	</tr>
		{assign var=rowNumber value = 1}
        {foreach from=$caseRelationships item=row key=relId}
        <tr>
            <td class="crm-case-caseview-role-relation label">{$row.relation}</td>
            <td class="crm-case-caseview-role-name" id="relName_{$rowNumber}"><a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$row.cid`"}" title="view contact record">{$row.name}</a></td>
           
            <td class="crm-case-caseview-role-phone" id="phone_{$rowNumber}">{$row.phone}</td>
            <td class="crm-case-caseview-role-email" id="email_{$rowNumber}">{if $row.email}
            <a href="{crmURL p='civicrm/contact/view/activity' q="reset=1&action=add&atype=3&cid=`$row.cid`&caseid=`$caseID`"}" title="{ts}compose and send an email{/ts}">
            	<div class="icon email-icon" title="{ts}compose and send an email{/ts}"></div>
           	</a>{/if}
            </td>
          {if $relId neq 'client' and $hasAccessToAllCases}
            <td id ="edit_{$rowNumber}">
            	<a href="#" title="edit case role" onclick="createRelationship( {$row.relation_type}, {$row.cid}, {$relId}, {$rowNumber}, '{$row.relation}' );return false;">
            	<div class="icon edit-icon" ></div>
            	</a> &nbsp;&nbsp;
            	<a href="{crmURL p='civicrm/contact/view/rel' q="action=delete&reset=1&cid=`$contactID`&id=`$relId`&caseID=`$caseID`"}" onclick = "if (confirm('{ts}Are you sure you want to remove this person from their case role{/ts}?') ) this.href+='&confirmed=1'; else return false;">
            	<div class="icon delete-icon" title="remove contact from case role"></div>
            	</a>
            	
            </td>
          {/if}
        </tr>
		{assign var=rowNumber value = `$rowNumber+1`}
        {/foreach}

        {foreach from=$caseRoles item=relName key=relTypeID}
         {if $relTypeID neq 'client'} 
           <tr>
               <td class="crm-case-caseview-role-relName label">{$relName}</td>
               <td class="crm-case-caseview-role-relName_{$rowNumber}" id="relName_{$rowNumber}">(not assigned)</td>
               <td class="crm-case-caseview-role-phone" id="phone_{$rowNumber}"></td>
               <td class="crm-case-caseview-role-email" id="email_{$rowNumber}"></td>
	       {if $hasAccessToAllCases}               
	       <td id ="edit_{$rowNumber}">
	       <a href="#" title="edit case role" onclick="createRelationship( {$relTypeID}, null, null, {$rowNumber}, '{$relName}' );return false;">
	       	<div class="icon edit-icon"></div>
	       </a> 
	       </td>
	       {else}
	       <td></td>
	       {/if}
           </tr>
         {else}
           <tr>
               <td rowspan="{$relName|@count}" class="crm-case-caseview-role-label label">{ts}Client{/ts}</td>
	   {foreach from=$relName item=client name=clientsRoles}
               {if not $smarty.foreach.clientsRoles.first}</tr>{/if}
               <td class="crm-case-caseview-role-sort_name" id="relName_{$rowNumber}"><a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$client.contact_id`"}" title="view contact record">{$client.sort_name}</a></td>
               <td class="crm-case-caseview-role-phone" id="phone_{$rowNumber}">{$client.phone}</td>
               <td class="crm-case-caseview-role-email" id="email_{$rowNumber}">{if $client.email}<a href="{crmURL p='civicrm/contact/view/activity' q="reset=1&action=add&atype=3&cid=`$client.contact_id`&caseid=`$caseID`"}" title="{ts}compose and send an email{/ts}"><div class="icon email-icon"></div></a>&nbsp;{/if}</td>
               <td></td>
           </tr>
           {/foreach}
         {/if}
		{assign var=rowNumber value = `$rowNumber+1`}
        {/foreach}
    </table>    
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->


<div id="dialog">
     {ts}Begin typing last name of contact.{/ts}<br/>
     <input type="text" id="rel_contact"/>
     <input type="hidden" id="rel_contact_id" value="">
</div>

{literal}
<script type="text/javascript">
var selectedContact = '';
var caseID = {/literal}"{$caseID}"{literal};
var contactUrl = {/literal}"{crmURL p='civicrm/ajax/rest' q='className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=newcontact' h=0 }"{literal};
cj( "#change_client_id").autocomplete( contactUrl, { width : 250, selectFirst : false, matchContains:true
                            }).result( function(event, data, formatted) { cj( "#contact_id" ).val( data[1] ); selectedContact = data[0];
                            }).bind( 'click', function( ) { cj( "#contact_id" ).val(''); });

cj("#dialog").hide( );

function addClient( ) {
    cj("#dialog").show( );

    cj("#dialog").dialog({
        title: "Add Client to the Case",
        modal: true,
		bgiframe: true,
		close  : function(event, ui) { cj("#rel_contact").unautocomplete( ); },
		overlay: { opacity: 0.5, background: "black" },
		beforeclose: function(event, ui) { cj(this).dialog("destroy"); },

		open:function() {

			var contactUrl = {/literal}"{crmURL p='civicrm/ajax/rest' q='className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=caseview' h=0 }"{literal};

			cj("#rel_contact").autocomplete( contactUrl, {
				width: 260,
				selectFirst: false,
	                        matchContains: true 
			});
			
			cj("#rel_contact").focus();
			cj("#rel_contact").result(function(event, data, formatted) {
				cj("input[id=rel_contact_id]").val(data[1]);
			});		    
		
		},

		buttons: { "Done": function() { cj(this).dialog("close"); cj(this).dialog("destroy"); }}	
	}
	)
}

function createRelationship( relType, contactID, relID, rowNumber, relTypeName ) {
    cj("#dialog").show( );

	cj("#dialog").dialog({
		title: "Assign Case Role",
		modal: true, 
		bgiframe: true,
		close: function(event, ui) { cj("#rel_contact").unautocomplete( ); },
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},
		
		open:function() {
			/* set defaults if editing */
			cj("#rel_contact").val( "" );
			cj("#rel_contact_id").val( null );
			if ( contactID ) {
				cj("#rel_contact_id").val( contactID );
				cj("#rel_contact").val( cj("#relName_" + rowNumber).text( ) );
			}

			var contactUrl = {/literal}"{crmURL p='civicrm/ajax/rest' q='className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=caseview' h=0 }"{literal};

			cj("#rel_contact").autocomplete( contactUrl, {
				width: 260,
				selectFirst: false,
	                        matchContains: true 
			});
			
			cj("#rel_contact").focus();
			cj("#rel_contact").result(function(event, data, formatted) {
				cj("input[id=rel_contact_id]").val(data[1]);
			});		    
		},

		buttons: { 
			"Ok": function() { 	    
				if ( ! cj("#rel_contact").val( ) ) {
					alert('{/literal}{ts escape="js"}Select valid contact from the list{/ts}{literal}.');
					return false;
				}

				var sourceContact = {/literal}"{$contactID}"{literal};
				var caseID        = {/literal}"{$caseID}"{literal};

				var v1 = cj("#rel_contact_id").val( );

				if ( ! v1 ) {
					alert('{/literal}{ts escape="js"}Select valid contact from the list{/ts}{literal}.');
					return false;
				}

				var postUrl = {/literal}"{crmURL p='civicrm/ajax/relation' h=0 }"{literal};
                cj.post( postUrl, { rel_contact: v1, rel_type: relType, contact_id: sourceContact, rel_id: relID, case_id: caseID, key: {/literal}"{crmKey name='civicrm/ajax/relation'}"{literal} },
                    function( data ) {
                        var resourceBase   = {/literal}"{$config->resourceBase}"{literal};

			var html = '';			
			if ( data.status == 'process-relationship-success' ) {
                            var contactViewUrl = {/literal}"{crmURL p='civicrm/contact/view' q='action=view&reset=1&cid=' h=0 }"{literal};	
                            var deleteUrl      = {/literal}"{crmURL p='civicrm/contact/view/rel' q="action=delete&reset=1&cid=`$contactID`&caseID=`$caseID`&id=" h=0 }"{literal};	
                            var html = '<a href=' + contactViewUrl + data.cid +' title="view contact record">' +  data.name +'</a>';
                            cj('#relName_' + rowNumber ).html( html );
                            html = '';
                            html = '<a onclick="createRelationship( ' + relType +','+ data.cid +', ' + data.rel_id +', ' + rowNumber +', \''+ relTypeName +'\' ); return false" title="edit case role" href="#"><div class="icon edit-icon" ></div></a> &nbsp;&nbsp; <a href=' + deleteUrl + data.rel_id +' onclick = "if (confirm(\'{/literal}{ts escape="js"}Are you sure you want to delete this relationship{/ts}{literal}?\') ) this.href +=\'&confirmed=1\'; else return false;"><div title="remove contact from case role" class="icon delete-icon"></div></a>';
                            cj('#edit_' + rowNumber ).html( html );

			} else {
			   html = '<img src="' +resourceBase+'i/edit.png" title="edit case role" onclick="createRelationship( ' + relType +','+ data.cid +', ' + data.rel_id +', ' + rowNumber +', \''+ relTypeName +'\' );">&nbsp;&nbsp;';
			   var relTypeAdminLink = {/literal}"{crmURL p='civicrm/admin/reltype' q='reset=1' h=0 }"{literal};
			   var errorMsg = '{/literal}{ts 1="' + relTypeName + '" 2="' + relTypeAdminLink + '" }The relationship type definition for the %1 case role is not valid. Both sides of the relationship type must be an Individual or a subtype of Individual. You can review and edit relationship types at <a href="%2">Administer >> Option Lists >> Relationship Types</a>{/ts}{literal}.'; 

			   //display error message.
			   var imageIcon = "<a href='#'  onclick='cj( \"#restmsg\" ).hide( ); return false;'>" + '<div class="ui-icon ui-icon-close" style="float:left"></div>' + '</a>';
			   cj( '#restmsg' ).html( imageIcon + errorMsg  ).show( );
			}

                        html = '';
                        if ( data.phone ) {
                            html = data.phone;
                        }	
                        cj('#phone_' + rowNumber ).html( html );

                        html = '';
                        if ( data.email ) {
                            var activityUrl = {/literal}"{crmURL p='civicrm/contact/view/activity' q="atype=3&action=add&reset=1&caseid=`$caseID`&cid=" h=0 }"{literal};
                            html = '<a href=' + activityUrl + data.cid + '><div title="compose and send an email" class="icon email-icon"></div></a>&nbsp;';
                        } 
                        cj('#email_' + rowNumber ).html( html );

                        }, 'json' 
                    );

				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			},

			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 

	});
}

function viewRelatedCases( mainCaseID, contactID ) {
  cj("#view-related-cases").show( );
     cj("#view-related-cases").dialog({
        title: "Related Cases",
        modal: true, 
        width : "680px", 
        height: 'auto', 
        resizable: true,
        bgiframe: true,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

        open:function() {

	    var dataUrl = {/literal}"{crmURL p='civicrm/contact/view/case' h=0 q="snippet=4" }"{literal};
	    dataUrl = dataUrl + '&id=' + mainCaseID + '&cid=' +contactID + '&relatedCases=true&action=view&context=case&selectedChild=case';

	     cj.ajax({ 
             	       url     : dataUrl,   
        	       async   : false,
        	       success : function(html){
            	       	         cj("#related-cases-content" ).html( html );
        		         }
    	     });   
        },

        buttons: { 
            "Done": function() { 	    
                cj(this).dialog("close"); 
                cj(this).dialog("destroy");
            }
        }
    });
}

cj(document).ready(function(){
   cj("#view-activity").hide( );
});
</script>
{/literal}

{if $hasAccessToAllCases}
<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-case-other-relationships-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Other Relationships{/ts}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  
  {if $clientRelationships}
    <div class="crm-submit-buttons">
    <a class="button" href="#" onClick="window.location='{crmURL p='civicrm/contact/view/rel' q="action=add&reset=1&cid=`$contactId`&caseID=`$caseID`"}'; return false;">
    <span><div class="icon add-icon"></div>{ts}Add client relationship{/ts}</a></span>
    </div>
	
    <table class="report-layout otherRelationships">
    	<tr class="columnheader">
    		<th>{ts}Client Relationship{/ts}</th>
    		<th>{ts}Name{/ts}</th>
    		<th>{ts}Phone{/ts}</th>
    		<th>{ts}Email{/ts}</th>
    	</tr>
        {foreach from=$clientRelationships item=row key=relId}
        <tr id="otherRelationship-{$row.cid}">
            <td class="crm-case-caseview-otherrelationship-relation label">{$row.relation}</td>
            <td class="crm-case-caseview-otherrelationship-name" id="relName_{$rowNumber}"><a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$row.cid`"}" title="view contact record">{$row.name}</a></td>
            <td class="crm-case-caseview-otherrelationship-phone" id="phone_{$rowNumber}">{$row.phone}</td>
	        <td class="crm-case-caseview-otherrelationship-email" id="email_{$rowNumber}">{if $row.email}<a href="{crmURL p='civicrm/contact/view/activity' q="reset=1&action=add&atype=3&cid=`$row.cid`&caseid=`$caseID`"}" title="{ts}compose and send an email{/ts}"><div class="icon email-icon"></div></a>&nbsp;{/if}</td>
        </tr>
		{assign var=rowNumber value = `$rowNumber+1`}
        {/foreach}
    </table>
  {else}
    <div class="messages status">
      <div class="icon inform-icon"></div>
          {capture assign=crmURL}{crmURL p='civicrm/contact/view/rel' q="action=add&reset=1&cid=`$contactId`&caseID=`$caseID`"}{/capture}
          {ts 1=$crmURL}There are no Relationships entered for this client. You can <a accesskey="N" href='%1'>add one</a>.{/ts}
    </div>
  {/if}

  <br />
  
  {if $globalRelationships}
    <div class="crm-submit-buttons">
        <a class="button" href="#"  onClick="window.location='{crmURL p='civicrm/group/search' q="reset=1&context=amtg&amtgID=`$globalGroupInfo.id`"}'; return false;">
        <span><div class="icon add-icon"></div>{ts 1=$globalGroupInfo.title}Add members to %1{/ts}</a></span>
    </div>
	
    <table class="report-layout globalrelationship">
    	<tr class="columnheader">
    		<th>{$globalGroupInfo.title}</th>
    		<th>{ts}Phone{/ts}</th>
    		<th>{ts}Email{/ts}</th>
    	</tr>
        {foreach from=$globalRelationships item=row key=relId}
        <tr id="caseResource-{$row.contact_id}">
            <td class="crm-case-caseview-globalrelationship-sort_name label" id="relName_{$rowNumber}"><a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$row.contact_id`"}" title="view contact record">{$row.sort_name}</a></td>
            <td class="crm-case-caseview-globalrelationship-phone" id="phone_{$rowNumber}">{$row.phone}</td>
	    <td class="crm-case-caseview-globalrelationship-email" id="email_{$rowNumber}">{if $row.email}<a href="{crmURL p='civicrm/contact/view/activity' q="reset=1&action=add&atype=3&cid=`$row.contact_id`&caseid=`$caseID`"}" title="{ts}compose and send an email{/ts}"><div title="compose and send an email" class="icon email-icon"></div></a>&nbsp;{/if}</td>
        </tr>
		{assign var=rowNumber value = `$rowNumber+1`}
        {/foreach}
    </table>
  {elseif $globalGroupInfo.id}
    <div class="messages status">
      <div class="icon inform-icon"></div>&nbsp;        
          {capture assign=crmURL}{crmURL p='civicrm/group/search' q="reset=1&context=amtg&amtgID=`$globalGroupInfo.id`"}{/capture}
          {ts 1=$crmURL 2=$globalGroupInfo.title}The group %2 has no members. You can <a href='%1'>add one</a>.{/ts}
    </div>
  {/if}

 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{/if} {* other relationship section ends *} 

<div id="addRoleDialog">
{$form.role_type.label}<br />
{$form.role_type.html}
<br /><br />
    {ts}Begin typing last name of contact.{/ts}<br/>
    <input type="text" id="role_contact"/>
    <input type="hidden" id="role_contact_id" value="">
</div>

{literal}
<script type="text/javascript">

cj("#addRoleDialog").hide( );
function addRole() {
    cj("#addRoleDialog").show( );

	cj("#addRoleDialog").dialog({
		title: "Add Role",
		modal: true,
		bgiframe: true, 
		close: function(event, ui) { cj("#role_contact").unautocomplete( ); },
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},

        
		open:function() {
			/* set defaults if editing */
			cj("#role_contact").val( "" );
			cj("#role_contact_id").val( null );

			var contactUrl = {/literal}"{crmURL p='civicrm/ajax/rest' q='className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=caseview' h=0 }"{literal};

			cj("#role_contact").autocomplete( contactUrl, {
				width: 260,
				selectFirst: false,
				matchContains: true 
			});
			
			cj("#role_contact").focus();
			cj("#role_contact").result(function(event, data, formatted) {
				cj("input[id=role_contact_id]").val(data[1]);
			});		    
		},

		buttons: { 
			"Ok": function() { 	    
				if ( ! cj("#role_contact").val( ) ) {
					alert('{/literal}{ts escape="js"}Select valid contact from the list{/ts}{literal}.');
					return false;
				}

				var sourceContact = {/literal}"{$contactID}"{literal};
				var caseID        = {/literal}"{$caseID}"{literal};
				var relID         = null;

				var v1 = cj("#role_contact_id").val( );

				if ( ! v1 ) {
					alert('{/literal}{ts escape="js"}Select valid contact from the list{/ts}{literal}.');
					return false;
				}

				var v2 = cj("#role_type").val();
				if ( ! v2 ) {
					alert('{/literal}{ts escape="js"}Select valid type from the list{/ts}{literal}.');
					return false;
				}
				
               /* send synchronous request so that disabling any actions for slow servers*/
				var postUrl = {/literal}"{crmURL p='civicrm/ajax/relation' h=0 }"{literal}; 
				var data = 'rel_contact='+ v1 + '&rel_type='+ v2 + '&contact_id='+sourceContact + '&rel_id='+ relID + '&case_id=' + caseID + "&key={/literal}{crmKey name='civicrm/ajax/relation'}{literal}";
                		cj.ajax({ type     : "POST", 
					  url      : postUrl, 
					  data     : data, 
					  async    : false,
					  dataType : "json",
					  success  : function( values ) {
					  	    	if ( values.status == 'process-relationship-success' ) {
               						     window.location.reload();
							} else {
							     var relTypeName = cj("#role_type :selected").text();  
							     var relTypeAdminLink = {/literal}"{crmURL p='civicrm/admin/reltype' q='reset=1' h=0 }"{literal};
			  				     var errorMsg = '{/literal}{ts 1="' + relTypeName + '" 2="' + relTypeAdminLink + '"  }The relationship type definition for the %1 case role is not valid. Both sides of the relationship type must be an Individual or a subtype of Individual. You can review and edit relationship types at <a href="%2">Administer >> Option Lists >> Relationship Types</a>{/ts}{literal}.'; 

			   				     //display error message.
			   				     var imageIcon = "<a href='#'  onclick='cj( \"#restmsg\" ).hide( ); return false;'>" + '<div class="ui-icon ui-icon-close" style="float:left"></div>' + '</a>';
			   				     cj( '#restmsg' ).html( imageIcon + errorMsg  ).show( );  
							}
					  	    }
				       });
				       cj(this).dialog("close"); 
				       cj(this).dialog("destroy");
 			},

			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 

	});
}

</script>
{/literal}
{include file="CRM/Case/Form/ActivityToCase.tpl"}

{* pane to display / edit regular tags or tagsets for cases *}
{if $showTags OR $showTagsets }

<div id="casetags" class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-open crm-case-tags-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
  {ts}Case Tags{/ts}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  {if $tags}
    <div class="crm-block crm-content-block crm-case-caseview-display-tags">{$tags}</div>
  {/if}

  {foreach from=$tagset item=displayTagset}
      {if $displayTagset.entityTagsArray}
          <div class="crm-block crm-content-block crm-case-caseview-display-tagset">
              &nbsp;&nbsp;{$displayTagset.parentName}:
              {foreach from=$displayTagset.entityTagsArray item=val name="tagsetList"}
                  &nbsp;{$val.name}{if !$smarty.foreach.tagsetList.last},{/if}
              {/foreach}
          </div>
      {/if}
  {/foreach}

  {if !tags and !$displayTagset.entityTagsArray }
    <div class="status">
        {ts}There are no tags currently assigend to this case.{/ts}
    </div>
  {/if}

  <div class="crm-submit-buttons"><input type="button" class="form-submit" onClick="javascript:addTags()" value={if $tags || $displayTagset.entityTagsArray}"{ts}Edit Tags{/ts}"{else}"{ts}Add Tags{/ts}"{/if} /></div>

 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

    <div id="manageTags">
        <div class="label">{$form.case_tag.label}</div>
        <div class="view-value"><div class="crm-select-container">{$form.case_tag.html}</div>
        <div style="text-align:left;">{include file="CRM/common/Tag.tpl"}</div>
    </div>
    </div>

{literal}
<script type="text/javascript">
cj("select[multiple]").crmasmSelect({
    addItemTarget: 'bottom',
    animate: true,
    highlight: true,
    sortable: true,
    respectParents: true
});

cj("#manageTags").hide( );
function addTags() {
    cj("#manageTags").show( );

    cj("#manageTags").dialog({
        title: "Change Case Tags",
        modal: true,
        bgiframe: true,
        width : 450,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

        open:function() {
            /* set defaults if editing */
        },

        buttons: { 
            "Save": function() { 
                var tagsChecked = '';	    
                var caseID      = {/literal}{$caseID}{literal};	

                cj("#manageTags #tags option").each( function() {
                    if ( cj(this).attr('selected') == true) {
                        if ( !tagsChecked ) {
                            tagsChecked = cj(this).val() + '';
                        } else {
                            tagsChecked = tagsChecked + ',' + cj(this).val();
                        }
                    }
                });
                
                var tagList = '';
                cj("#manageTags input[name^=taglist]").each( function( ) {
                    if ( !tagsChecked ) {
                        tagsChecked = cj(this).val() + '';
                    } else {
                        tagsChecked = tagsChecked + ',' + cj(this).val();
                    }
                });
                
                var postUrl = {/literal}"{crmURL p='civicrm/case/ajax/processtags' h=0 }"{literal}; 
                var data = 'case_id=' + caseID + '&tag=' + tagsChecked + '&key=' + {/literal}"{crmKey name='civicrm/case/ajax/processtags'}"{literal};

                cj.ajax({ type: "POST", url: postUrl, data: data, async: false });
                cj(this).dialog("close"); 
                cj(this).dialog("destroy");

                // Temporary workaround for problems with SSL connections being too
                // slow. The relationship doesn't get created because the page reload
                // happens before the ajax call.
                // In general this reload needs improvement, which is already on the list for phase 2.
                var sdate = (new Date()).getTime();
                var curDate = sdate;
                while(curDate-sdate < 2000) {
                    curDate = (new Date()).getTime();
                }
                
                //due to caching issues we use redirection rather than reload
                document.location = {/literal}'{crmURL q="action=view&reset=1&id=$caseID&cid=$contactID&context=$context" h=0 }'{literal};
            },

            "Cancel": function() { 
                cj(this).dialog("close"); 
                cj(this).dialog("destroy"); 
            } 
        } 

    });
}
    
</script>
{/literal}

{/if} {* end of tag block*}

{*include activity view js file*}
{include file="CRM/common/activityView.tpl"}

<div class="crm-accordion-wrapper crm-case_activities-accordion crm-accordion-open crm-case-activities-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
{ts}Case Activities{/ts}
 </div><!-- /.crm-accordion-header -->
 <div id="activities" class="crm-accordion-body">
    <span id='fileOnCaseStatusMsg' style="display:none;"></span><!-- Displays status from copy to case -->
<div id="view-activity">
     <div id="activity-content"></div>
</div>


  <div>
<div class="crm-accordion-wrapper crm-accordion-inner crm-search_filters-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Search Filters{/ts}</a>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

  <table class="no-border form-layout-compressed" id="searchOptions">
    <tr>
        <td class="crm-case-caseview-form-block-repoter_id"colspan="2"><label for="reporter_id">{ts}Reporter/Role{/ts}</label><br />
            {$form.reporter_id.html|crmReplace:class:twenty}
        </td>
        <td class="crm-case-caseview-form-block-status_id"><label for="status_id">{$form.status_id.label}</label><br />
            {$form.status_id.html}
        </td>
	<td style="vertical-align: bottom;">
		<span class="crm-button"><input class="form-submit default" name="_qf_Basic_refresh" value="Search" type="button" onclick="buildCaseActivities( true )"; /></span>
	</td>
    </tr>
    <tr>
        <td class="crm-case-caseview-form-block-activity_date_low">
	    {$form.activity_date_low.label}<br />
            {include file="CRM/common/jcalendar.tpl" elementName=activity_date_low}
        </td>
        <td class="crm-case-caseview-form-block-activity_date_high"> 
            {$form.activity_date_high.label}<br /> 
            {include file="CRM/common/jcalendar.tpl" elementName=activity_date_high}
        </td>
        <td class="crm-case-caseview-form-block-activity_type_filter_id">
            {$form.activity_type_filter_id.label}<br />
            {$form.activity_type_filter_id.html}
        </td>
    </tr>
    {if $form.activity_deleted}    
    	<tr class="crm-case-caseview-form-block-activity_deleted">
	     <td>
		 {$form.activity_deleted.html}{$form.activity_deleted.label}
	     </td>
	</tr>
	{/if}
  </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
 
  <table id="activities-selector"  class="nestedActivitySelector">
  <thead><tr class="columnheader">
  <th class='crm-case-activities-date'>{ts}Date{/ts}</th>
  <th class='crm-case-activities-subject'>{ts}Subject{/ts}</th>
  <th class='crm-case-activities-type'>{ts}Type{/ts}</th>
  <th class='crm-case-activities-with'>{ts}With{/ts}</th>
  <th class='crm-case-activities-assignee'>{ts}Reporter / Assignee{/ts}</th>
  <th class='crm-case-activities-status'>{ts}Status{/ts}</th>
  <th class='crm-case-activities-status' id="nosort">&nbsp;</th>
  <th class='hiddenElement'>&nbsp;</th>
  </tr></thead>
  </table>

 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->



{literal}
<script type="text/javascript">
var oTable;

function checkSelection( field ) {
    var validationMessage = '';
    var validationField   = '';
    var successAction     = '';
    var forceValidation   = false;
   
    var clientName = new Array( );
    clientName = selectedContact.split('::');
    var fName = field.name;

    switch ( fName )  {
        case '_qf_CaseView_next' :
            validationMessage = '{/literal}{ts escape="js"}Please select an activity set from the list.{/ts}{literal}';
            validationField   = 'timeline_id';
            successAction     = "confirm('{/literal}{ts escape='js'}Are you sure you want to add a set of scheduled activities to this case?{/ts}{literal}');";
            break;

        case 'new_activity' :
            validationMessage = '{/literal}{ts escape="js"}Please select an activity type from the list.{/ts}{literal}';
            validationField   = 'activity_type_id';
            if ( document.getElementById('activity_type_id').value == 3 ) {
                successAction     = "window.location='{/literal}{$newActivityEmailUrl}{literal}' + document.getElementById('activity_type_id').value";            
            } else {
                successAction     = "window.location='{/literal}{$newActivityUrl}{literal}' + document.getElementById('activity_type_id').value";                
            }
            break;

        case 'case_report' :
            validationMessage = '{/literal}{ts escape="js"}Please select a report from the list.{/ts}{literal}';
            validationField   = 'report_id';
            successAction     = "window.location='{/literal}{$reportUrl}{literal}' + document.getElementById('report_id').value";
            break;
 
        case '_qf_CaseView_next_merge_case' :
            validationMessage = '{/literal}{ts escape="js"}Please select a case from the list to merge with.{/ts}{literal}';
            validationField   = 'merge_case_id';
            break;
        case '_qf_CaseView_next_edit_client' :
            validationMessage = '{/literal}{ts escape="js"}Please select a client for this case.{/ts}{literal}';
	    if ( cj('#contact_id').val( ) == '{/literal}{$contactID}{literal}' ) {
	       	forceValidation = true;
                validationMessage = '{/literal}{ts 1="'+clientName[0]+'"}%1 is already assigned to this case. Please select some other client for this case.{/ts}{literal}';
            }
            validationField   = 'change_client_id';
	    successAction     = "confirm( '{/literal}{ts 1="'+clientName[0]+'"}Are you sure you want to reassign this case and all related activities and relationships to %1?{/ts}{literal}' )";
            break;   	    
    }	

    if ( forceValidation || ( document.getElementById( validationField ).value == '' ) ) {
        alert( validationMessage );
        return false;
    } else if ( successAction ) {
        return eval( successAction );
    }
}

cj( function ( ) {
   buildCaseActivities( false );
});

function buildCaseActivities( filterSearch ) {
	if( filterSearch ) {
	    oTable.fnDestroy();
 	}
	var count   = 0; 
	var columns = '';
	var sourceUrl = {/literal}"{crmURL p='civicrm/ajax/activity' h=0 q='snippet=4&caseID='}{$caseID}"{literal};
            sourceUrl = sourceUrl + '&cid={/literal}{$contactID}{literal}';
            sourceUrl = sourceUrl + '&userID={/literal}{$userID}{literal}';   

        cj('#activities-selector th').each( function( ) {
          if ( cj(this).attr('id') != 'nosort' ) {
	    columns += '{"sClass": "' + cj(this).attr('class') +'"},';
	  } else {
	    columns += '{ "bSortable": false },';
	  }
	  count++; 
	});

	columns    = columns.substring(0, columns.length - 1 );
	eval('columns =[' + columns + ']');

 	oTable = cj('#activities-selector').dataTable({
    	        "bFilter"    : false,
		"bAutoWidth" : false,
                "aaSorting"  : [],
		"aoColumns"  : columns,
	    	"bProcessing": true,
		"sPaginationType": "full_numbers",
		"sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',	
	   	"bServerSide": true,
	   	"sAjaxSource": sourceUrl,
		"fnDrawCallback": function() { setSelectorClass(); },
		"fnServerData": function ( sSource, aoData, fnCallback ) {

				if ( filterSearch ) {
				var activity_deleted = 0;
    				if ( cj("#activity_deleted:checked").val() == 1 ) {
        			   activity_deleted = 1;
    			        }
				aoData.push(	     
				{name:'status_id', value: cj("select#status_id").val()},
				{name:'activity_type_id', value: cj("select#activity_type_filter_id").val()},
				{name:'activity_date_low', value: cj("#activity_date_low").val()},
				{name:'activity_date_high', value: cj("#activity_date_high").val() },
				{name:'activity_deleted', value: activity_deleted }
				);	
				}		
				cj.ajax( {
			 	"dataType": 'json', 
				"type": "POST", 
				"url": sSource, 
				"data": aoData, 
				"success": fnCallback
				} ); 
				}
     		}); 

}

function setSelectorClass( ) {
    cj("#activities-selector td:last-child").each( function( ) {
       cj(this).parent().addClass(cj(this).text() );
    });
}

function printCaseReport( ){
 
 	var dataUrl = {/literal}"{crmURL p='civicrm/case/report/print'}"{literal};
 	dataUrl     = dataUrl+ '&all=1&cid={/literal}{$contactID}{literal}' 
                      +'&caseID={/literal}{$caseID}{literal}';
        window.location = dataUrl;
}
	
</script>
{/literal}

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>

{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}


</div>

{/if} {* view related cases if end *}
</div>
