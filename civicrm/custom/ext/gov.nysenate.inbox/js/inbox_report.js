if (!String.prototype.capitalize) {
  Object.defineProperty(String.prototype, 'capitalize',
    {
      writable: true,
      value: function (first_only, undefined) {
        if (first_only === undefined) {
          first_only = false;
        }
        r = first_only ? /(?!^\/)\b([a-z])/ : /(?!^\/)\b([a-z])/g;
        return this.replace(r, function (m) {
          return m.toUpperCase()
        });
      }
    });
}

cj(document).ready(function () {
  // Set the onChange events for date selection.
  cj('body').on('change', 'select#date_range_relative, input.hasDatepicker', function (e) {
    if (validateDates()) {
      getReports();
    }
  });

  // Set the accordion toggle for advanced filters.
  cj('body').on('click', '.advanced-filter-switch', function (e) {
    cj('.advanced-filter-container').toggleClass('active');
  });

  // Set the onClick selection for the stats bar.
  cj(".stats-overview").on('click', function (e) {
    e.preventDefault();
    cj(".stats-overview").removeClass('active');
    cj(this).addClass('active');
    setTableFilters();
  });

  // Set the onClick for adding an advanced filter.
  cj("#advanced-filter-add-new").on('click', function (e) {
    e.preventDefault();

    var col = Number(cj("#advanced-filter-field-select option:selected").val()),
      lbl = cj("#advanced-filter-field-select option:selected").text(),
      term = cj('#advanced-filter-field-text').val();

    if (col >= 0 && term) {
      cj('.advanced-filter-current-item[data-col=' + col + ']').remove();
      var new_div = cj('<div/>').addClass('advanced-filter-current-item')
        .attr('data-col', col)
        .attr('data-term', term)
        .append(cj('<div/>').addClass('filter-item-remove'))
        .append(cj('<div/>').addClass('filter-item-label')
          .html(lbl))
        .append(cj('<div/>').addClass('filter-item-term')
          .html(term));
      cj('.advanced-filter-current').append(new_div);
      setTableFilters();
    }
  });

  // Set the onClick handler for clearing an individual advanced filter.
  cj(".advanced-filter-current").on('click', ".filter-item-remove", function (e) {
    cj(e.target).closest('.advanced-filter-current-item').remove();
    setTableFilters();
  });

  // Set the onClick handler for clearing all advanced filters.
  cj("#advanced-filter-clear-all").on('click', function (e) {
    e.preventDefault();
    cj('.advanced-filter-current-item').remove();
    setTableFilters();
  });

  // Set the default value for date range, and trigger the query.
  cj('select#date_range_relative').val('this.month').change();

});//end cj.ready()

function setTableFilters() {
  var stats = cj('.stats-overview.active'),
    found = stats.length ? stats.attr('class').match('stats-(matched|unmatched|cleared|deleted)') : [],
    advfilt = cj('.advanced-filter-current-item'),
    table = cj('#sortable-results').DataTable();

  // Reset the table's filters.
  table.search('').columns().search('').draw();

  // Add any filter from the stats bar.
  if (found[1]) {
    table.column(5).search('\\b' + found[1] + '\\b',true);
  }

  // Add any advanced filters.
  if (advfilt.length) {
    advfilt.each(function (k, v) {
      var col = Number(v.attributes['data-col'].nodeValue),
        term = v.attributes['data-term'].nodeValue;
      if (col >= 0 && term) {
        table.column(col).search(term);
      }
    });
  }

  // Redraw the table.
  table.draw();
}

function validateDates() {
  var oDates = cj('input.hasDatepicker'),
    datesel = cj('#date_range_relative').val(),
    d1 = oDates[0].value,
    d2 = oDates[1].value;
  return (datesel !== "0" || (d1 !== '' && d2 != '' && d1 <= d2));
}

// Create shortended String with title tag for hover
// If subject is null return N/A
function shortenString(subject, length) {
  if (subject) {
    if (subject.length > length) {
      var safe_subject = '<span title="' + subject + '" data-sort="' + subject + '">' + subject.substring(0, length) + "...</span>";
      return safe_subject;
    }
    else {
      return '<span data-sort="' + subject + '">' + subject + '</span>';
    }
  }
  else {
    return '<span title="Not Available" data-sort="Not Available"> N/A </span>';
  }
}

function resetStatsRow(data) {
  for (var x in data) {
    var y = x.toLowerCase();
    if (cj('a.stats-' + y).length) {
      cj('a.stats-' + y + ' .stat-value').html(data[x]);
    }
  }
}

function constructRow(data) {
  var
    new_cell = cj('<td/>'),
    new_row = cj('<tr></tr>')
      .attr({
        id: data.id, "data-id": data.activity_id, "data-contact_id": data.matched_to
      })
      .addClass("imapper-message-box " + data.status_string);

  // Sender's name.
  new_row.append(
    new_cell.clone().addClass("imap_column").html(shortenString(data.fromName, 40))
  );

  // Matched contact.
  if (data.contactType) {
    var
      icon_link = cj('<a></a>').addClass('crm-summary-link')
        .attr('href', '/civicrm/profile/view?reset=1&gid=13&snippet=4&id=' + data.matched_to)
        .append(cj('<div></div>').addClass('icon crm-icon ' + data.contactType + '-icon')),
      name_link = cj('<a></a>')
        .html(shortenString(data.fromName, 19))
        .attr({
          href: '/civicrm/contact/view?reset=1&cid=' + data.matched_to,
          title: data.fromName
        });
    new_row.append(
      new_cell.clone()
        .addClass('imap_name_column')
        .attr({"data-firstName": data.firstName, "data-lastName": data.lastName})
        .append(icon_link)
        .append(name_link)
    );
  }
  else {
    new_row.append(
      new_cell.clone().addClass('imap_name_column').html('&nbsp;')
    );
  }

  // Subject.
  new_row.append(
    new_cell.clone()
      .addClass("imap_subject_column")
      .html(shortenString(data.subject, 40))
  );

  // Edit date.
  new_row.append(
    new_cell.clone()
      .addClass('imap_date_column')
      .append(
        cj('<span/>')
          .attr({"data-sort": data.updated_date_unix, title: data.updated_date_long})
          .html(data.updated_date_short)
      )
  );

  // Sent date.
  new_row.append(
    new_cell.clone()
      .addClass('imap_date_column')
      .append(
        cj('<span/>')
          .attr({"data-sort": data.email_date_unix, title: data.email_date_long})
          .html(data.email_date_short)
      )
  );

  // Status.
  var status_content = cj('<a/>')
    .addClass('crm-summary-link mail-merge-hover')
    .attr('href', '#')
    .append(
      cj('<span/>').addClass("mail-merge-filter-data").html(data.status_icon_class)
    )
    .append(
      cj('<div/>').addClass("icon crm-icon mail-merge-icon mail-merge-" + data.status_icon_class)
    )
    .append(
      cj('<div/>').addClass("crm-tooltip")
        .html(data.status_string)
        .wrap('<div/>').parent()
        .addClass('crm-tooltip-wrapper')
    );
  new_row.append(new_cell.clone().attr("data-search", data.status_icon_class).append(status_content));

  // Tags
  var tag_content = '';
  if (Number(data.tagCount) > 0) {
    tag_content = cj('<div/>').addClass('mail-merge-tags mail-merge-icon icon crm-icon')
      .wrap('<a/>')
      .parent()
      .addClass("crm-summary-link mail-merge-hover")
      .attr('href', '/civicrm/imap/ajax/reports/getTags?id=' + data.id);
  }
  new_row.append(new_cell.clone().addClass('imap_date_column').append(tag_content));

  // Forwarder.
  var forward_content = cj("<span/>")
    .attr('data-sort', data.forwarder.replace("@", "_"))
    .html(shortenString(data.forwarder, 14));
  new_row.append(new_cell.clone().append(forward_content));

  return new_row;
}

function getReports() {
  // Make sure only good date ranges are submitted.
  if (!validateDates()) {
    console.log("WARNING: getReports() called without proper date populations");
    return;
  }

  // Set some references.
  var table_body = cj('#imapper-messages-list'),
    range = {
      date_range: cj('#date_range_relative').val(),
      date_from: cj('#date_range_low').val(),
      date_to: cj('#date_range_high').val()
    };

  // Erase the existing table body and add the "loading data" placeholder.
  if (cj.fn.DataTable.isDataTable("#sortable-results")) {
    cj("#sortable-results").DataTable().destroy();
  }
  table_body.html('<td valign="top" colspan="7" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading message data <img src="/sites/all/ext/gov.nysenate.inbox/img/loading.gif"/></span></span></td>');

  // Send the report request
  cj.ajax({
    url: '/civicrm/nyss/inbox/ajax/report',
    data: range,
    success: function (data, status) {
      var reports = data.data,
        new_body = cj('<tbody></tbody>');

      //console.log(data);
      if (!(reports.total == 0 || reports.Messages == null)) {
        cj.each(reports.Messages, function (key, value) {
          new_body.append(constructRow(value));
        });
      }

      // Reset the stats row.
      resetStatsRow(reports);

      // Redraw the datatable.
      table_body.html(new_body.html());
      ReportTable();
    },
    error: function () {
      CRM.alert('Unable to load messages', '', 'error');
    }
  });
}

function ReportTable() {
  cj("#sortable-results").DataTable({
    "sDom": '<"table-controls"<"paging"p><"filter"f>><"length-subset"<"length"l><"info"i>><"clear">rt <p><"clear">',
    "sPaginationType": "full_numbers",
    "aaSorting": [[3, "desc"]],
    "aoColumnDefs": [{"sType": "title-string", "aTargets": [3, 4]},
    ],
    "aoColumns": [{"sWidth": "12%"},
      {"sWidth": "18%"},
      {"sWidth": "14%"},
      {"sWidth": "12%"},
      {"sWidth": "10%"},
      {"sWidth": "1%"},
      {"sWidth": "1%"},
      {"sWidth": "22%"},
    ],
    'aTargets': [1],
    "iDisplayLength": 50,
    "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, 'All']],
    "bAutoWidth": false,
    "bFilter": true,
    "oLanguage": {
      "sEmptyTable": "No records found"
    },
  });
}
