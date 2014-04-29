<!DOCTYPE html>
<html<?php print $html_attributes . $rdf_namespaces; ?>>
<head>
  <?php print $head; ?>
  <title><?php print $head_title; ?></title>
  <?php //print $styles; ?>
  <?php //print $scripts; ?>

  <!--NYSS we are inserting only what we need-->
  <?php
    //first pull the BB url and rewrite it
    $urlEle = explode('.', $_SERVER['HTTP_HOST']);
    $urlBase = "http://pubfiles.nysenate.gov/{$urlEle[1]}/{$urlEle[0]}/theme/css";

    echo "
      <style type='text/css' media='all'>
        @import url('{$urlBase}/default.css');
        @import url('{$urlBase}/ie8.css');
        @import url('{$urlBase}/ie9.css');
        @import url('{$urlBase}/ie10.css');
        @import url('{$urlBase}/layout.css');
        @import url('{$urlBase}/normalize.css');
        @import url('{$urlBase}/print.css');
        @import url('{$urlBase}/style.css');
      </style>
    ";
  ?>

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
