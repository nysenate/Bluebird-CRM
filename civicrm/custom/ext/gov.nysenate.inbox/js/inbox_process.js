CRM.$(function($) {
  $("#mainTabContainer").tabs( {active: 0} );
  var contact_id = CRM.vars.NYSS.matched_to;
  $('#contact_positions').on('select2-selecting', function(e) {
    CRM.api3('nyss_tags', 'savePosition', {value:e.val, contactId:contact_id}, false);
  });

  (function($, _){
    var entityID = null,
      entityTable='civicrm_contact',
      $form = $('form.CRM_NYSS_Inbox_Form_Process');

    $(function() {
      // Load js tree.
      CRM.loadScript(CRM.config.resourceBase + 'bower_components/jstree/dist/jstree.min.js').done(function() {
        $("#tagtree").jstree({
          plugins : ['search', 'wholerow', 'checkbox'],
          core: {
            animation: 100,
            themes: {
              "theme": 'classic',
              "dots": false,
              "icons": false
            }
          },
          'search': {
            'case_insensitive' : true,
            'show_only_matches': true
          },
          checkbox: {
            three_state: false
          }
        })
          .on('select_node.jstree deselect_node.jstree', function(e, selected) {
            /*var id = selected.node.a_attr.id.replace('tag_', ''),
              op = e.type === 'select_node' ? 'create' : 'delete';
            CRM.api3('entity_tag', op, {entity_table: entityTable, entity_id: entityID, tag_id: id}, true);*/
          });
      });

      $('input[name=filter_tag_tree]', '#Tag').on('keyup change', function() {
        $("#tagtree").jstree(true).search($(this).val());
      });
    });
  })(CRM.$, CRM._);
});
