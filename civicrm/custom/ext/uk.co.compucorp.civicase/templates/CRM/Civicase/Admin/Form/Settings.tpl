<tr class="crm-case-form-block-allowCaseLocks">
  <td class="label">{$form.civicaseAllowCaseLocks.label}</td>
  <td>{$form.civicaseAllowCaseLocks.html}<br />
    <span class="description">{ts}This will allow cases to be locked for certain contacts.{/ts}</span>
  </td>
</tr>

{foreach from=$caseCategoryWebFormSetting key=settingKey item=setting}
  <tr class="crm-case-form-block-{$settingKey}">
    <td class="label">{$form.$settingKey.label}</td>
    <td>{$form.$settingKey.html}<br />
      <span class="description">{ts}{$setting.description}{/ts}</span>
    </td>
  </tr>
{/foreach}

<script type="text/javascript">
  var caseCategoryWebFormSetting = {$caseCategoryWebFormSetting|@json_encode};
  {literal}

    CRM.$(function($) {
      cj.each(caseCategoryWebFormSetting, function(key, val){
        if (!val.is_webform_url) {
          allowCaseWebformToggle(key, val.webform_url_name);
          cj('input[name="' + key + '"]').click(function() {
            allowCaseWebformToggle(key, val.webform_url_name);
          });
        }
      });
    });

  /**
   * Logic for what happens when the allow case webform radio button is
   * toggled.
   *
   * @param string settingKey the webform setting key
   * @param string corresponding webform Url setting name
   */
  function allowCaseWebformToggle(settingKey, webformUrl) {
    var allowCaseWebform = cj('input[name="' + settingKey + '"]:checked').val();
    if (allowCaseWebform === '0') {
      cj('.crm-case-form-block-' + webformUrl).hide();
    }
    else if (allowCaseWebform === '1') {
      cj('.crm-case-form-block-' + webformUrl).show();
    }
  }
  {/literal}
</script>
