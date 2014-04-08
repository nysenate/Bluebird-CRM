<div class="nyss-mailing-subscriptions">
  <div class="nyss-senator">
    {$senatorFormal}
  </div>

  <div class="nyss-intro">
    <div class="nyss-intro-text">
      <p>Below find a list of all email addresses we have on file for you. Please review them and indicate any mailing categories <strong>for which you want to opt out.</strong></p>
      <p><strong>Note:</strong> If you do not select any categories, we will assume you want to receive mass emails for any of the mailing categories listed. Only select categories <strong>if you do not want to receive those emails.</strong> If any of the emails are incorrect, no longer in use, or you would like them removed from our system, please provide those details and requests using the notes field.</p>
    </div>
  </div>

  <div class="nyss-section display_name">
    {$display_name}
  </div>

  <div class="nyss-section emails">
    <div class="nyss-email-row-header">
      <div class="nyss-email-label wide">Email</div>
      <div class="nyss-email-label">Location Type</div>
      <div class="nyss-email-label">Hold Status</div>
      <div class="nyss-email-label wide">Subscription Opt-Outs</div>
    </div>
    {foreach from=$emails key=k item=e}
      <div class="nyss-email-row">
        <div class="nyss-email-fld wide {if $e.is_primary}nyss-email-primary{/if}">{$e.email}</div>
        <div class="nyss-email-fld">{$e.location_type_id}</div>
        <div class="nyss-email-fld">{$e.on_hold}&nbsp;</div>
        <div class="nyss-email-fld wide">{$form.email.$k.mailing_categories.html}
          {literal}
          <script type="text/javascript">
            var k = {/literal}{$k}{literal};
            cj('select#subscription-optout-' + k).crmasmSelect({
              addItemTarget: 'bottom',
              animate: false,
              highlight: true,
              sortable: true,
              respectParents: true
            });
          </script>
          {/literal}
        </div>
      </div>
    {/foreach}
    <div class="clear"></div>
  </div>

  <div class="nyss-key"><strong>Location Type</strong> indicates where or how the email is used. A value of "BOE" or "BOEmailing" indicates we received the email from the Board of Elections. <strong>Hold Status</strong> may indicate the email bounced in a previous delivery attempt, or that you opted-out of receiving all mailings to that email. If the email is on hold, it will not be delivered to, regardless of the subscription opt-out settings. If an email is on hold and should not be, please inform us through the notes field.</div>

  <div class="nyss-section notes">
    <span class="label">Notes</span>
    {$form.note.html}
  </div>

  <div class="nyss-buttons">
    {$form.buttons.html}
  </div>
</div>
