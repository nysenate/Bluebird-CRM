{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
{capture assign=expandIconURL}<img src="{$config->resourceBase}i/TreePlus.gif" alt="{ts}open section{/ts}"/>{/capture}
{strip}

{*NYSS 5340*}
<table id="clientRelationships-selector-{$context}" class="report-layout">
  <thead>
  <tr class="columnheader">
    <th class="control"></th>
    <th>{ts}Contact{/ts}</th>
    <th>{ts}Subject{/ts}</th>
    <th>{ts}Status{/ts}</th>
    <th>{ts}Type{/ts}</th>
    <th>{ts}My Role{/ts}</th>
    <th class="nosort">{ts}Manager{/ts}</th>
    {*NYSS*}
    {if $list neq 'allcases'}
      <th class="nosort">{if $context eq 'dashboard' && $list eq 'upcoming'}Next/Recent{elseif $list eq 'upcoming'}{ts}Next Sched.{/ts}{elseif $list EQ 'recent'}{ts}Most Recent{/ts}{/if}</th>
    {/if}
    <th class="nosort"></th>
  </tr>
  </thead>
</table>

<table class="caseSelector" style="display:none">
  <tr class="columnheader">
    <th></th>
    <th>{ts}Contact{/ts}</th>
    <th>{ts}Subject{/ts}</th>
    <th>{ts}Status{/ts}</th>
    <th>{ts}Type{/ts}</th>
    <th>{ts}My Role{/ts}</th>
    <th>{ts}Manager{/ts}</th>
    {*NYSS*}
    {if $list neq 'allcases'}
      <th>{if $list EQ 'upcoming'}{ts}Next Sched.{/ts}{elseif $list EQ 'recent'}{ts}Most Recent{/ts}{/if}</th>
    {/if}
    <th></th>
  </tr>

  {counter start=0 skip=1 print=false}
  {foreach from=$rows item=row}

  <tr id='{$context}-{$list}-rowid-{$row.case_id}' class="crm-case crm-case_{$row.case_id}">
    <td>
      {* &nbsp;{$row.contact_type_icon}<br /> *}
      <span id="{$context}{$list}{$row.case_id}_show">
      <a href="#" onclick="{$context}{$list}CaseDetails('{$row.case_id}','{$row.contact_id}', '{$list}', '{$context}');
        showCaseActivities('{$row.case_id}','{$list}', '{$context}'); return false;">
        <img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/>
      </a>
      </span>
      <span id="minus{$context}{$list}{$row.case_id}_hide">
      <a href="#" onclick="hideCaseActivities('{$row.case_id}','{$list}', '{$context}'); return false;">
        <img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}open section{/ts}"/>
      </a>
    </td>

    <td class="crm-case-phone"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a>{if $row.phone}<br /><span class="description">{$row.phone}</span>{/if}<br /><span class="description">{ts}Case ID{/ts}: {$row.case_id}</span></td>
    <td class="crm-case-case_subject">{$row.case_subject}</td>
    <td class="{$row.class} crm-case-case_status">{$row.case_status}</td>
    <td class="crm-case-case_type">{$row.case_type}</td>
    <td class="crm-case-case_role">{if $row.case_role}{$row.case_role}{else}---{/if}</td>
    <td class="crm-case-casemanager">xyz{if $row.casemanager_id}<a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.casemanager_id`"}">{$row.casemanager}</a>{else}---{/if}</td>

    {*NYSS*}
    {if $list neq 'allcases'}
      {if $list eq 'upcoming'}
        <td class="crm-case-case_scheduled_activity">
          {if $row.case_upcoming_activity_viewable}
            <a href="#" onclick="{$list}viewActivity({$row.case_scheduled_activity_id}, {$row.contact_id}, '{$list}'); return false;" title="{ts}View this activity.{/ts}">{$row.case_scheduled_activity_type}</a>
          {else}
            {$row.case_scheduled_activity_type}
          {/if}
          &nbsp;&nbsp;
          {if $row.case_upcoming_activity_editable}
            <a href="{crmURL p="civicrm/case/activity" q="reset=1&cid=`$row.contact_id`&caseid=`$row.case_id`&action=update&id=`$row.case_scheduled_activity_id`"}" title="{ts}Edit this activity.{/ts}"><span class="icon edit-icon"></span></a>
          {/if}
          <br />
          {$row.case_scheduled_activity_date|crmDate}
        </td>

      {elseif $list eq 'recent'}
        <td class="crm-case-case_recent_activity">
          {if $row.case_recent_activity_viewable}
            <a href="#" onclick="{$list}viewActivity({$row.case_recent_activity_id}, {$row.contact_id}, '{$list}'); return false;" title="{ts}View this activity.{/ts}">{$row.case_recent_activity_type}</a>
          {else}
            {$row.case_recent_activity_type}
          {/if}
          {if $row.case_recent_activity_editable and $row.case_recent_activity_type_name != 'Inbound Email' && $row.case_recent_activity_type_name != 'Email'}&nbsp;&nbsp;<a href="{crmURL p="civicrm/case/activity" q="reset=1&cid=`$row.contact_id`&caseid=`$row.case_id`&action=update&id=`$row.case_recent_activity_id`"}" title="{ts}Edit this activity.{/ts}"><span class="icon edit-icon"></span></a>
          {/if}<br />
          {$row.case_recent_activity_date|crmDate}
        </td>
      {/if}
    {/if}{*NYSS end allcases condition*}

    <td>{$row.action}{$row.moreActions}</td>
  </tr>
  <tr id="{$list}{$row.case_id}_hide" class="crm-case_{$row.case_id}">
     <td></td>
     <td colspan="8" width="99%" class="enclosingNested crm-case_{$row.case_id}">
        <div id="{$context}-{$list}-casedetails-{$row.case_id}"></div>{*NYSS 4394*}
     </td>
  </tr>

  <script type="text/javascript">
    cj('#minus{$context}{$list}{$row.case_id}_hide').hide();
  </script>
  {/foreach}

  {* Dashboard only lists 10 most recent casess. *}
  {if $context EQ 'dashboard' and $limit and $pager->_totalItems GT $limit }
    <tr class="even-row">
      <td colspan="10"><a href="{crmURL p='civicrm/case/search' q='reset=1'}">&raquo; {ts}Find more cases{/ts}... </a></td>
    </tr>
  {/if}

</table>

{*include activity view js file*}
{include file="CRM/common/activityView.tpl" list=$list}
<div id="view-activity-{$list}">
  <div id="activity-content-{$list}"></div>
</div>
{/strip}

{* Build case details*}
{literal}
<script type="text/javascript">
function CaseDetails( caseId, contactId, type, context ) {
  var dataUrl = {/literal}"{crmURL p='civicrm/case/details' h=0 q='snippet=4&caseId='}{literal}" + caseId +'&cid=' + contactId + '&type=' + type;
  cj.ajax({
    url     : dataUrl,
    dataType: "html",
    timeout : 5000, //Time in milliseconds
    success : function( data ){
      //cj( '#'+ context + '-' + type +'-casedetails-' + caseId ).html( data );
      cj( 'div.innerDetails' ).html( data );
    },
    error   : function( XMLHttpRequest, textStatus, errorThrown ) {
      console.error( 'Error: '+ textStatus );
    }
  });
}

function showCaseActivities( caseId, type, context ) {
  cj('#' + context + '-' + type +'-casedetails-'+ caseId).show();
  cj('#' + context+type+caseId+'_show').hide();
  cj('#minus'+context+type+caseId+'_hide').show();
}

function hideCaseActivities( caseId , type, context ) {
  cj('#' + context + '-' + type +'-casedetails-' + caseId).hide();
  cj('#' + context+type+caseId+'_show').show();
  cj('#minus'+context+type+caseId+'_hide').hide();
}

//NYSS 5340
cj(function(){
  buildCaseClientRelationships( false );
});

function buildCaseClientRelationships( filterSearch ) {
  if( filterSearch ) {
    oTable.fnDestroy();
  }
  var count   = 0;
  var anOpen = [];
  var columns = '';
  var context = {/literal}"{$context}"{literal};
  var sourceUrl = {/literal}"{crmURL p='civicrm/ajax/getallcases' h=0 q='snippet=4'}"{literal};
  var sImageUrl = {/literal}"{$config->resourceBase}i/TreePlus.gif"{literal};

  cj('#clientRelationships-selector-'+ context +' th').each( function( ) {
    //the detail expand column should not be sortable, but does need to be added to sClass
    if ( !cj(this).hasClass('nosort') ) {
      columns += '{"sClass": "' + cj(this).attr('class') +'"},';
    }
    else {
      columns += '{"bSortable": false},';
    }
    count++;
  });

  columns = columns.substring(0, columns.length - 1 );
  eval('columns =[' + columns + ']');

  oTable = cj('#clientRelationships-selector-'+ context).dataTable({
    "bFilter"    : false,
    "bAutoWidth" : false,
    "aaSorting"  : [],
    "aoColumns"  : columns,
    "bProcessing": true,
    "bJQueryUI": true,
    "asStripClasses" : [ "odd-row", "even-row" ],
    "sPaginationType": "full_numbers",
    "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',
    "bServerSide": true,
    "sAjaxSource": sourceUrl,
    "bRetrieve": true,
    "iDisplayLength": 10,
    "fnDrawCallback": function() { setClientRelationshipsSelectorClass{/literal}{$context}{literal}( context ); },
    "fnServerData": function ( sSource, aoData, fnCallback ) {
      cj.ajax( {
        "dataType": 'json',
        "type": "POST",
        "url": sSource,
        "data": aoData,
        "success": fnCallback
      } );
    }
  });

  cj('#clientRelationships-selector-'+ context +' td.control').live( 'click', function () {
    var nTr = this.parentNode;
    var i = cj.inArray( nTr, anOpen );
    var oData = oTable.fnGetData( nTr );

    if ( i === -1 ) {
      var nDetailsRow = oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );
      cj('div.innerDetails', nDetailsRow).slideDown();
      cj('#treeIcon-' + oData[9]).html('<a><img src="{/literal}{$config->resourceBase}{literal}i/TreeMinus.gif" alt="close section"/><a/>');
      anOpen.push( nTr );
    }
    else {
      cj('div.innerDetails', cj(nTr).next()[0]).slideUp( function () {
        oTable.fnClose( nTr );
        anOpen.splice( i, 1 );
        cj('#treeIcon-' + oData[9]).html('<a><img src="{/literal}{$config->resourceBase}{literal}i/TreePlus.gif" alt="open section"/><a/>');
      } );
    }
  } );
}

function setClientRelationshipsSelectorClass{/literal}{$context}{literal}( context ) {
  cj('#clientRelationships-selector-' + context + ' td:last-child').each( function( ) {
    cj(this).parent().addClass(cj(this).text() );
  });
}

function fnFormatDetails( oTable, nTr ) {
  var oData = oTable.fnGetData( nTr );
  var list = '{/literal}{$list}{literal}';
  var context = '{/literal}{$context}{literal}';
  var case_id = oData[9];
  var contact_id = oData[10];

  // initiate innerDetails div
  var sOut = '<div class="innerDetails"></div>';

  // fill innerDetails div with data from AJAX call
  CaseDetails(case_id, contact_id, list, context);

  return sOut;
}
</script>
{/literal}
