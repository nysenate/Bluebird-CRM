<!-- ______________________ HEADER _______________________ -->

<header id="header">
  <div class="nyss-logo">
    <img src="/sites/default/themes/BluebirdPublic/images/nyss_logo.png" alt="NYS Senate Logo">
  </div>
</header> <!-- /header -->

<div id="page" class="<?php print $classes; ?>"<?php print $attributes; ?>>
  <!-- ______________________ MAIN _______________________ -->

  <div id="main" class="clearfix">

    <section id="content">

        <div id="content-area">
          <?php print render($page['content']) ?>
        </div>

        <?php print $feed_icons; ?>

    </section> <!-- /content-inner /content -->

  </div> <!-- /main -->

  <!-- ______________________ FOOTER _______________________ -->

  <?php if ($page['footer']): ?>
    <footer id="footer">
      <?php print render($page['footer']); ?>
    </footer> <!-- /footer -->
  <?php endif; ?>

</div> <!-- /page -->

<script type="text/javascript">
  (function ($) {
    $('div.civi-search-section').remove();
    $('div#bluebirds').remove();
    $('div.content div.clear:first').remove();
    $('div.civi-navigation-section').remove();
    $('div#crm-seal').remove();
  })(jQuery);
</script>
