CRM.$(function($) {
  var specialBlockIds = CRM.vars.NYSS.specialBlockIds;
  //console.log('specialBlockIds: ', specialBlockIds);

  $.each(specialBlockIds, function(index, blockId) {
    $(document).ready(function () {
      //remove delete address block
      $('div#Address_Block_' + blockId + ' a.delete_block').remove();
      $('div#Address_Block_' + blockId + ' a[title="Delete Address Block"]').remove();

      //set BOE loc type and remove fields
      //removing in lieu of field freeze via buildForm hook -- #13829
      //failed to set hidden input field causing form validation on save
      //$('div#Address_Block_' + boeAddressBlockId + ' span.location_type_id-address-element').html('<label for="address_' + boeAddressBlockId + '_location_type_id">Address Location Type: BOE</span>');

      //remove shared address row
      $('div#Address_Block_' + blockId + ' div#shared-address-' + blockId).closest('tr').remove();

      //change edit address elements text
      $('div#Address_Block_' + blockId + ' a[title="Edit Address Elements"]')
        .prop('title', 'View Address Elements')
        .text('View Address Elements');

      $('div#Address_Block_' + blockId + ' a[title="Edit Street Address"]')
        .prop('title', 'View Complete Street Address')
        .text('View Complete Street Address');
    });
  });
});
