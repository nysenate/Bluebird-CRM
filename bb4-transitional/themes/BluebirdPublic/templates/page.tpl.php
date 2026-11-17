<!-- ______________________ HEADER _______________________ -->
<?php
  $bbcfg = get_bluebird_instance_config();
  $base = $bbcfg['public.url.base'];
  $envname = $bbcfg['envname'];
  $instance = $bbcfg['shortname'];
  $logo_url = "$base/$envname/$instance/common/images/nyss_logo.png";
?>

<header id="header">
  <div class="nyss-logo">The New York State Senate</div>
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

</div> <!-- /page -->
