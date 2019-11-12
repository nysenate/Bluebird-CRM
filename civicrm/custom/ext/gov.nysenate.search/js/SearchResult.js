(function($) {
  $(document)
    .on('crmLoad', '#civicrm-menu', function() {
      if (CRM.vars && CRM.vars.searchresults && !CRM.menubar.getItem('recent_items')) {
        CRM.menubar.addItems(-1, null, [CRM.vars.searchresults]);
      }
    })
    .ajaxSuccess(function(event, xhr, settings) {
      try {
        if ((!settings.dataType || settings.dataType == 'json') && xhr.responseText) {
          var response = $.parseJSON(xhr.responseText);
          if (typeof(response.searchresults_items) == 'object') {
            CRM.vars.searchresults = response.searchresults_items;
            CRM.menubar.updateItem(response.searchresults_items);
          }
        }
      }
        // Ignore errors thrown by parseJSON and the menubar
      catch (e) {}
    });
})(CRM.$);
