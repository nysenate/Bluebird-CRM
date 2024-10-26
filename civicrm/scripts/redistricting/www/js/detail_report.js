
var detail_oTable;
var detail_paginate;

$(document).ready(function() {
  init_detail_page();

  $("#toggle_pagination").click(function(e) {
    e.preventDefault();
    toggle_pagination();
  });
});


function init_detail_page() {
  detail_oTable = enable_pagination();
  $('.district-view').show();
  $('#detail_load_text').fadeOut();
}


function enable_pagination() {
  detail_paginate = true;
  return $('table').dataTable({
          "bPaginate": true,
          "bFilter": true,
          "bInfo": true,
          "bDestroy": true,
          "bProcessing": true
  }).css('width', '100%');
}


function disable_pagination() {
  detail_paginate = false;
  return $('table').dataTable({
          "bPaginate": false,
          "bInfo": true,
          "bDestroy": true
  }).css('width', '100%');
}


function toggle_pagination() {
  if (detail_paginate) {
    disable_pagination();
  }
  else {
    // Just reload the page, it's faster
    location.reload();
  }
}

