<!DOCTYPE html>
<html<?php print $html_attributes . $rdf_namespaces; ?>>
<head>
  <?php print $head; ?>
  <title><?php print $head_title; ?></title>
  <?php //print $styles; ?>
  <?php //print $scripts; ?>

  <!--NYSS we are inserting only what we need-->
  <?php
    $bbcfg = get_bluebird_instance_config();
    $base = $bbcfg['public.url.base'];
    $envname = $bbcfg['envname'];
    $instance = $bbcfg['shortname'];
    $cssBase = "$base/$envname/$instance/theme/css";

    echo "
      <style type='text/css' media='all'>
        @import url('{$cssBase}/default.css');
        @import url('{$cssBase}/ie8.css');
        @import url('{$cssBase}/ie9.css');
        @import url('{$cssBase}/ie10.css');
        @import url('{$cssBase}/layout.css');
        @import url('{$cssBase}/normalize.css');
        @import url('{$cssBase}/print.css');
        @import url('{$cssBase}/style.css');
      </style>
    ";
  ?>

  <!--make fonts available-->
  <script src="https://use.typekit.net/wos2cac.js"></script>
  <script>try{Typekit.load({ async: true });}catch(e){}</script>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
</head>
<body class="<?php print $classes; ?>" <?php print $attributes; ?>>
  <div id="skip">
    <a href="#main-menu"><?php print t('Jump to Navigation'); ?></a>
  </div>
  <?php print $page_top; ?>
  <?php print $page; ?>
  <?php print $page_bottom; ?>
</body>
</html>
