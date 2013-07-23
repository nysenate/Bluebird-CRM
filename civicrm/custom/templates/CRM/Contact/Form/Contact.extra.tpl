{*NYSS 1748*}
{literal}
<script type="text/javascript">
cj(function( ) {
  /*var elementId = "{/literal}#phone_{$blockId}_phone{literal}";
    cj( elementId ).blur( function( ) {
        if ( cj(this).hasClass('valid') ) {
            var currentValue = cj(this).val();
            if ( currentValue ) {
                currentValue = currentValue.replace(/[^\d]/g, "");
                var formattedValue = currentValue.substr(0,3) + '-' + currentValue.substr(3,3) + '-' + currentValue.substr(6,4);
                cj(this).val( formattedValue );
            }
        }
    });*/
});

//suppress address elements link for BOE
cj('[id^=Address_Block_]').each(function(){
  var loctype  = cj(this).find('.location_type_id-address-element input').val();
  var dellink  = cj(this).find('.crm-edit-address-form tr:first a');
  if ( loctype == 6 ) {
    cj(this).find('[id^=streetAddress_] a').remove();
    //remove delete for BOE
    dellink.remove();
    //remove shared address row
    cj(this).find('table.crm-edit-address-form tr:nth-child(2)').remove();
  } else {
    //move delete link
    dellink.addClass('delete_block');
    cj(this).find('table.crm-edit-address-form').before(dellink);
  }
});

//default open address panel
cj('#addressBlockId').addClass('crm-accordion-open').removeClass('crm-accordion-closed');

//ui mods to custom address data
cj('.crm-edit-address-custom_data').parent().addClass('address-custom-cell').removeAttr('colspan');
cj('.crm-edit-address-custom_data').parent().parent().addClass('address-content-block');

//4980 on hold select
cj(document).ready(function(){
  cj('select[id$="_on_hold"]').each(function(){
    cj(this).children('option:first').text('- Active -');
  })
});

//5363 add cancel class
cj('input[name=_qf_Contact_cancel]').addClass('cancel');
</script>
{/literal}


