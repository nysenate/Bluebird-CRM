<div class="nyss-mailing-subscriptions">
  <div class="nyss-section display_name">
    {$display_name}
  </div>

  <div class="nyss-section emails">
    {foreach from=$form.email key=k item=e}
      <div class="nyss-email-row">
        <div class="nyss-email-fld">
          {$e.email.label}<br />{$e.email.html}
        </div>
        <div class="nyss-email-fld">
          {$e.location_type_id.label}<br />{$e.location_type_id.html}
        </div>
        <div class="nyss-email-fld">
          {$e.on_hold.label}<br />{$e.on_hold.html}
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
