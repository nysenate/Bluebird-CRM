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
{*NYSS include additional reports css*}
{literal}
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/css/reportsCivicrm.css" />
{/literal}

{include file="CRM/common/crmeditable.tpl"}
{* this div is being used to apply special css *}
{*NYSS include js files in print mode; allows sorting and removes IE erorrs*}
{if $printOnly}
  {include file="CRM/common/jquery.tpl"}
{/if}

{if $section eq 1}
  <div class="crm-block crm-content-block crm-report-layoutGraph-form-block">
    {*include the graph*}
    {include file="CRM/Report/Form/Layout/Graph.tpl"}
  </div>
{elseif $section eq 2}
  <div class="crm-block crm-content-block crm-report-layoutTable-form-block">
    {*include the table layout*}
    {include file="CRM/Report/Form/Layout/Table.tpl"}
	</div>
{else}
  {*include actions*}
  {include file="CRM/Report/Form/Actions.tpl"}

  {if $criteriaForm OR $instanceForm OR $instanceFormError}
    <div class="crm-block crm-form-block crm-report-field-form-block">
      {include file="CRM/Report/Form/Fields.tpl"}
    </div>
  {/if}
    
  <div class="crm-block crm-content-block crm-report-form-block">
    {*Statistics at the Top of the page*}
    {include file="CRM/Report/Form/Statistics.tpl" top=true}

    {*include the graph*}
    {include file="CRM/Report/Form/Layout/Graph.tpl"}

    {*include the table layout*}
    {include file="CRM/Report/Form/Layout/Table.tpl"}
    <br />
    {*Statistics at the bottom of the page*}
    {include file="CRM/Report/Form/Statistics.tpl" bottom=true}

    {include file="CRM/Report/Form/ErrorMessage.tpl"}
  </div>
{/if}

{if $outputMode == 'print'}
  <script type="text/javascript">
    window.print();
  </script>
{/if}

{*NYSS 6440*}
{literal}
<script type="text/javascript">
  //6440
  //find report form
  var rptId = '';
  cj('form').each(function(){
    if ( cj(this).prop('action').indexOf("/civicrm/report/") > -1 ) {
      rptId = cj(this).prop('id');
    }
  });

  //determine if update button was clicked and not yet confirmed
  cj('input[value="Update Existing Report"]').click(function(){
    if ( cj('input[value="Update Existing Report"]').attr('confirmed') != 'true' ) {
      cj('input[value="Update Existing Report"]').attr('update', 'true');
    }
  });

  //on form submit, show modal
  cj('form#' + rptId).submit(function(e){
    var self = this;
    var frmUpd = cj('input[value="Update Existing Report"]').attr('update');
    //console.log('frmUpd', frmUpd);

    if ( frmUpd == 'true' ) {
      e.preventDefault();
      cj('input[value="Update Existing Report"]').attr('update', 'false');

      var $dialog = cj('<div></div>')
        .html('Are you sure you wish to update the existing report? Remember that this report may be in use by other staff in your office, and changing the report criteria and settings may impact their workflow. If you wish to create a new report rather than modify an existing report, cancel this step, click the settings tab and give your report a new name, then click "Save as New Report".')
        .dialog({
          autoOpen: false,
          title: 'Update Existing Report',
          modal: true,
          bgiframe: true,
          overlay: { opacity: 0.5, background: "black" },
          buttons: {
            "OK": function() {
              $dialog.dialog('close');

              //designate it as confirmed, and allow the update button to be resubmitted
              cj('input[value="Update Existing Report"]').attr('confirmed', 'true');

              cj('input[value="Update Existing Report"]').trigger('click');
              return true;
            },
            Cancel: function() {
              cj(this).dialog("close");

              return false;
            }
          }
        });
      $dialog.dialog('open');
      return false;
    }
  });
</script>
{/literal}
