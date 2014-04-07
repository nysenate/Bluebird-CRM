<div class="nyss-mailing-subscriptions">
  <div class="nyss-section display_name">
    {$display_name}
  </div>

  <div class="nyss-section emails">
    <div class="help">
      <p>Below find a list of all email addresses we have on file. </p>
    </div>
    <div class="nyss-email-row-header">
      <div class="nyss-email-label">Email</div>
      <div class="nyss-email-label">Location Type</div>
      <div class="nyss-email-label">Hold Status</div>
      <div class="nyss-email-label">Subscription Opt-Outs</div>
    </div>
    {foreach from=$emails key=k item=e}
      <div class="nyss-email-row">
        <div class="nyss-email-fld {if $e.is_primary}nyss-email-primary{/if}">
          {$e.email}
        </div>
        <div class="nyss-email-fld">
          {$e.location_type_id}
        </div>
        <div class="nyss-email-fld">
          {$e.on_hold}
        </div>
        <div class="nyss-email-fld">
          categories...
        </div>
      </div>
    {/foreach}
  </div>

  <div class="nyss-section notes">
    <span class="label">Notes</span>
    {$form.note.html}
  </div>

</div>
