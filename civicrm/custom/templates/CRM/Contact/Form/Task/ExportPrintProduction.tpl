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
<dl>

<dt></dt>
  <dd>{include file="CRM/Contact/Form/Task.tpl"}</dd>

{if $form.avanti_job_id}
  <dt>{$form.avanti_job_id.label}</dt>
    <dd>{$form.avanti_job_id.html}</dd>
{/if}

<dt>{$form.merge_households.label}</dt>
  <dd>{$form.merge_households.html}</dd>

<dt>{$form.primaryAddress.label}</dt>
  <dd>{$form.primaryAddress.html}
      <span class="description">By default, we export BOE mailing addresses if they exist and the BOE physical address if flagged as primary. This option overrides that behavior and exports the primary address regardless of what BOE addresses exist.</span>
      </dd>

<dt>{$form.exclude_rt.label}<dt>
  <dd>{$form.exclude_rt.html}
      {literal}
      <script type="text/javascript">
        cj("select#exclude_rt").crmasmSelect({
            addItemTarget: 'bottom',
            animate: false,
            highlight: true,
            sortable: true,
            respectParents: true
            });
      </script>
      {/literal}
                
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

{if $form.district_excludes}
  <dt>{$form.district_excludes.label}</dt>
    <dd>{$form.district_excludes.html}</dd>
{/if}

<dt>{$form.excludeSeeds.label}</dt>
  <dd>{$form.excludeSeeds.html}</dd>

<dt>{$form.restrict_district.label}</dt>
  <dd>{$form.restrict_district.html}</dd>

<dt>{$form.restrict_state.label}</dt>
  <dd>{$form.restrict_state.html}</dd>

  {*7777*}
  <dt>{$form.di_congressional_district_46.label}</dt>
  <dd>{$form.di_congressional_district_46.html}</dd>

  <dt>{$form.di_ny_assembly_district_48.label}</dt>
  <dd>{$form.di_ny_assembly_district_48.html}</dd>

  <dt>{$form.di_election_district_49.label}</dt>
  <dd>{$form.di_election_district_49.html}</dd>

  <dt>{$form.di_county_50.label}</dt>
  <dd>{$form.di_county_50.html}</dd>

  <dt>{$form.di_county_legislative_district_51.label}</dt>
  <dd>{$form.di_county_legislative_district_51.html}</dd>

  <dt>{$form.di_town_52.label}</dt>
  <dd>{$form.di_town_52.html}</dd>

  <dt>{$form.di_ward_53.label}</dt>
  <dd>{$form.di_ward_53.html}</dd>

  <dt>{$form.di_school_district_54.label}</dt>
  <dd>{$form.di_school_district_54.html}</dd>

  <dt>{$form.di_new_york_city_council_55.label}</dt>
  <dd>{$form.di_new_york_city_council_55.html}</dd>

  <dt>{$form.di_neighborhood_56.label}</dt>
  <dd>{$form.di_neighborhood_56.html}</dd>

<dt>{$form.orderBy.label}</dt>
  <dd>{$form.orderBy.html}</dd>

<dt></dt>
  <dd>{$form.buttons.html}</dd>
</dl>
</fieldset>
</div>
