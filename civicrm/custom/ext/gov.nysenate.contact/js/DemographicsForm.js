//NYSS 3527 - set comm preferences
var storeExisting = {};

function processDeceased() {
  if (cj("#is_deceased").is(':checked')) {

    //privacy fields
    cj('input[id^=privacy]').each(function(){
      storeExisting[cj(this).prop('id')] = cj(this).prop('checked');
      cj(this).prop('checked', 'checked').prop('onclick', 'return false');
    });

    //opt out
    storeExisting['is_opt_out'] = cj('#is_opt_out').prop('checked')
    cj('#is_opt_out').prop('checked', 'checked').prop('onclick', 'return false');

    //preferred fields
    cj('input[id^=preferred]').each(function(){
      storeExisting[cj(this).prop('id')] = cj(this).prop('checked');
      cj(this).removeAttr('checked').prop('onclick', 'return false');
    });
  }
  else {
    //cycle through stored array when unchecking and restore to previous values
    cj.each(storeExisting, function(id, setting) {
      cj('#' + id).prop('checked', setting).removeAttr('onclick');
    });
  }
}

CRM.$(function($) {
  processDeceased();
});
