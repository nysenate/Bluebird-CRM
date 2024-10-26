{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
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
  {ts}Print Production Export{/ts}
  </legend>

  <div class="crm-section">
    <div class="content">{include file="CRM/Contact/Form/Task.tpl"}</div>
  </div>

  {if $form.avanti_job_id}
    <div class="crm-section">
      <div class="label">{$form.avanti_job_id.label}</div>
      <div class="content">{$form.avanti_job_id.html}</div>
    </div>
  {/if}

  <div class="crm-section">
    <div class="label">{$form.merge_households.label}</div>
    <div class="content">{$form.merge_households.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.primaryAddress.label}</div>
    <div class="content">{$form.primaryAddress.html}
      <span class="description">By default, we export BOE mailing addresses if they exist and the BOE physical address if flagged as primary. This option overrides that behavior and exports the primary address regardless of what BOE addresses exist.</span>
    </div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.exclude_rt.label}</div>
    <div class="content">{$form.exclude_rt.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.excludeGroups.label}</div>
    <div class="content">{$form.excludeGroups.html}</div>
  </div>

  {if $form.district_excludes}
    <div class="crm-section">
      <div class="label">{$form.district_excludes.label}</div>
      <div class="content">{$form.district_excludes.html}</div>
    </div>
  {/if}

  <div class="crm-section">
    <div class="label">{$form.excludeSeeds.label}</div>
    <div class="content">{$form.excludeSeeds.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.restrict_district.label}</div>
    <div class="content">{$form.restrict_district.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.restrict_state.label}</div>
    <div class="content">{$form.restrict_state.html}</div>
  </div>

  {*8952*}
  <div class="crm-section">
    <div class="label">{$form.restrict_zip.label}</div>
    <div class="content">{$form.restrict_zip.html}
      <br /><span class="description">Enter a comma-separated list of zip codes to filter by.</span>
    </div>
  </div>

  {*7777*}
  <div class="crm-section">
    <div class="label">{$form.di_congressional_district_46.label}</div>
    <div class="content">{$form.di_congressional_district_46.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_ny_assembly_district_48.label}</div>
    <div class="content">{$form.di_ny_assembly_district_48.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_election_district_49.label}</div>
    <div class="content">{$form.di_election_district_49.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_county_50.label}</div>
    <div class="content">{$form.di_county_50.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_county_legislative_district_51.label}</div>
    <div class="content">{$form.di_county_legislative_district_51.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_town_52.label}</div>
    <div class="content">{$form.di_town_52.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_ward_53.label}</div>
    <div class="content">{$form.di_ward_53.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_school_district_54.label}</div>
    <div class="content">{$form.di_school_district_54.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.di_new_york_city_council_55.label}</div>
    <div class="content">{$form.di_new_york_city_council_55.html}</div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.orderBy.label}</div>
    <div class="content">{$form.orderBy.html}</div>
  </div>

  <div class="crm-section">
    <div class="label"></div>
    <div class="content">{$form.buttons.html}</div>
  </div>

  <br />
</fieldset>
</div>
