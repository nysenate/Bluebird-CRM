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
{*debug*}

<div class="crm-web-activity-stream nyss-integration-tab">
  <h3>Website Activity Stream</h3>
  <div class="crm-accordion-wrapper crm-search_filters-accordion">
    <div class="crm-accordion-header">
      {ts}Filter by Activity Type{/ts}</a>
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <div class="no-border form-layout-compressed" id="searchOptions">
        <div class="crm-contact-form-block-activity_type_filter_id crm-inline-edit-field">
          {$form.web_activity_type_filter.label} {$form.web_activity_type_filter.html|crmAddClass:big}
        </div>
      </div>
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
  <table id="contact-web-activity-selector">
    <thead>
    <tr>
      <th class='crm-contact-activity-activity_contact'>{ts}Contact{/ts}</th>
      <th class='crm-contact-activity-activity_type'>{ts}Type{/ts}</th>
      <th class='crm-contact-activity-activity_date'>{ts}Date{/ts}</th>
      <th class='crm-contact-activity-activity_details'>{ts}Details{/ts}</th>
    </tr>
    </thead>
  </table>
</div>

{literal}
<script type="text/javascript">
  var oTable;

  cj(function() {
    var filterSearchOnLoad = true;
    buildWebActivities(filterSearchOnLoad);

    cj('#web_activity_type_filter').change(function() {
      buildWebActivities(true);
    });
  });

  function buildWebActivities( filterSearch ) {
    //console.log('buildWebActivities');
    if ( filterSearch && oTable ) {
      oTable.fnDestroy();
    }

    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/nyss/web/getactivitystream" h=0 q="snippet=4&cid=$contactId"}'{literal};

    var ZeroRecordText = {/literal}'{ts escape="js"}No matches found{/ts}'{literal};
    if ( cj('select#web_activity_type_filter').val() ) {
      ZeroRecordText += {/literal}'{ts escape="js"} for Activity Type = "{/ts}'{literal} +  cj('select#web_activity_type_filter :selected').text( ) + '"';
    }
    else {
      ZeroRecordText += '.';
    }

    oTable = cj('#contact-web-activity-selector').dataTable({
      "bFilter"    : false,
      "bAutoWidth" : false,
      "aaSorting"  : [],
      "aoColumns"  : [
        {sClass:'crm-contact-activity-activity_contact'},
        {sClass:'crm-contact-activity-activity_type'},
        {sClass:'crm-contact-activity-activity_date'},
        {sClass:'crm-contact-activity-activity_details'}
      ],
      "bProcessing": true,
      "sPaginationType": "full_numbers",
      "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',
      "bServerSide": true,
      "bJQueryUI": true,
      "sAjaxSource": sourceUrl,
      "iDisplayLength": 25,
      "oLanguage": {
        "sZeroRecords":  ZeroRecordText,
        "sProcessing":   {/literal}"{ts escape='js'}Processing...{/ts}"{literal},
        "sLengthMenu":   {/literal}"{ts escape='js'}Show _MENU_ entries{/ts}"{literal},
        "sInfo":         {/literal}"{ts escape='js'}Showing _START_ to _END_ of _TOTAL_ entries{/ts}"{literal},
        "sInfoEmpty":    {/literal}"{ts escape='js'}Showing 0 to 0 of 0 entries{/ts}"{literal},
        "sInfoFiltered": {/literal}"{ts escape='js'}(filtered from _MAX_ total entries){/ts}"{literal},
        "sSearch":       {/literal}"{ts escape='js'}Search:{/ts}"{literal},
        "oPaginate": {
          "sFirst":    {/literal}"{ts escape='js'}First{/ts}"{literal},
          "sPrevious": {/literal}"{ts escape='js'}Previous{/ts}"{literal},
          "sNext":     {/literal}"{ts escape='js'}Next{/ts}"{literal},
          "sLast":     {/literal}"{ts escape='js'}Last{/ts}"{literal}
        }
      },
      "fnDrawCallback": function() { setSelectorClass(); },
      "fnServerData": function ( sSource, aoData, fnCallback ) {
        aoData.push( {name:'contact_id', value: {/literal}{$contactId}{literal}},
          {name:'admin', value: {/literal}'{$admin}'{literal}}
        );

        if ( filterSearch ) {
          var type = cj('select#web_activity_type_filter').val();
          //console.log('type: ', type);
          aoData.push({name:'type', value: type});
        }

        cj.ajax( {
          "dataType": 'json',
          "type": "POST",
          "url": sSource,
          "data": aoData,
          "success": fnCallback,
          // CRM-10244
          "dataFilter": function(data, type) { return data.replace(/[\n\v\t]/g, " "); }
        });
      }
    });
  }

  function setSelectorClass() {
  }

  function displayNote(noteId) {
    cj('#msg-' + noteId).dialog().dialog('open');
    return false;
  }
</script>
{/literal}
