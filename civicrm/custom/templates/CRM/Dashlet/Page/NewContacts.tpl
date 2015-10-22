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
{*<pre>{$form|@print_r}</pre>*}

{literal}
<style type="text/css">
  div.crm-contact-form-block-activity_type_filter_id {
    vertical-align: top;
  }
</style>
{/literal}

<div class="crm-newcontacts">
  <div class="crm-accordion-wrapper crm-search_filters-accordion">
    <div class="crm-accordion-header">
      {ts}Report Filters{/ts}</a>
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <div class="no-border form-layout-compressed" id="searchOptions">
        <div class="crm-contact-form-block-source_filter_id crm-inline-edit-field">
          {$form.newcontact_source_filter.label} {$form.newcontact_source_filter.html}
        </div>
        <div class="crm-contact-form-block-date_filter_id crm-inline-edit-field">
          <label>Created Date</label> {include file="CRM/Core/DateRange.tpl" fieldName="newcontact_date" from='_low' to='_high'}
        </div>
      </div>
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
  <table id="newcontacts-selector">
    <thead>
    <tr>
      <th class='crm-contact-newcontacts_contact'>{ts}Contact{/ts}</th>
      <th class='crm-contact-newcontacts_date'>{ts}Date{/ts}</th>
      <th class='crm-contact-newcontacts_email'>{ts}Email{/ts}</th>
      <th class='crm-contact-newcontacts_address'>{ts}Address{/ts}</th>
      <th class='crm-contact-newcontacts_city'>{ts}City{/ts}</th>
      <th class='crm-contact-newcontacts_source'>{ts}Source{/ts}</th>
    </tr>
    </thead>
  </table>
</div>

{literal}
<script type="text/javascript">
  //bump activity date filter to next line
  cj('span.crm-absolute-date-range').before('<br />');

  var oTable;

  cj(function() {
    cj().crmAccordions();
    var filterSearchOnLoad = true;
    buildRecentContacts(filterSearchOnLoad);

    cj('#newcontact_source_filter').change( function( ) {
      buildRecentContacts(true);
    });
    cj('#newcontact_date_relative').change( function( ) {
      buildRecentContacts(true);
    });
    cj('#newcontact_date_low_display').change( function( ) {
      buildRecentContacts(true);
    });
    cj('#newcontact_date_high_display').change( function( ) {
      buildRecentContacts(true);
    });
    cj('.crm-absolute-date-range span.crm-clear-link a').click( function( ) {
      buildRecentContacts(true);
    });
  });

  function buildRecentContacts( filterSearch ) {
    //console.log('buildRecentContacts');
    if ( filterSearch && oTable ) {
      oTable.fnDestroy();
    }

    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/nyss/web/getnewcontacts" h=0 q="snippet=4"}'{literal};

    var ZeroRecordText = {/literal}'{ts escape="js"}No matches found{/ts}'{literal};
    if ( cj('select#newcontact_source_filter').val() ) {
      ZeroRecordText += {/literal}'{ts escape="js"} for Source = "{/ts}'{literal} +  cj('select#newcontact_source_filter :selected').text( ) + '"';
    }
    else {
      ZeroRecordText += '.';
    }

    oTable = cj('#newcontacts-selector').dataTable({
      "bFilter"    : false,
      "bAutoWidth" : false,
      "aaSorting"  : [],
      "aoColumns"  : [
        {sClass:'crm-contact-newcontacts_contact'},
        {sClass:'crm-contact-newcontacts_date'},
        {sClass:'crm-contact-newcontacts_email'},
        {sClass:'crm-contact-newcontacts_address'},
        {sClass:'crm-contact-newcontacts_city'},
        {sClass:'crm-contact-newcontacts_source'}
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
        aoData.push( {name:'admin', value: {/literal}'{$admin}'{literal}} );

        if ( filterSearch ) {
          var source = cj('select#newcontact_source_filter').val();
          var date_relative = cj('select#newcontact_date_relative').val();
          var date_low = cj('input#newcontact_date_low').val();
          var date_high = cj('input#newcontact_date_high').val();
          //console.log('source: ', source);
          aoData.push({name:'source', value: source});
          aoData.push({name:'date_relative', value: date_relative});
          aoData.push({name:'date_low', value: date_low});
          aoData.push({name:'date_high', value: date_high});
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
</script>
{/literal}
