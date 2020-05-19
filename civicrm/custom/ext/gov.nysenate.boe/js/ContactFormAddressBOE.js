CRM.$(function($) {
  var boeAddressBlockId = CRM.vars.NYSS.boeAddressBlockId;
  //console.log('boeAddressBlockId: ', boeAddressBlockId);

  if (boeAddressBlockId) {
    $(document).ready(function () {
      //remove delete address block
      $('div#Address_Block_' + boeAddressBlockId + ' a.delete_block').remove();
      $('div#Address_Block_' + boeAddressBlockId + ' a[title="Delete Address Block"]').remove();

      //set BOE loc type and remove fields
      $('div#Address_Block_' + boeAddressBlockId + ' span.location_type_id-address-element').html('<label for="address_' + boeAddressBlockId + '_location_type_id">Address Location Type: BOE</span>');

      //remove shared address row
      $('div#Address_Block_' + boeAddressBlockId + ' div#shared-address-' + boeAddressBlockId).closest('tr').remove();

      //change edit address elements text
      $('div#Address_Block_' + boeAddressBlockId + ' a[title="Edit Address Elements"]')
        .prop('title', 'View Address Elements')
        .text('View Address Elements');

      $('div#Address_Block_' + boeAddressBlockId + ' a[title="Edit Street Address"]')
        .prop('title', 'View Complete Street Address')
        .text('View Complete Street Address');
    });
  }
});
