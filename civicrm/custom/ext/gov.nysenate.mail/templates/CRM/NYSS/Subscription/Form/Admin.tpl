<div class='nyss-mailing-subscriptions-admin'>
  <div class='nyss-section emails'>
    <div class='nyss-intro-text help'>
      <p>Note: Mailing subscriptions options are email-address specific.</p>
    </div>
    <div class='nyss-email-row fld-email crm-section'>
      <div class='nyss-email-label label'>Email</div>
      <div class='nyss-email-fld content'>{$email}</div>
      <div class='clear'></div>
    </div>
    <div class='nyss-email-row fld-subscription crm-section'>
      <div class='nyss-email-label label'>Subscription Preferences</div>
      <div class='nyss-email-fld content'>
        {$form.mailing_categories.html}
      </div>
      <div class='clear'></div>
    </div>
  </div>

  <div class='nyss-buttons'>
    {$form.buttons.html}
  </div>
</div>

{literal}
<script type='text/javascript'>
</script>
{/literal}
