(function ($) {
  //there is contention with the roleassign module for modifying this layout
  //need to ensure the block exists before we modify with js
  var waitForEl = function(selector, callback) {
    if (jQuery(selector).length) {
      callback();
    } else {
      setTimeout(function() {
        waitForEl(selector, callback);
      }, 100);
    }
  };

  waitForEl('div#edit-roleassign-roles', function() {
    //main role
    $('div.form-item-roleassign-roles-9').before('<div class="nyss-roles-section-header">Main Role</div><div class="nyss-roles-description">In order for a staff member to get started in Bluebird, they must first have ONE main role assigned to them.</div>');

    //add-on roles
    $('div.form-item-roleassign-roles-16').before('<div class="nyss-roles-section-header">Add-on Roles</div><div class="nyss-roles-description">Additional roles to extend access in specific feature areas.</div><div class="nyss-roles-subsection-header">Bluebird Mass Email</div>');
    $('div.form-item-roleassign-roles-19').before('<div class="nyss-roles-subsection-header">Bluebird Inbound Email</div>');

    //other senate office roles
    $('div.form-item-roleassign-roles-8').before('<div class="nyss-roles-section-header">Other Senate Office Roles</div><div class="nyss-roles-description">Several Senate central staff offices have permission to access your Senator\'s Bluebird database. The roles below are used specifically for those offices.</div>');
  });

}(jQuery));
