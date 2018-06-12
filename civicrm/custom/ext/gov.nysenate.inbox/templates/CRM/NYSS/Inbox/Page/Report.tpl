<div class="crm-content-block nyss-inbox-report-container" id="Reports">
  <div class="full" id="top">
    <div class="header-title">
      <h1>Inbox Report</h1>
      <div class="date-range-container">
        {include file="CRM/Core/DateRange.tpl" fieldName="date_range" from='_low' to='_high'}
      </div>
    </div>
    <div class="stats">
      <a href="#" class="stats-overview stats-total">Total<span class="stat-value">&nbsp;</span></a>
      <a href="#" class="stats-overview stats-unmatched"><div class="icon mail-merge-icon mail-merge-unmatched"></div>Unmatched<span class="stat-value">&nbsp;</span></a>
      <a href="#" class="stats-overview stats-matched"><div class="icon mail-merge-icon mail-merge-matched"></div>Matched<span class="stat-value">&nbsp;</span></a>
      <a href="#" class="stats-overview stats-cleared"><div class="icon mail-merge-icon mail-merge-cleared"></div>Cleared<span class="stat-value">&nbsp;</span></a>
      <a href="#" class="stats-overview stats-deleted"><div class="icon mail-merge-icon mail-merge-deleted"></div>Deleted<span class="stat-value">&nbsp;</span></a>
    </div>
  </div>
  <div class="advanced-filter-container">
    <div class="advanced-filter-switch">Advanced Filter</div>
    <div class="advanced-filters">
      <div class="advanced-filter-add">
        <label for="advanced-filter-field-select">Choose a column:
        <select name="advanced_filter_field_select" id="advanced-filter-field-select">
          sender, matched to, subject, forwarded by
          <option value="-1" disabled="disabled" selected="selected">Filter by ...</option>
          <option value="0">Sender Info</option>
          <option value="1">Matched To</option>
          <option value="2">Subject</option>
          <option value="7">Forwarded By</option>
        </select>
        </label>
        <label for="advanced-filter-field-text">Search Term:
        <input name="advanced_filter_field_text" id="advanced-filter-field-text" placeholder="Search term ..." /></label>
        <button name="advanced_filter_add_new" id="advanced-filter-add-new" value="add">Add</button>
        <button name="advanced_filter_clear_all" id="advanced-filter-clear-all" value="clear">Clear All</button>
      </div>
      <div class="advanced-filter-current"></div>
    </div>
  </div>
  <div class="full">
    <table id="sortable-results" class="">
      <thead>
        <tr class="list-header">
          <th class="Name">Sender Info</th>
          <th class="Name">Matched To</th>
          <th class="Subject">Subject</th>
          <th class="Date">Last Edited</th>
          <th class="Date-Sent">Date Sent</th>
          <th class="Status">Status</th>
          <th class="Tags">Tags</th>
          <th class="Forwarded">Forwarded By</th>
        </tr>
      </thead>
      <tbody id="imapper-messages-list">
        <td valign="top" colspan="8" class="dataTables-empty"><span class="loading-row"><span class="loading-message">Loading Message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>
      </tbody>
    </table>
  </div>
</div>
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $('.hasDatepicker').datepicker({
        onSelect: function (d, i) {
          if (d !== i.lastVal) {
            $(this).change();
          }
        }
      });
    });
  </script>
{/literal}