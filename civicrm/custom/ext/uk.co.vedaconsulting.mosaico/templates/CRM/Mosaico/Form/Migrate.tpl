{crmScope extensionKey='uk.co.vedaconsulting.mosaico'}

{if !$oldTemplates}
  <div class="help">
    <p>
      {ts}Your system appears to be current. There are no templates to migrate.{/ts}
    </p>
  </div>
{/if}

{if $oldTemplates}
<h1>{ts}Introduction{/ts}</h1>
<p class="">
  {ts}In CiviCRM-Mosaico v1.x, the Mosaico editor integrates <strong>indirectly</strong> with CiviMail. Each Mosaico template is mapped to a CiviCRM <em>Message Template</em> (which may be loaded into CiviMail).{/ts}
  {ts}This allows the template to be used in many different ways, but the indirection leads to some user-experiences quirks.{/ts}
</p>
<p class="">
  {ts}In CiviCRM-Mosaico v2.x, the Mosaico editor integrates <strong>directly</strong> with CiviMail.{/ts}
  {ts}This provides a more consistent user-experience, but it changes the data-structure, and it sacrifices some flexibility.{/ts}
</p>
<p class="">
  {ts}This migration assistant will help you move templates from v1.x in v2.x.{/ts}
</p>

<p class="help">
  {ts 1="https://github.com/civicrm/org.civicrm.mosaicomsgtpl"}<strong>Tip</strong>: If you would still like to use <em>Message Templates</em> in v2.x, please visit <a href="%1" target="_blank">%1</a>.{/ts}
</p>
{/if}

<br/>
<h1>{ts}Template Summary{/ts}</h1>

<h3>{ts}Mosaico 1.x Templates{/ts}</h3>
{if $oldTemplates}
  <table>
    <thead>
    <tr>
      <th>{ts}Name{/ts}</th>
      <th>{ts}1.x ID{/ts}</th>
      <th>{ts}Message Template ID{/ts}</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$oldTemplates item=tpl}
      <tr>
        <td>{$tpl.name}</td>
        <td>{$tpl.id}</td>
        <td>{$tpl.msg_tpl_id}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
{else}
  <p>{ts}No templates found{/ts}</p>
{/if}

{capture assign=tplBrowseUrl}{crmURL p='civicrm/a/#/mosaico-template'}{/capture}
<h3>{ts}Mosaico 2.x Templates{/ts} (<a href="{$tplBrowseUrl}" target="_blank">{ts}Manage{/ts}</a>)</h3>
{if $newTemplates}
  <table>
    <thead>
    <tr>
      <th>{ts}Name{/ts}</th>
      <th>{ts}2.x ID{/ts}</th>
      <th>{ts}Message Template ID{/ts}</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$newTemplates item=tpl}
      <tr>
        <td>{$tpl.title}</td>
        <td>{$tpl.id}</td>
        <td>{$tpl.msg_tpl_id}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
{else}
  <p>{ts}No templates found{/ts}</p>
{/if}

{if $msgTplWarning}
  <p>
    {ts}<strong>WARNING</strong>: Some <em>Message Templates</em> are mapped to multiple <em>Mosaico Templates</em>!{/ts}
    {ts}If you setup synchronization, this could cause problems.{/ts}
  </p>
{/if}

{if $oldTemplates}
  <br/>
  <h1>{ts}Suggested Process{/ts}</h1>

  <ol>
    <li>{ts}<u>Copy</u>: Use this Migration Assistant to <em>copy</em> every template from Mosaico v1.x to v2.x. This will let you continue using your old templates.{/ts}</li>
    <li>{ts}<u>Evaluate</u>: Use the new system for a while. If you don't like it, you can downgrade.{/ts}</li>
    <li>{ts}<u>Purge</u>: Use this Migration Assistant to <em>purge</em> all the old templates from v1.x. This removes any in-app notices about the migration process.{/ts}</li>
  </ol>
{/if}

{if $oldTemplates}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/if}

{/crmScope}
