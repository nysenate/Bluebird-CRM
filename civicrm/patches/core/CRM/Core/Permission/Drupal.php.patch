diff --git a/modules/civicrm/CRM/Core/Permission/Drupal.php b/modules/civicrm/CRM/Core/Permission/Drupal.php
index 279661d9e..bb8a80d5c 100644
--- a/modules/civicrm/CRM/Core/Permission/Drupal.php
+++ b/modules/civicrm/CRM/Core/Permission/Drupal.php
@@ -159,6 +159,7 @@ class CRM_Core_Permission_Drupal extends CRM_Core_Permission_DrupalBase {
     }
 
     $uids = [];
+    //NYSS force exclusion of role 4 (Admin)
     $sql = "
       SELECT {users}.uid, {role_permission}.permission
       FROM {users}
@@ -168,6 +169,7 @@ class CRM_Core_Permission_Drupal extends CRM_Core_Permission_DrupalBase {
         ON {role_permission}.rid = {users_roles}.rid
       WHERE {role_permission}.permission = '{$permissionName}'
         AND {users}.status = 1
+        AND {users_roles}.rid != 4
     ";
 
     $result = db_query($sql);
