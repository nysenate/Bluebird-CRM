CRM.$(function($) {
  //TODO after processing multiple rows, if you change the filter it throws a datatables warning
  //this prevents the popup; functionality still works; but it is preferred if we determine the root cause
  $.fn.dataTableExt.sErrMode = "console";
});
