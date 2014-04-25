<div class="nyss-mailing-subscriptions">
  <div class="nyss-senator">
    {$senatorFormal}
  </div>

  <div class="nyss-intro">
    <div class="nyss-intro-text">
      <p>Thank you! Below find your mailing subscriptions summary.</strong></p>
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
      <div class="nyss-email-fld">{$contact.mailing_categories_list}</div>
      <div class="clear"></div>
    </div>
    <div class="nyss-email-row">
      <div class="nyss-email-label">Opt-Out</div>
      <div class="nyss-email-fld">{$contact.opt_out}</div>
      <div class="clear"></div>
    </div>
  </div>
</div>
