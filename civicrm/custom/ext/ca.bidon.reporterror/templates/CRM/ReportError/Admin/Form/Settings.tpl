{crmScope extensionKey='ca.bidon.reporterror'}
<div class="crm-block crm-form-block crm-reporterror-form-block">
  <h3>{ts}General Setup{/ts}</h3>
  <table class="form-layout-compressed" style="width:100%;">
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.reporterror_mailto.label}</td>
      <td>{$form.reporterror_mailto.html}
        <div class="description">{ts domain="ca.bidon.reporterror"}This usually is your domain administrator's email. Separate multiple email addresses with a comma (','). If left empty, no e-mails will be sent.{/ts}</div></td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.reporterror_show_full_backtrace.label}</td>
      <td>{$form.reporterror_show_full_backtrace.html}
        <div class="description">{ts}The full backtrace can provide more information on the variables passed to each function, but could expose more sensitive information.{/ts}</div>
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.reporterror_show_post_data.label}</td>
      <td>{$form.reporterror_show_post_data.html}
        <div class="description">{ts}POST data is usually the data submitted in forms. This can include sensitive information.{/ts}</div>
      </td>
    </tr>
    <tr class="crm-reporterror-form-block">
      <td class="label">{$form.reporterror_show_session_data.label}</td>
      <td>{$form.reporterror_show_session_data.html}
        <div class="description">{ts}Session data can provide clues, but should probably be disabled most of the time, as it can include sensitive information.{/ts}</div>
      </td>
    </tr>
  </table>

  <div class="crm-accordion-wrapper crm-reporterror_admin_form-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Contribution pages with no referrer{/ts}
    </div>
    <div class="crm-accordion-body">
      <p>{ts}Sometimes users might restore their browser session or share the link of the contribution 'thank you' page, which will result in a fatal error. You can use the options below to redirect visitors to a more relevant location.{/ts}</p>

      <table class="form-layout-compressed" style="width:100%;">
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_noreferer_sendreport.label}</td>
          <td>{$form.reporterror_noreferer_sendreport.html}</td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_noreferer_handle.label}</td>
          <td>{$form.reporterror_noreferer_handle.html}</td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_noreferer_pageid.label}</td>
          <td>{$form.reporterror_noreferer_pageid.html}</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="crm-accordion-wrapper crm-reporterror_admin_form-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Event registration pages with no referrer{/ts}
    </div>
    <div class="crm-accordion-body">
      <p>{ts}Sometimes users might restore their browser session or share the link of the event confirmation page, which will result in a fatal error. You can use the options below to redirect visitors to a more relevant location.{/ts}</p>

      <table class="form-layout-compressed" style="width:100%;">
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_noreferer_sendreport_event.label}</td>
          <td>{$form.reporterror_noreferer_sendreport_event.html}</td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_noreferer_handle_event.label}</td>
          <td>{$form.reporterror_noreferer_handle_event.html}</td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_noreferer_handle_eventid.label}</td>
          <td>{$form.reporterror_noreferer_handle_eventid.html}</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="crm-accordion-wrapper crm-reporterror_admin_form-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Profile errors{/ts}
    </div>
    <div class="crm-accordion-body">
      <p>{ts}Common errors on profiles include: anonymous users cannot view profiles (ex: accessing anything CiviCRM page requires user authentication) or the profile may have been disabled. In most cases, the user rarely needs to know about it. Bots also like to browse profiles, since they do not return proper HTTP error codes when they are disabled. Profiles errors are always logged in the CiviCRM logs (ConfigAndLog).{/ts}</p>

      <table class="form-layout-compressed" style="width:100%;">
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_sendreport_profile.label}</td>
          <td>{$form.reporterror_sendreport_profile.html}</td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_handle_profile.label}</td>
          <td>{$form.reporterror_handle_profile.html}</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="crm-accordion-wrapper crm-reporterror_admin_form-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Bots and crawlers{/ts}
    </div>
    <div class="crm-accordion-body">

      <p>{ts}Web crawlers used by search engines can often generate a lot of errors. In some cases, this might be because you have invalid links, but in most cases, the bots are just being annoying and crawling where they shouldn't.{/ts}</p>

      <table class="form-layout-compressed" style="width:100%;">
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_bots_sendreport.label}</td>
          <td>
            {$form.reporterror_bots_sendreport.html}
          </td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_bots_404.label}</td>
          <td>
            {$form.reporterror_bots_404.html}
            <p class="description">By default, CiviCRM always responds '200 OK', even if there was a fatal error. By responding to the request with a '404 not found' code, the bot is less likely to try again.</p>
          </td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_bots_regexp.label}</td>
          <td>
            {$form.reporterror_bots_regexp.html}
            <p class="description">{ts 1='(Googlebot|bingbot|python|Baiduspider|Yandex)'}If in doubt, leave this as is. The default is: %1{/ts}</p>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="crm-accordion-wrapper crm-reporterror_admin_form-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Smart Groups{/ts}
    </div>
    <div class="crm-accordion-body">
      <p>{ts}In some more complex configurations, Smart Groups can fail to refresh if their search criterias are no longer valid. This is a rather rare use-case, usually caused by custom searches. If one smarty group fails to refresh, the Scheduled Job that periodically to refresh the smart group contacts will then fail to run, which can cause incorrect contact group counts in other groups.{/ts}</p>

      <table class="form-layout-compressed" style="width:100%;">
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_smartgroups_autodisable.label}</td>
          <td>
            {$form.reporterror_smartgroups_autodisable.html}
            <p class="description">{ts}When a group is disabled, an email notification will be sent (if one was set in the field at the top of this screen) and a note will be added in the group description.{/ts}</p>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="crm-accordion-wrapper crm-reporterror_admin_form-accordion collapsed">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Remote Logging{/ts}
    </div>
    <div class="crm-accordion-body">
      <p>{ts}By default, Report Error sends an email when an error occurs. In some circumstances, this can generate a lot of e-mails. You may want to collect error reports in a more structured way to simplify analysis. This extension supports (for now) the Gelf protocol, which is supported by Greylog2 or Logstash.{/ts}</p>

      <table class="form-layout-compressed" style="width:100%;">
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_gelf_enable.label}</td>
          <td>
            {$form.reporterror_gelf_enable.html}
          </td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_gelf_hostname.label}</td>
          <td>
            {$form.reporterror_gelf_hostname.html}
            <p class="description">{ts}Ex: log.example.org{/ts}</p>
          </td>
        </tr>
        <tr class="crm-reporterror-form-block">
          <td class="label">{$form.reporterror_gelf_port.label}</td>
          <td>
            {$form.reporterror_gelf_port.html}
            <p class="description">{ts}Ex: 12201{/ts}</p>
          </td>
        </tr>
      </table>
    </div>
  </div>

  {capture assign='symbioticURL'}https://www.symbiotic.coop/en/turn-key-civicrm-hosting?utm_source=reporterror&utm_medium=extension&utm_content=v1{/capture}
  <p>{ts 1=$symbioticURL}Need a hand? <a href="%1" target="_blank">Coop SymbioTIC</a> provides turn-key CiviCRM hosting. Our hosting provides peace of mind, with regular upgrades and 24/7 monitoring. As the authors of this extension, we're pretty good at debugging too! <a href="%1" target="_blank">Read more</a>{/ts}</p>

  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
{/crmScope}
