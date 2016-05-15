<!-- ______________________ HEADER _______________________ -->
<?php
  $bbcfg = get_bluebird_instance_config();
  $env = $bbcfg['envname'];
  $ins = $bbcfg['shortname'];
  $logo_url = "//pubfiles.nysenate.gov/$env/$ins/common/images/nyss_logo.png";
?>

<header id="header">
  <div class="nyss-logo">
    <img src="<?php print $logo_url;?>" alt="NYS Senate Logo">
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

</div> <!-- /page -->
