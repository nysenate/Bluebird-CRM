{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{*NYSS 12559*}
{literal}
  <style type="text/css">
    div.contact-tagset div.crm-clearfix > label {
      display: block;
    }
    tr.nyss-advsearch-grouptag td {
      padding: 5px !important;
    }
    div#s2id_contact_type {
      margin-top: 0.3em;
    }
  </style>
{/literal}

<table class="form-layout">
  <tr>
    <td>
      {*NYSS 13372*}
      <div id="sortnameselect">
        <label>{ts}Complete OR Partial Name{/ts} <span class="description">(<a href="#" id='searchbyindivflds'>{ts}search by individual name fields{/ts}</a>)</span></label><br />
        {$form.sort_name.html}
      </div>
      <div id="indivfldselect">
        <label>{ts}First/Last Name{/ts}<span class="description"> (<a href="#" id='searchbysortname'>{ts}search by complete or partial name{/ts}</a>)</span></label><br />
        {$form.first_name.html} {$form.last_name.html}
      </div>

      {literal}
      <script type="text/javascript">
        CRM.$(function($) {
          function showIndivFldsSearch() {
            $('#sortnameselect').hide();
            $('#indivfldselect').show();
            $('#sort_name').val('');
            $('#first_name').removeClass('big').addClass('eight').attr('placeholder', ts('First Name'));
            $('#last_name').removeClass('big').addClass('eight').attr('placeholder', ts('Last Name'));
            return false;
          }
          function showSortNameSearch() {
            $('#indivfldselect').hide();
            $('#sortnameselect').show();
            $('#first_name').val('');
            $('#last_name').val('');
            return false;
          }
          $('#searchbyindivflds').click(showIndivFldsSearch);
          $('#searchbysortname').click(showSortNameSearch);

          if ($('#first_name').val() || $('#last_name').val()) {
            showIndivFldsSearch();
          }
          else {
            showSortNameSearch();
          }
        });
      </script>
      {/literal}
    </td>
    <td>
      <label>{ts}Complete OR Partial Email{/ts}</label><br />
      {$form.email.html}
    </td>
    {if $form.contact_type}
      <td><label>{ts}Contact Type(s){/ts}</label><br />
        {$form.contact_type.html}
      </td>
    {else}
      <td>&nbsp;</td>
    {/if}
  </tr>
  <tr class="nyss-advsearch-grouptag"> {*NYSS 12559*}
  {if $form.group}
    <td>
      <div id='groupselect'><label>{ts}Group(s){/ts} <span class="description">(<a href="#" id='searchbygrouptype'>{ts}search by group type{/ts}</a>)</span></label>
        <br />
        {$form.group.html}
    </div>
    <div id='grouptypeselect'>
      <label>{ts}Group Type(s){/ts} <span class="description"> (<a href="#" id='searchbygroup'>{ts}search by group{/ts}</a>)</span></label>
      <br />
      {$form.group_type.html}
        {literal}
        <script type="text/javascript">
        CRM.$(function($) {
          function showGroupSearch() {
            $('#grouptypeselect').hide();
            $('#groupselect').show();
            $('#group_type').select2('val', '');
            return false;
          }
          function showGroupTypeSearch() {
            $('#groupselect').hide();
            $('#grouptypeselect').show();
            $('#group').select2('val', '');
            return false;
          }
          $('#searchbygrouptype').click(showGroupTypeSearch);
          $('#searchbygroup').click(showGroupSearch);

          if ($('#group_type').val() ) {
            showGroupTypeSearch();
          }
          else {
            showGroupSearch();
          }

        });
        </script>
        {/literal}
    </div>
    </td>
  {else}
    <td>&nbsp;</td>
  {/if}
    {if $form.contact_tags}
      <td><label>{ts}Select Tag(s){/ts}</label>
        {$form.contact_tags.html}


        {*NYSS 12559/13006*}
        <br /><br />
        {$form.tag_search.label}  {help id="id-all-tags"}<br />{$form.tag_search.html}

        {*NYSS 13006*}
        {if $form.all_tag_types}
          <br /><br />
          {$form.all_tag_types.html} {$form.all_tag_types.label} {help id="id-all-tag-types"}
        {/if}
      </td>
    {else}
      <td>&nbsp;</td>
    {/if}
    {if $isTagset}
      {*NYSS 12559*}
      {include file="CRM/common/Tagset.tpl"}
    {/if}

    {if ! $isTagset}
      <td colspan="2">&nbsp;</td>
    {/if}
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td>
      <div>
        {$form.phone_numeric.label}<br />{$form.phone_numeric.html}
      </div>
      <div class="description font-italic">
        {ts}Punctuation and spaces are ignored.{/ts}
      </div>
    </td>
    <td>{$form.phone_location_type_id.label}<br />{$form.phone_location_type_id.html}</td>
    <td>{$form.phone_phone_type_id.label}<br />{$form.phone_phone_type_id.html}</td>
  </tr>
  <tr>
    {*NYSS 1 col*}
    <td colspan="1">
      <table class="form-layout-compressed">
      <tr>
        <td colspan="2">
            {$form.privacy_toggle.html} {help id="id-privacy"}
        </td>
      </tr>
      <tr>
        <td>
            {$form.privacy_options.html}
        </td>
        <td style="vertical-align:middle">
            <div id="privacy-operator-wrapper">{$form.privacy_operator.html} {help id="privacy-operator"}</div>
        </td>
      </tr>
      </table>
      {literal}
        <script type="text/javascript">
          cj("select#privacy_options").change(function() {
            if (cj(this).val() && cj(this).val().length > 1) {
              cj('#privacy-operator-wrapper').show();
            } else {
              cj('#privacy-operator-wrapper').hide();
            }
          }).change();
        </script>
      {/literal}
    </td>
    <td id="privacyOptionNotes_cell"></td>{*NYSS*}
    <td colspan="3">
      {$form.preferred_communication_method.label}<br />
      {$form.preferred_communication_method.html}<br />
      <div class="spacer"></div>
      {*NYSS 13006*}
      {$form.email_on_hold.label}<br />
      {$form.email_on_hold.html}
    </td>
  </tr>
  <tr>
    <td>
      {$form.contact_source.label} {help id="id-source" file="CRM/Contact/Form/Contact"}<br />
      {$form.contact_source.html}
    </td>
    <td>
      {$form.job_title.label}<br />
      {$form.job_title.html}
    </td>
    <td colspan="3">
      {$form.preferred_language.label}<br />
      {$form.preferred_language.html}
    </td>
  </tr>
  <tr>
    <td>
       {$form.contact_id.label} {help id="id-internal-id" file="CRM/Contact/Form/Contact"}<br />
       {$form.contact_id.html}
    </td>
    <td>
       {$form.external_identifier.label} {help id="id-external-id" file="CRM/Contact/Form/Contact"}<br />
       {$form.external_identifier.html}
    </td>
    <td>
      {if $form.uf_user}
        {$form.uf_user.label} {$form.uf_user.html}
        <div class="description font-italic">
          {ts 1=$config->userFramework}Does the contact have a %1 Account?{/ts}
        </div>
      {else}
        &nbsp;
      {/if}
    </td>
  </tr>
</table>
