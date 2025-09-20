diff --git a/civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmbWizard.js b/civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmbWizard.js
index 006fdf7a6..82458278c 100644
--- a/civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmbWizard.js
+++ b/civicrm/custom/ext/uk.co.vedaconsulting.mosaico/ang/crmbWizard.js
@@ -21,6 +21,8 @@
           var crmbWizardCtrl = this;
           var maxVisited = 0;
           var selectedIndex = null;
+          //NYSS
+          $scope.checkPerm = CRM.checkPerm;
 
           var findIndex = function() {
             var found = null;
@@ -40,6 +42,21 @@
           this.$validStep = function() {
             return steps[selectedIndex] && steps[selectedIndex].isStepValid();
           };
+          //NYSS
+          this.workFlowEnabled = function() {
+            return CRM.crmMailing.workflowEnabled;
+          };
+          this.check = function() {
+            if (this.$index() == 1 || this.$index() == 0) {
+              return !(CRM.crmMailing.workflowEnabled && !CRM.checkPerm('create mailings') && !CRM.checkPerm('access CiviMail'));
+            }
+            if (this.$index() == 2) {
+              return !(CRM.crmMailing.workflowEnabled && !CRM.checkPerm('schedule mailings') && !CRM.checkPerm('access CiviMail'));
+            }
+            else {
+              return true;
+            }
+          }
           this.iconFor = function(index) {
             if (index < this.$index()) return '√';
             if (index === this.$index()) return '»';
