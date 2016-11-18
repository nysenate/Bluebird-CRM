<div class="BBInit">
  <div class="crm-section tag-section contact-tagset-291-section crm-processed-input">
    <div class="tag-label">
      <label>Issue Codes</label>
    </div>
    <input type="text" autocomplete="off" placeholder="Type here to search issue codes" maxlength="64" id="issue-code-search" />
    <div id="issue-code-results" class="TreeWrap" data-contact="{$contactId}"></div>
  </div>
</div>
{literal}
  <script>
    var tree = new TagTreeTag({
      tree_container: cj('#issue-code-results'),
      list_container: cj('.contactTagsList'),
      filter_bar: cj('#issue-code-search'),
      tag_trees: [291],
      default_tree: 291,
      auto_save: true,
      entity_id: cj('#issue-code-results').data('contact'),
      entity_counts: false,
      entity_type: 'civicrm_contact'
    });
    tree.load();
  </script>
{/literal}
