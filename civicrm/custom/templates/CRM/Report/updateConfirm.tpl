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
