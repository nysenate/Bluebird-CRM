<div class="nyss-mailing-subscriptions">
  <div class="nyss-senator">
    {$senatorFormal}
  </div>

  <div class="nyss-intro">
    <div class="nyss-intro-text">
      <p>Thank you! Below find an updated list of the email addresses we have on file for you.</strong></p>
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
        <div class="nyss-email-fld wide">{$e.mailing_categories}&nbsp;</div>
      </div>
    {/foreach}
    <div class="clear"></div>
  </div>
</div>
