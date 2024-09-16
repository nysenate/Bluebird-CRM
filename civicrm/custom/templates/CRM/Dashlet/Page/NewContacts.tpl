{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}

{literal}
<style>
  div.crm-contact-form-block-activity_type_filter_id {
    vertical-align: top;
  }
</style>
{/literal}

<div class="crm-newcontacts">
  <details class="crm-accordion-bold crm-search_filters-accordion" open>
    <summary>
      {ts}Report Filters{/ts}
    </summary>

    <div class="crm-accordion-body">
      <form><!-- form element is here to fool the datepicker widget -->
        <table class="no-border form-layout-compressed newcontacts-search-options">
          <tr>
            <td class="crm-contact-form-block-activity_type_filter_id crm-inline-edit-field">
                {$form.newcontact_source_filter.label}<br /> {$form.newcontact_source_filter.html|crmAddClass:medium}
            </td>
            {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="newcontact_date" hideRelativeLabel=false}
          </tr>
        </table>
      </form>
    </div>
  </details>
  <table id="newcontacts-selector" class="contact-activity-selector-{$context}" style="width: 100%;">
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
  var oTable;

  cj(function() {
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
