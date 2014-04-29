<div class="nyss-mailing-subscriptions">
  <div class="nyss-senator">
    {$senatorFormal}
  </div>

  <div class="nyss-intro">
    <div class="nyss-intro-text">
      <p>Below find your email address on file with the Senate office. Use the tool below to select any email subscription categories <strong>for which you do not want to receive mailings.</strong> If no options are selected, you will continue to receive mailings for all categories.</p>
      <p>If you do not wish to receive <strong>any</strong> mailings from the Senator's office, please select "opt-out." Note that if you opt-out of all mailings, it will override any category-based selections.</p>
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
        <div class="nyss-key">Only select categories for which you <strong>do not</strong> want to receive mailings.</div>
        {$form.mailing_categories.html}
      </div>
      <div class="clear"></div>
    </div>
    <div class="nyss-email-row">
      <div class="nyss-email-label">Opt-Out</div>
      <div class="nyss-email-fld">
        <div class="nyss-key">Only select opt-out if you do not want to receive <strong>any</strong> mailings from the Senate office.</div>
        {$form.opt_out.html}Opt-Out of ALL mass email from {$senatorFormal}
      </div>
      <div class="clear"></div>
    </div>
  </div>

  <div class="nyss-buttons">
    {$form.buttons.html}
  </div>
</div>
