(function($) {
  $('input#case_taglist_292').on('select2-selecting',function(e, data) {
    // if the choice matches ':::value' then we need to create it
    // and get a real id via the API
    if (/:::value$/.test(e.choice.id)) {
      CRM.api3('tag', 'save_position', {value:e.choice.id}, false)
        .done(function(result) {
          if (result.is_error ===0) {
            e.choice.id = result.result;
            var updatedData = $(e.target).select2("data");
            updatedData.push(e.choice);
            $(e.target).select2("data",updatedData);
          }
        });
    }
  });
}(CRM.$));
