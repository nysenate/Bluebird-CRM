(function ($) {
  //there is contention with the roleassign module for modifying this layout
  //need to ensure the block exists before we modify with js
  var layoutRoles = function(selector, callback) {
    if (jQuery(selector).length) {
      callback();
    } else {
      setTimeout(function() {
        layoutRoles(selector, callback);
      }, 100);
    }
  };

  layoutRoles('div.form-item-roles-change', function() {
    var html = '<label for="edit-roles-change">Assignable Roles</label>' +
      '<div id="edit-roles-change" class="form-checkboxes nyss-user-roles-wrapper">' +
      '<div class="nyss-roles-section-header">Main Role</div>' +
      '<div class="nyss-roles-description">In order for a staff member to get started in Bluebird, they must first have ONE main role assigned to them.</div>' +
      $('div.form-item-roles-change-9')[0].outerHTML +
      $('div.form-item-roles-change-10')[0].outerHTML +
      $('div.form-item-roles-change-6')[0].outerHTML +
      $('div.form-item-roles-change-11')[0].outerHTML +
      $('div.form-item-roles-change-13')[0].outerHTML +
      '<div class="nyss-roles-section-header">Add-on Roles</div>' +
      '<div class="nyss-roles-description">Additional roles to extend access in specific feature areas.</div>' +
      '<div class="nyss-roles-subsection-header">Bluebird Mass Email</div>' +
      $('div.form-item-roles-change-16')[0].outerHTML +
      $('div.form-item-roles-change-14')[0].outerHTML +
      $('div.form-item-roles-change-15')[0].outerHTML +
      $('div.form-item-roles-change-17')[0].outerHTML +
      '<div class="nyss-roles-subsection-header">Bluebird Inbound Email</div>' +
      $('div.form-item-roles-change-19')[0].outerHTML +
      '<div class="nyss-roles-section-header">Other Senate Office Roles</div>' +
      '<div class="nyss-roles-description">Several Senate central staff offices have permission to access your Senator\'s Bluebird database. The roles below are used specifically for those offices.</div>' +
      $('div.form-item-roles-change-8')[0].outerHTML +
      $('div.form-item-roles-change-5')[0].outerHTML +
      $('div.form-item-roles-change-12')[0].outerHTML +
      '</div>'
    ;
    //console.log('html: ', html);

    $('div.form-item-roles-change').html(html);

    //hide tabs
    $('ul.tabs.primary').remove();
  });

}(jQuery));
