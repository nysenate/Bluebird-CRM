<div class="nyss-mailing-subscriptions">
  <div class="nyss-senator">
    {$senatorFormal}
  </div>

  <div class="nyss-intro">
    <div class="nyss-intro-text">
      <p>Please review the options below so you can stay informed about important news, issues and events that may affect you and your family.</p>
    </div>
  </div>

  <div class="nyss-section display_name">
    {$contact.display_name}
  </div>

  <div class="nyss-section emails">
    <div class="nyss-email-row">
      <div class="nyss-email-label">Email</div>
      <div class="nyss-email-fld">{$contact.email}</div>
      <div class="clear"></div>
    </div>
    <div class="nyss-email-row">
      <div class="nyss-email-label">Subscription Preferences</div>
      <div class="nyss-email-fld">
        <div class="nyss-key">If you feel you are receiving too much email, you can UNCHECK any topics below you aren’t interested in and still get critical news that matters to you.</div>
        {$form.mailing_categories.html}
      </div>
      <div class="clear"></div>
    </div>
    <div class="nyss-email-row">
      <div class="nyss-email-label">Opt-Out</div>
      <div class="nyss-email-fld">
        <div class="nyss-key">Or you can unsubscribe from all emails.</div>
        {$form.opt_out.html}I prefer not to receive ANY email notices from {$senatorFormal}.
      </div>
      <div class="clear"></div>
    </div>
  </div>

  <div class="nyss-buttons">
    {$form.buttons.html}
  </div>
</div>

{*7865*}
{literal}
<script type="text/javascript">
  //using standard dom js as we do not load jquery on this page (and want to continue keeping it lean)

  var form = document.Manage;
  var optout = document.getElementById('opt_out');
  var unsubOptsIds = [];
  var selectedOpts = [];

  optout.onclick = function(){
    if (optout.checked) {
      unsubOptsIds = [];
      selectedOpts = [];

      //store all existing selections as we cycle through
      for (var i = 0; i < form.elements.length; i++ ) {
        if (form.elements[i].type == 'checkbox' && form.elements[i].name.indexOf('mailing_categories') == 0) {
          //console.log('chkbxs: ', form.elements[i]);
          if (form.elements[i].checked == true) {
            selectedOpts.push(form.elements[i].id);
            form.elements[i].checked = false;
          }
          else if (selectedOpts.indexOf(form.elements[i].id) != -1) {
            selectedOpts.splice( selectedOpts.indexOf(form.elements[i].id), 1 );
          }
          unsubOptsIds.push(form.elements[i].id);
        }
      }
      //console.log('unsubOptsIds: ', unsubOptsIds);
    }
    else {
      //reselect any categories that were originally checked
      for (var i = 0; i < selectedOpts.length; i++ ) {
        document.getElementById(selectedOpts[i]).checked = true;
      }
    }
  };

  //uncheck optout if any subscription options checked
  var chkbx = document.getElementsByClassName('form-checkbox');
  for (var i = 0; i < chkbx.length; i++ ) {
    if ( chkbx[i].name.indexOf('mailing_categories') == 0 ) {
      chkbx[i].onclick = function(e){
        //console.log(e);
        if (e.srcElement.checked && optout.checked) {
          optout.checked = false;
        }
      }
    }

    //document.getElementById(selectedOpts[i]).checked = true;
  }
  //console.log(chkbx);
</script>
{/literal}
