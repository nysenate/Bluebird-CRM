{* Report form criteria section *}
{literal}
  <script type="text/javascript">
{/literal}
{foreach from=$table item=field key=fieldName}
  {literal}var val = "dnc";{/literal}
  {assign var=fieldOp     value=$fieldName|cat:"_op"}
  {if !($field.operatorType & 4) && !$field.no_display && $form.$fieldOp.html}
    {literal}var val = document.getElementById("{/literal}{$fieldOp}{literal}").value;{/literal}
  {/if}
  {literal}showHideMaxMinVal( "{/literal}{$fieldName}{literal}", val );{/literal}
{/foreach}

{literal}

  /**
   * Show/Hide min value for form filter fields.
   */
  function showHideMaxMinVal( field, val ) {
    var fldVal    = field + "_value_cell";
    var fldMinMax = field + "_min_max_cell";
    if ( val == "bw" || val == "nbw" ) {
      cj('#' + fldVal ).hide();
      cj('#' + fldMinMax ).show();
    } else if (val =="nll" || val == "nnll") {
      cj('#' + fldVal).hide() ;
      cj('#' + field + '_value').val('');
      cj('#' + fldMinMax ).hide();
    } else {
      cj('#' + fldVal ).show();
      cj('#' + fldMinMax ).hide();
    }
  }

  CRM.$(function($) {
    $('.crm-report-criteria-groupby input:checkbox').click(function() {
      $('#fields_' + this.id.substr(10)).prop('checked', this.checked);
    });
    {/literal}{if $displayToggleGroupByFields}{literal}
      $('.crm-report-criteria-field input:checkbox').click(function() {
        $('#group_bys_' + this.id.substr(7)).prop('checked', this.checked);
      });
      {/literal}{/if}{literal}
    });
  </script>
{/literal}
