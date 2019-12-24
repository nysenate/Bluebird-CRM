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

  {capture assign=msgTplURL}{crmURL p='civicrm/admin/messageTemplates' q="reset=1&activeTab=mosaico"}{/capture}
  {literal}
  <script type="text/javascript">
    $(function() {
      if (!Mosaico.isCompatible()) {
        alert('Your browser is out of date or you have incompatible plugins.  See https://civicrm.stackexchange.com/q/26118/225');
        return;
      }

      var plugins;
      // A basic plugin that expose the "viewModel" object as a global variable.
      // plugins = [function(vm) {window.viewModel = vm;}];
      var config = {/literal}{$mosaicoConfig}{literal};
      var ok = Mosaico.init(config, plugins);
      if (!ok) {
        console.log("Missing initialization hash, redirecting to main entrypoint");
      }

      addCustomButton();
    });
    function addCustomButton() {
      var msgTplURL = "{/literal}{$msgTplURL}{literal}";
      if ($('#page .rightButtons').is(':visible')) {
        $("#page .rightButtons").append('<a href="' + msgTplURL + '" class="ui-button">Done</a>');
      } else {
        console.log('timeout 50');
        setTimeout(addCustomButton, 50);
      }
    }
  </script>
  {/literal}
</head>

<body class="mo-standalone">
</body>

</html>
