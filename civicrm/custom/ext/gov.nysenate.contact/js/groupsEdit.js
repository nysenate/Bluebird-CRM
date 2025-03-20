cj('.crm-group-form-block-group_type a.helpicon').remove();

//5359 UI mods
cj('input[type=checkbox][name="group_type[1]"]').remove();
cj('label[for="group_type_1"]').remove();
var acg = cj('tr.crm-group-form-block-group_type td[class!="label"]').html().replace(/&nbsp;/g,'');
cj('tr.crm-group-form-block-group_type td[class!="label"]').html(acg);
cj('tr.crm-group-form-block-visibility').hide();
cj('tr.crm-group-form-block-description span.description').hide();
