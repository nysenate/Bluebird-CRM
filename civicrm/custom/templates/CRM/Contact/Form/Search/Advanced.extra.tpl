<div id="all_tag_types">{$form.all_tag_types.html} {$form.all_tag_types.label}</div>

{literal}
<script type="text/javascript">
  //2649 improve consistency between AdvSearch popup and full screen
  cj('.crm-advanced_search_form-accordion .crm-accordion-body').addClass('accordion_wrapper');

  //3815 move privacy options note
  var pof = '{/literal}{$form.custom_64.html}{literal}';
  var pon = '<tr><td colspan="2">Privacy Option Notes  ' + pof + '</td></tr>';
  cj('table.search-privacy-options tbody').append(pon);

  //5556
  cj('#all_tag_types').appendTo(cj('.contact-tagset-296-section .content'));

  //6383 repeat reset form button
  var rfb = cj('div.reset-advanced-search').clone();
  rfb.css('float', 'left').css('margin-top', '3px');
  cj('input#_qf_Advanced_refresh').after(rfb);

  //5647 default open address block
  cj('#location').parent().trigger('click');

  if ( cj('div.crm-advanced_search_form-accordion div.crm-accordion-header').css('display') == 'block' ) {
    cj('div.crm-advanced_search_form-accordion div.crm-accordion-header a.helpicon').
      appendTo('div.crm-title h1.title');
  }

  //toggle adv search panel; compensate for wrapper panel method
  cj('.crm-ajax-accordion').on('click', '.crm-accordion-header', function() {
    var pid = cj(this).attr('id');
    if ( pid ) {
      cj('div.crm-accordion-body.' + pid).toggle();
    }
    else {
      pid = cj(this).parent('div').attr('id');
      if ( pid ) {
        cj('#' + pid + ' div.crm-accordion-body').toggle();
      }
    }

    if ( cj(this).parent('div').hasClass('collapsed') ) {
      cj(this).parent('div').removeClass('collapsed');
    }
    else {
      cj(this).parent('div').addClass('collapsed');
    }
  });
</script>
{/literal}
