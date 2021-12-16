<div class="crm-mailing-template-category-selector">
  <div class="crm-accordion-wrapper crm-search_filters-accordion">
    <div class="crm-accordion-header">
    {ts}Filter by Mailing Template{/ts}</a>
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <form><!-- form element is here to fool the datepicker widget -->
      <table class="no-border form-layout-compressed template-search-options">
        <tr>
          <td class="crm-contact-form-block-title crm-inline-edit-field">
            {$form.title.label}<br /> {$form.title.html|crmAddClass:huge}
          </td>
          <td class="crm-contact-form-block-category_id crm-inline-edit-field">
            <label>{$form.category_id.label}</label><br /> {$form.category_id.html}
          </td>
        </tr>
      </table>
      </form>
    </div><!-- /.crm-accordion-body -->
  </div>
  <table class="mailing-template-category-selector crm-ajax-table" style="width: 100%;">
    <thead>
    <tr>
      <th data-data="title" cell-class="crmf-title crm-editable" class="crm-mailing-template-category-title">{ts}Title{/ts}</th>
      <th data-data="base" class="crm-mailing-template-category-base">{ts}Base{/ts}</th>
      <th data-data="category_id" cell-class="crmf-category_id crm-editable" cell-data-type="select" cell-data-refresh="true" class="crm-mailing-template-category-category_id">{ts}Category{/ts}</th>
    <!--  <th data-data="links" data-orderable="false" class="crm-mailing-template-category-links">&nbsp;</th> -->
    </tr>
    </thead>
  </table>
  
  {literal}
    <script type="text/javascript">
      (function($, _) {
        var context = {/literal}"{$context}"{literal};
        CRM.$('table.mailing-template-category-selector').data({
          "ajax": {
            "url": {/literal}'{crmURL p="civicrm/ajax/mosaico/template/listing" h=0 q="snippet=4"}'{literal},
            "data": function (d) {
              var category_id = $('.crm-mailing-template-category-selector select#category_id').val() || [];
              d.title = $('.crm-mailing-template-category-selector #title').val(),
              d.category_id = category_id.join(',')
            }
          }
        });
        $(function($) {
          $('.template-search-options :input').change(function(){
            $('table.mailing-template-category-selector').DataTable().draw();
          });
        });
      })(CRM.$, CRM._);
    </script>
  {/literal}

</div>
  
