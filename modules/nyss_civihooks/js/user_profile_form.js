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

  layoutRoles('div#edit-roleassign-roles', function() {
    var html = '<label for="edit-roleassign-roles">Assignable Roles</label>' +
      '<div id="edit-roleassign-roles" class="form-checkboxes">' +
      '<div class="nyss-roles-section-header">Main Role</div>' +
      '<div class="nyss-roles-description">In order for a staff member to get started in Bluebird, they must first have ONE main role assigned to them.</div>' +
      $('div.form-item-roleassign-roles-9')[0].outerHTML +
      $('div.form-item-roleassign-roles-10')[0].outerHTML +
      $('div.form-item-roleassign-roles-6')[0].outerHTML +
      $('div.form-item-roleassign-roles-11')[0].outerHTML +
      $('div.form-item-roleassign-roles-13')[0].outerHTML +
      '<div class="nyss-roles-section-header">Add-on Roles</div>' +
      '<div class="nyss-roles-description">Additional roles to extend access in specific feature areas.</div>' +
      '<div class="nyss-roles-subsection-header">Bluebird Mass Email</div>' +
      $('div.form-item-roleassign-roles-16')[0].outerHTML +
      $('div.form-item-roleassign-roles-14')[0].outerHTML +
      $('div.form-item-roleassign-roles-15')[0].outerHTML +
      $('div.form-item-roleassign-roles-17')[0].outerHTML +
      '<div class="nyss-roles-subsection-header">Bluebird Inbound Email</div>' +
      $('div.form-item-roleassign-roles-19')[0].outerHTML +
      '<div class="nyss-roles-section-header">Other Senate Office Roles</div>' +
      '<div class="nyss-roles-description">Several Senate central staff offices have permission to access your Senator\'s Bluebird database. The roles below are used specifically for those offices.</div>' +
      $('div.form-item-roleassign-roles-8')[0].outerHTML +
      $('div.form-item-roleassign-roles-5')[0].outerHTML +
      $('div.form-item-roleassign-roles-12')[0].outerHTML +
      '</div>' +
      $('div.form-item-roleassign-roles div.description')[0].outerHTML
    ;
    //console.log('html: ', html);

    $('div.form-item-roleassign-roles').html(html);
  });

}(jQuery));
