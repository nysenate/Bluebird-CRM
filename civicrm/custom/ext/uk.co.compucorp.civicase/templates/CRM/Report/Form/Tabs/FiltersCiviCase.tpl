{literal}
<script type="text/javascript">
  CRM.$(function($) {
    $('.crm-ajax-accordion').on('click', '.crm-accordion-header:not(.active)', function() {
      loadPanes($(this).attr('id'));
    });
    $('.crm-ajax-accordion:not(.collapsed) .crm-accordion-header').each(function() {
      loadPanes($(this).attr('id'));
    });

    /**
     * Loads snippet based on id of crm-accordion-header
     * @params {String} id
     */
    function loadPanes(id) {
      var url = "{/literal}{crmURL p=`$currentPath` q="qfKey=`$qfKey`&filterPane=" h=0}{literal}" + id;
      var header = $('#' + id);
      var body = $('.crm-accordion-body.' + id);
      if (header.length > 0 && body.length > 0) {
        body.html('<div class="crm-loading-element"><span class="loading-text">{/literal}{ts escape='js'}Loading{/ts}{literal}...</span></div>');
        header.addClass('active');
        CRM.loadPage(url, {target: body, block: false});
      }
    }
  });
</script>
{/literal}

<div id="report-tab-set-filters" class="civireport-criteria">
  <div class="crm-accordion-wrapper crm-accordion collapsed">
    <div class="crm-accordion-header">As At Reporting</div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <table class="report-layout">
        <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-{$tableName}" {if $field.no_display} style="display: none;"{/if}>
          <td class="label report-contents">{ts}As At Date{/ts}</td>
          <td> {$form.as_at_date.html} <span>Please note that this filter will not exclude activities and/or check the actual logs for data at that date.</span></td>
        </tr>
      </table>
    </div>
  </div>
  <table class="report-layout">
    {assign var="counter" value=1}
    {*{$filters|@var_dump}*}
    {foreach from=$filters item=table key=tableName}
    {assign  var="filterCount" value=$table|@count}
    {* Wrap custom field sets in collapsed accordion pane. *}
    {if $filterGroups.$tableName.group_title and $filterCount gte 1}
    {* we should close table that contains other filter elements before we start building custom group accordion
     *}
  {if $counter eq 1}
  </table>
  {assign var="counter" value=0}
  {/if}
  <div class="crm-accordion-wrapper crm-ajax-accordion crm-{$filterGroups.$tableName.pane_name}-accordion {if $filterGroups.$tableName.open eq 'true'} {else}collapsed{/if}">
    <div class="crm-accordion-header" id="{$filterGroups.$tableName.pane_name}">
      {$filterGroups.$tableName.group_title}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body {$filterGroups.$tableName.pane_name}">
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
  {assign var=closed value="1"} {*-- ie table tags are closed-- *}
  {else}
  {assign var=closed     value="0"} {*-- ie table tags are not closed-- *}
  {/if}
  {/foreach}
  {if $closed eq 0 }
    </table>
  {/if}
</div>
