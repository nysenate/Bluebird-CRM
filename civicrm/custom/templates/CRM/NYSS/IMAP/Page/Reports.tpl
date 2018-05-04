<div class="crm-content-block imapperbox " id="Reports">
  <div class="full">
    <h1>Inbox Reports
      <select class="form-select range" id="range" name="range">
        <option value="0">All Time</option>
        <option value="1">Last 24 hours</option>
        <option value="7">Last 7 days</option>
        <option value="30" selected>Last 30 days</option>
        <option value="90">Last 90 days</option>
        <option value="365">Last Year</option>
      </select>
    </h1>
    <div class="crm-contact-form-block-range_filter crm-left crm-margin-right">
      {$form.range_filter.html|crmAddClass:big}
    </div>
    <div class="crm-contact-form-block-search crm-left crm-margin-right">
      {$form.search_filter.html|crmAddClass:twelve}
    </div>

    <div class="stats">
      <a href="#Total" class="stats_overview Total">Total<span id="total"> </span></a>
      <a href="#Unmatched" class="stats_overview Unmatched"><div class="icon mail-merge-icon mail-merge-unmatched"></div>Unmatched<span id="total_unmatched"> </span></a>
      <a href="#Matched" class="stats_overview Matched"><div class="icon mail-merge-icon mail-merge-matched"></div>Matched<span id="total_Matched"> </span></a>
      <a href="#Cleared" class="stats_overview Cleared"><div class="icon mail-merge-icon mail-merge-cleared"></div>Cleared<span id="total_Cleared"> </span></a>
      <a href="#Deleted" class="stats_overview Deleted"><div class="icon mail-merge-icon mail-merge-deleted"></div>Deleted<span id="total_Deleted"> </span></a>
<!-- removed per NYSS #8396
      <a href="#Errors" class="stats_overview Errors"><div class="icon mail-merge-icon mail-merge-errors"></div>Errors<span id="total_Errors"> </span></a>
-->
    </div>
  </div>
  <div id="top"></div>
  <div class="full">
    <table id="sortable_results" class="">
      <thead>
        <tr class="list_header">
          <th class="Name">Sender Info</th>
          <th class="Name">Matched To</th>
          <th class="Subject">Subject</th>
          <th class="Date">Last Edited</th>
          <th class="Date_Sent">Date Sent</th>
          <th class="Status">Status</th>
          <th class="Tags">Tags</th>
          <th class="Forwarded">Forwarded By</th>
        </tr>
      </thead>
      <tbody id="imapper-messages-list">
        <td valign="top" colspan="8" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading Message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>
      </tbody>
    </table>
  </div>
</div>
