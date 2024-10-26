(function($) {
  $(document)
    .on('crmLoad', '#civicrm-menu', function() {
      if (CRM.vars && CRM.vars.recentmenu) {
        CRM.menubar.updateItem(CRM.vars.recentmenu);
      }
    })
    .ajaxSuccess(function(event, xhr, settings) {
      try {
        if ((!settings.dataType || settings.dataType == 'json') && xhr.responseText) {
          var response = $.parseJSON(xhr.responseText);
          if (typeof(response.recentmenu_items) == 'object') {
            CRM.vars.recentmenu = response.recentmenu_items;
            CRM.menubar.updateItem(response.recentmenu_items);
          }
        }
      }
      // Ignore errors thrown by parseJSON and the menubar
      catch (e) {}
    });
})(CRM.$);
