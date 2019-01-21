{* short-lived template hack until we migrate this extension to core *}

<script type="text-template" id="extra-block_menubar_position">
  <tr class="crm-preferences-display-form-block_menubar_position">
    <td class="label">{$form.menubar_position.label}</td>
    <td>
      {$form.menubar_position.html}
      <div class="description">{ts}Default position for the CiviCRM menubar.{/ts}</div>
    </td>
  </tr>
</script>
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $('.crm-preferences-display-form-block > table').append($('#extra-block_menubar_position').html());
    });
  </script>
{/literal}
