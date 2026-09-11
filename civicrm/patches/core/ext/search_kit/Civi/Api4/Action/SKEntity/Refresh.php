diff --git a/modules/civicrm/ext/search_kit/Civi/Api4/Action/SKEntity/Refresh.php b/modules/civicrm/ext/search_kit/Civi/Api4/Action/SKEntity/Refresh.php
index 8b32ff4e0..48b0dd80b 100644
--- a/modules/civicrm/ext/search_kit/Civi/Api4/Action/SKEntity/Refresh.php
+++ b/modules/civicrm/ext/search_kit/Civi/Api4/Action/SKEntity/Refresh.php
@@ -41,7 +41,7 @@ class Refresh extends AbstractAction {
     // and it would change the default ordering over time.)
 
     // Prepare a sketch of the process. Ensure metadata is well-formed.
-    $sql = (new SKEntityGenerator())->createQuery($display['saved_search_id.api_entity'], $display['saved_search_id.api_params'], $display['settings']);
+    $sql = (new SKEntityGenerator())->createQuery($display['saved_search_id.api_entity'], $display['saved_search_id.api_params'], $display['settings'], 'table');
     $finalTable = _getSearchKitDisplayTableName($displayName);
     $columnSpecs = array_column($display['settings']['columns'], 'spec');
     $columns = implode(', ', array_column($columnSpecs, 'name'));
