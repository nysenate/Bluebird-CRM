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
<div class="crm-block crm-form-block crm-group-search-form-block">

<h3>{ts}Find Groups{/ts}</h3>
<table class="form-layout">
    <tr>
        <td>
            {$form.title.label}<br />
            {$form.title.html}<br />
            <span class="description font-italic">
                {ts}Complete OR partial group name.{/ts}
            </span>
        </td>
        <td>
            {$form.group_type.label}<br />
            {$form.group_type.html}<br />
            <span class="description font-italic">
                {ts}Filter search by group type(s).{/ts}
            </span>
        </td>
        <td>
            {$form.visibility.label}<br />
            {$form.visibility.html}<br />
            <span class="description font-italic">
                {ts}Filter search by visibility.{/ts}
            </span>
        </td>
	    <td>
            {$form.group_status.label}<br />
            {$form.group_status.html}
	    </td>
    </tr>
    <tr>
        <td>{$form.buttons.html}</td><td colspan="2">
    </tr>
</table>
</div>
<br/>
<table id="crm-group-selector">
    <thead>
        <tr>
            <th class='crm-group-name'>{ts}Name{/ts}</th>
            <th class='crm-group-group_id'>{ts}ID{/ts}</th>
            <th class='crm-group-description'>{ts}Description{/ts}</th>
            <th class='crm-group-group_type'>{ts}Group Type{/ts}</th>
            <th class='crm-group-visibility'>{ts}Visibility{/ts}</th>
            {if $showOrgInfo}
            <th class='crm-group-org_info'>{ts}Organization{/ts}</th>
            {/if}
            <th class='crm-group-group_links nosort'>&nbsp;</th>
            <th class='hiddenElement'>&nbsp;</th>
        </tr>
    </thead>
</table>

{* handle enable/disable actions*}
{include file="CRM/common/enableDisable.tpl"}
 
{literal}
<script type="text/javascript">
cj( function() {
    buildGroupSelector( false );
    cj('#_qf_Search_refresh').click( function() {
        buildGroupSelector( true );
    });
});
function buildGroupSelector( filterSearch ) {
    if ( filterSearch ) {
        crmGroupSelector.fnDestroy();
        var ZeroRecordText = '<div class="status messages">{/literal}{ts escape="js"}No matching Groups found for your search criteria. Suggestions:{/ts}{literal}<div class="spacer"></div><ul><li>{/literal}{ts escape="js"}Check your spelling.{/ts}{literal}</li><li>{/literal}{ts escape="js"}Try a different spelling or use fewer letters.{/ts}{literal}</li><li>{/literal}{ts escape="js"}Make sure you have enough privileges in the access control system.{/ts}{literal}</li></ul></div>';
    } else {
        var ZeroRecordText = {/literal}'{ts escape="js"}<div class="status messages">No Groups have been created for this site.{/ts}</div>'{literal};
    }
    
    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/ajax/grouplist" h=0 q="snippet=4"}'{literal};

    crmGroupSelector = cj('#crm-group-selector').dataTable({
        "bFilter"    : false,
        "bAutoWidth" : false,
        "aaSorting"  : [],
        "aoColumns"  : [
                        {sClass:'crm-group-name'},
                        {sClass:'crm-group-group_id'},
                        {sClass:'crm-group-description', bSortable:false},
                        {sClass:'crm-group-group_type'},
                        {sClass:'crm-group-visibility'},
                        {sClass:'crm-group-group_links', bSortable:false},
                        {/literal}{if $showOrgInfo}{literal}
                        {sClass:'crm-group-org_info', bSortable:false},
                        {/literal}{/if}{literal}
                        {sClass:'hiddenElement', bSortable:false}
                       ],
        "bProcessing": true,
        "asStripClasses" : [ "odd-row", "even-row" ],
        "sPaginationType": "full_numbers",
        "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',	
        "bServerSide": true,
        "bJQueryUI": true,
        "sAjaxSource": sourceUrl,
        "iDisplayLength": 25,
        "oLanguage": { "sZeroRecords":  ZeroRecordText,                         
                       "sProcessing":    {/literal}"{ts escape='js'}Processing...{/ts}"{literal},   
                       "sLengthMenu":    {/literal}"{ts escape='js'}Show _MENU_ entries{/ts}"{literal},
                       "sInfo":          {/literal}"{ts escape='js'}Showing _START_ to _END_ of _TOTAL_ entries{/ts}"{literal},
                       "sInfoEmpty":     {/literal}"{ts escape='js'}Showing 0 to 0 of 0 entries{/ts}"{literal},
                       "sInfoFiltered":  {/literal}"{ts escape='js'}(filtered from _MAX_ total entries){/ts}"{literal},
                       "sSearch":        {/literal}"{ts escape='js'}Search:{/ts}"{literal},      
                       "oPaginate": {                                           
                            "sFirst":    {/literal}"{ts escape='js'}First{/ts}"{literal},          
                            "sPrevious": {/literal}"{ts escape='js'}Previous{/ts}"{literal},       
                            "sNext":     {/literal}"{ts escape='js'}Next{/ts}"{literal},           
                            "sLast":     {/literal}"{ts escape='js'}Last{/ts}"{literal}            
                        }                                                       
                    },
        "fnDrawCallback": function() { setSelectorClass(  ); },
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            var showOrgInfo = {/literal}"{$showOrgInfo}"{literal};
            aoData.push( {name:'showOrgInfo', value: showOrgInfo });
            if ( filterSearch ) {
                var groupTypes = '';
                if ( cj('.crm-group-search-form-block #group_type\\[1\\]').prop('checked') ) {
                    groupTypes = '1'; 
                }
                
                if ( cj('.crm-group-search-form-block #group_type\\[2\\]').prop('checked') ) {
                    if ( groupTypes ) {
                        groupTypes = groupTypes + ',2';
                    } else {
                        groupTypes = groupTypes + '2';
                    }
                }

                var groupStatus = '';
                if ( cj('.crm-group-search-form-block #group_status\\[1\\]').prop('checked') ) {
                    groupStatus = '1'; 
                }
                
                if ( cj('.crm-group-search-form-block #group_status\\[2\\]').prop('checked') ) {
                    if ( groupStatus ) {
                        groupStatus = '3';
                    } else {
                        groupStatus = '2';
                    }
                }

                aoData.push(	     
                    {name:'title', value: cj('.crm-group-search-form-block #title').val()},
                    {name:'group_type', value: groupTypes },
                    {name:'visibility', value: cj('.crm-group-search-form-block #visibility').val()},
                    {name:'status', value: groupStatus }
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

function setSelectorClass(  ) {
    cj('#crm-group-selector tr').each( function( ) {
       var className = cj(this).find('td:last-child').text();
       cj(this).addClass( className );
       var rowID = cj(this).find('td:nth-child(2)').text();
       cj(this).prop( 'id', 'row_' + rowID );
    });
}

</script>
{/literal}
