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
      <div class="nyss-email-label">Subscription Removal</div>
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
