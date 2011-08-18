<span id="fileOnCaseStatusMsg-{$context}" style="display:none;"></span>
<div class="crm-activity-selector-{$context}">
<div class="crm-accordion-wrapper crm-search_filters-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Filter by Activity Type{/ts}</a>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

  <table class="no-border form-layout-compressed" id="searchOptions">
    <tr>
        <td class="crm-contact-form-block-activity_type_filter_id">
            {$form.activity_type_filter_id.html}
        </td>
        <!--td style="vertical-align: bottom;">
		<span class="crm-button"><input class="form-submit default" name="_qf_Basic_refresh" value="Search" type="button" onclick="buildContactActivities( true )"; /></span>
	</td-->
    </tr>
  </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
<table id="contact-activity-selector-{$context}">
    <thead>
        <tr>
            <th class='crm-contact-activity-activity_type'>{ts}Type{/ts}</th>
            <th class='crm-contact-activity_subject'>{ts}Subject{/ts}</th>
            <th class='crm-contact-activity-source_contact'>{ts}Added By{/ts}</th>
            <th class='crm-contact-activity-target_contact nosort'>{ts}With{/ts}</th>
            <th class='crm-contact-activity-assignee_contact nosort'>{ts}Assigned{/ts}</th>
            <th class='crm-contact-activity-activity_date'>{ts}Date{/ts}</th>
            <th class='crm-contact-activity-activity_status'>{ts}Status{/ts}</th>
            <th class='crm-contact-activity-links nosort'>&nbsp;</th>
            <th class='hiddenElement'>&nbsp;</th>
        </tr>
    </thead>
</table>
</div>
{include file="CRM/Case/Form/ActivityToCase.tpl" contactID=$contactId}
{literal}
<script type="text/javascript">
var {/literal}{$context}{literal}oTable;

cj( function ( ) {
   cj().crmaccordions(); 
   var context = {/literal}"{$context}"{literal}; 
   buildContactActivities{/literal}{$context}{literal}( false );
   cj('.crm-activity-selector-'+ context +' #activity_type_filter_id').change( function( ) {
       buildContactActivities{/literal}{$context}{literal}( true );
   });
});

function buildContactActivities{/literal}{$context}{literal}( filterSearch ) {
    if ( filterSearch ) {
        {/literal}{$context}{literal}oTable.fnDestroy();
    }
    var context = {/literal}"{$context}"{literal}; 
    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/ajax/contactactivity" h=0 q="snippet=4&context=$context&cid=$contactId"}'{literal};

    var ZeroRecordText = {/literal}'{ts escape="js"}No matches found{/ts}'{literal};
    if ( cj('.crm-activity-selector-'+ context +' select#activity_type_filter_id').val( ) ) {
      ZeroRecordText += {/literal}'{ts escape="js"} for Activity Type = "{/ts}'{literal} +  cj('.crm-activity-selector-'+ context +' select#activity_type_filter_id :selected').text( ) + '"';
    } else {
      ZeroRecordText += '.';
    }

    {/literal}{$context}{literal}oTable = cj('#contact-activity-selector-' + context ).dataTable({
        "bFilter"    : false,
        "bAutoWidth" : false,
        "aaSorting"  : [],
        "aoColumns"  : [
                        {sClass:'crm-contact-activity-activity_type'},
                        {sClass:'crm-contact-activity_subject'},
                        {sClass:'crm-contact-activity-source_contact'},
                        {sClass:'crm-contact-activity-target_contact', bSortable:false},
                        {sClass:'crm-contact-activity-assignee_contact', bSortable:false},
                        {sClass:'crm-contact-activity-activity_date'},
                        {sClass:'crm-contact-activity-activity_status'},
                        {sClass:'crm-contact-activity-links', bSortable:false},
                        {sClass:'hiddenElement', bSortable:false}
                       ],
        "bProcessing": true,
        "sPaginationType": "full_numbers",
        "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',	
        "bServerSide": true,
        "bJQueryUI": true,
        "sAjaxSource": sourceUrl,
        "iDisplayLength": 25,
        "oLanguage": { "sZeroRecords":  ZeroRecordText },
        "fnDrawCallback": function() { setSelectorClass{/literal}{$context}{literal}( context ); },
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            aoData.push( {name:'contact_id', value: {/literal}{$contactId}{literal}},
                         {name:'admin',   value: {/literal}'{$admin}'{literal}}
            );
            if ( filterSearch ) {
                aoData.push(	     
                    {name:'activity_type_id', value: cj('.crm-activity-selector-'+ context +' select#activity_type_filter_id').val()}
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

function setSelectorClass{/literal}{$context}{literal}( context ) {
    cj('#contact-activity-selector-' + context + ' td:last-child').each( function( ) {
       cj(this).parent().addClass(cj(this).text() );
    });
}
</script>
{/literal}
