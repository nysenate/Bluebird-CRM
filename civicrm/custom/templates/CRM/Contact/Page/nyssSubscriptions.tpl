{*7889*}
{foreach from=$email key="blockId" item=item}
  <span class="subscription-link description" id="subscription-row-{$item.id}">
    <a href="#" title="{ts}Subscriptions{/ts}" onClick="showHideSubscriptions('{$item.id}', '{$blockId}'); return false;">{ts}(subscriptions){/ts}</a>
    <div id="subscription-form-{$item.id}" class="hiddenElement nyss-subscription-form"></div>
  </span>

  {literal}
  <script type="text/javascript">
    var emailId = {/literal}'{$item.id}'{literal};
    var blockId = {/literal}'{$blockId}'{literal};
    //console.log('blockId: ', blockId);

    cj('#subscription-row-' + emailId).insertAfter('div#Email_Block_' + blockId + '_signature');
  </script>
  {/literal}
{/foreach}

{literal}
<script type="text/javascript">
  function showHideSubscriptions(emailId, blockNo) {
    //console.log('showHideSubscriptions emailId: ', emailId, ' | blockNo: ' + blockNo);

    var dataURL = {/literal}"{crmURL p='civicrm/nyss/subscription/admin' q="reset=1&snippet=5&context=dialog&emailId=" h=0 }"{literal};
    dataURL = dataURL + emailId;

    cj.ajax({
      url: dataURL,
      success: function( content ) {
        //console.log('content: ', content);
        cj( '#subscription-form-'+ emailId ).show( ).html( content ).dialog({
          title: "{/literal}{ts escape='js'}Manage Subscriptions{/ts}{literal}",
          modal: true,
          width: 680,
          overlay: {
            opacity: 0.5,
            background: "black"
          },
          buttons: {
            "Save Subscription Settings": function () {
              //console.log('this: ', this);
              //console.log('submitting form', cj('form#Admin'));
              //cj('form#Admin').submit();
              var mc = new Array();
              var mci = 0;
              cj('div#subscription-form-' + emailId + ' input[name^=mailing_categories]').each(function(i, c){
                //console.log('c: ', c);
                var mcid = cj(c).prop('id').replace('mailing_categories_', '');
                if (cj(c).is(':checked')) {
                  mc[mci] = mcid;
                  mci++;
                }
              });
              //console.log('mc: ', mc);

              var postUrl = {/literal}"{crmURL p='civicrm/nyss/subscription/admin/process' h=0 }"{literal};
              var data = 'eid='+ emailId + '&mailing_categories_list=' + mc + '&isajax=1';
              cj.ajax({
                type     : "POST",
                url      : postUrl,
                data     : data,
                async    : false,
                dataType : "json",
                success  : function(values) {
                  //console.log('values', values);
                  if (values.subUpdateResponse) {
                    //console.log('success msg');
                    var email = cj('div#subscription-form-' + emailId + ' div.fld-email div.nyss-email-fld').text();
                    CRM.alert('Mailing subscription options were successfully updated for: ' + email, 'Mailing Subscriptions', 'success');
                  }
                  else {
                    //display error message.
                    cj().crmError('There was a problem processing the mailing subscription options.');
                  }
                }
              });

              cj(this).dialog("close");
              cj(this).dialog("destroy");
              cj('.nyss-subscription-form').hide();
            },
            "Cancel": function() {
              cj(this).dialog("close");
              cj(this).dialog("destroy");
              cj('.nyss-subscription-form').hide();
            }
          },
          close: function(event, ui) {
            //console.log('event: ', event);
            cj('.nyss-subscription-form').hide();
          }
        });
      }
    });
  }
</script>
{/literal}
