<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">

<head>
  <title>CiviCRM Mosaico</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <base href="{$baseUrl|htmlspecialchars}">

    {foreach from=$scriptUrls item=scriptUrl}
      <script type="text/javascript" src="{$scriptUrl|htmlspecialchars}">
      </script>
    {/foreach}
    {foreach from=$styleUrls item=styleUrl}
      <link href="{$styleUrl|htmlspecialchars}" rel="stylesheet" type="text/css"/>
    {/foreach}

    {literal}
  <script type="text/javascript">
    $(function() {
      if (!Mosaico.isCompatible()) {
        alert('Your browser is out of date or you have incompatible plugins.  See https://civicrm.stackexchange.com/q/26118/225');
        return;
      }

      var plugins = [];
      var config = {/literal}{$mosaicoConfig}{literal};
      if (window.top.crmMosaicoIframe) {
        window.top.crmMosaicoIframe(window, Mosaico, config, plugins);
      }
      else {
        alert('This page must be loaded in a suitable IFRAME.');
      }
    });
  </script>
    {/literal}
</head>

<body class="mo-standalone">
</body>

</html>
