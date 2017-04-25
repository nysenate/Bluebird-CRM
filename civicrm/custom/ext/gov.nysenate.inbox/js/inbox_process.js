CRM.$(function($) {
  $("#mainTabContainer").tabs( {active: 0} );
  var contact_id = CRM.vars.NYSS.matched_to;
  $('#contact_positions').on('select2-selecting', function(e) {
    CRM.api3('nyss_tags', 'savePosition', {value:e.val, contactId:contact_id}, false);
  });

  var BBCID = 0;
  var BBActionConst = 1;
  var BBLoadTaglist = null;

  var tree = new TagTreeTag({
    tree_container: cj('#issue-code-results'),
    list_container: cj('.contactTagsList'),
    filter_bar: cj('#issue-code-search'),
    tag_trees: [291],
    default_tree: 291,
    auto_save: false,
    entity_counts: false,
    entity_table: 'civicrm_contact'
  });
  tree.load();
});
