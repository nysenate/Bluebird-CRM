{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<div class="crm-block crm-form-block crm-printproductionexport-form-block">
<fieldset>
<legend>
{ts}District Export for Merge/Purge{/ts}
</legend>
<dl>

<dt></dt>
  <dd>{include file="CRM/Contact/Form/Task.tpl"}</dd>

{if $form.avanti_job_id}
<dt>Avanti Job ID</dt>
  <dd>{$form.avanti_job_id.html}</dd>
{/if}

<dt>{$form.locType.label}</dt>
  <dd>{$form.locType.html}</dd>

<dt>{$form.includeLog.label}</dt>
  <dd>{$form.includeLog.html}</dd>

<dt>{$form.checkTouched.label}</dt>
  <dd>{$form.checkTouched.html}
    <span class="description"><br />Include two columns (untouched and privacy) to provide more details about contact history.
    <br />
    IF ( Contact Source = BOE AND Is Deceased = 0 AND Trashed = 0 )
      <ul>
        <li>IF there are no email, note, activity (non bulk email) or cases, untouched value = 1</li>
        <li>IF Do Not Phone = true AND Do not Postal Mail = true AND ( Do Not Email = true OR No Bulk Emails = true OR On Hold Bounce = true OR On Hold Opt Out = true ), privacy value = 1</li>
      </ul>
    In both cases, we return 1 in the column if the "untouched" condition is met.
    </span>
  </dd>

<dt>{$form.excludeGroups.label}</dt>
  <dd>{$form.excludeGroups.html}
      {literal}
      <script type="text/javascript">
        cj("select#excludeGroups").crmasmSelect({
            addItemTarget: 'bottom',
            animate: false,
            highlight: true,
            sortable: true,
            respectParents: true
            });
      </script>
      {/literal}
  </dd>

<dt></dt>
  <dd>{$form.buttons.html}</dd>
</dl>
</fieldset>
</div>
