{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
<!--<dt></dt><dd>{$form.merge_same_household.html} {$form.merge_same_household.label}</dd>-->
<dt></dt>
  <dd>{include file="CRM/Contact/Form/Task.tpl"}</dd>

{if $form.avanti_job_id}
  <dt>{$form.avanti_job_id.label}</dt>
    <dd>{$form.avanti_job_id.html}</dd>
{/if}

{*<dt>{$form.include_households.label}</dt>
  <dd>{$form.include_households.html}</dd>*}

<dt>{$form.exclude_rt.label}<dt>
  <dd>
                {$form.exclude_rt.html}
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


</dt>
  <dd>{$form.buttons.html}</dd>
</dl>
</fieldset>
</div>
