CRM.$(function($) {
  //remove entire share address row
  $('div.shared-address-update-employer').closest('tr').remove();

  //change edit address elements text
  $('a[title="Edit Address Elements"]')
    .prop('title', 'View Address Elements')
    .text('View Address Elements');

  $('a[title="Edit Street Address"]')
    .prop('title', 'View Complete Street Address')
    .text('View Complete Street Address');

  //insert address parsed values
  var addrVals = CRM.vars.NYSS;
  addrVals = Object.entries(addrVals);
  //console.log('addrVals: ', addrVals);

  for (const[f, v] of addrVals) {
    //console.log('f: ', f, 'v: ', v);

    if (f.indexOf('address_') >= 0) {
      $('input#' + f)
        .parent('span')
        .css('min-width', '50px')
        .css('display', 'inline-block')
        .prepend(v);
    }
  }
});
