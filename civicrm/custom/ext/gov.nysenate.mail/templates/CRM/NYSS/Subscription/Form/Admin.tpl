<div class="nyss-mailing-subscriptions-admin">
  <div class="nyss-section emails">
    <div class="nyss-intro-text">
      <p>Note: Mailing subscriptions options are email-address specific.</p>
    </div>
    <div class="nyss-email-row fld-email">
      <div class="nyss-email-label">Email</div>
      <div class="nyss-email-fld">{$email}</div>
      <div class="clear"></div>
    </div>
    <div class="nyss-email-row fld-subscription">
      <div class="nyss-email-label">Subscription Preferences</div>
      <div class="nyss-email-fld">
        {$form.mailing_categories.html}
      </div>
      <div class="clear"></div>
    </div>
  </div>

  <div class="nyss-buttons">
    {$form.buttons.html}
  </div>
</div>

{literal}
<script type="text/javascript">
</script>
{/literal}
