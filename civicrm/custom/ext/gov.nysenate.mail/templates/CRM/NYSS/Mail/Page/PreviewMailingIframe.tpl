<iframe src="{$previewUrl}" id="mailing-preview-wrapper" style="width: 100%;"></iframe>

{literal}
<script type="text/javascript">
  CRM.$(function($) {
    $(document).ready(function () {
      var iframe = $('iframe#mailing-preview-wrapper');
      var h = $(window).height();
      h = h * 0.8;
      iframe.height(h);
    });
  });
</script>

<style type="text/css">
  .crm-container.ui-dialog .ui-dialog-content[id*='crm-ajax-dialog'] {
    padding: 0 !important;
    margin: 0 !important;
  }
</style>
{/literal}
