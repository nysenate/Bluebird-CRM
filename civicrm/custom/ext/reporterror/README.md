Report Error CiviCRM Extension
==============================

Sometimes CiviCRM can be real tough to debug. Especially when you are getting
fatal errors, but only by some users, and you can't recreate the problems.
This utility will send you a detailed email when a CiviCRM fatal error occurs.

* when the error was encountered
* which CiviCRM page threw the error 
* which logged-in user encountered the error
* full request parameters ("get")
* optionally the "post" data

The extension can also offer to try to resolve some common errors:

* For contribution pages where the session has expired or the contribution
  page URL is truncated, you can choose to gracefully redirect CiviCRM
  errors to the site CMS default page, a specific contribution page or not
  at all. You also have the option of not getting emails on contribution
  page redirects (since crawlers can make them rather frequent).

* Detect bots and optionally generate a 404 http response instead of 'OK'.

To get the latest version of this module:  
https://lab.civicrm.org/extensions/reporterror

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.

Installation
------------

* Enable this extension in CiviCRM (Administer > System Settings > Manage Extensions)
* A new menu item will be added in Administer > System Settings > Report Errors,
  which you can use to access the extensions settings form.

If you are installing from the git repository, you must run 'composer install'.

Otherwise, please use the packages from the official releases:  
https://lab.civicrm.org/extensions/reporterror/-/releases

or from the releases listed on:  
https://civicrm.org/extensions/civicrm-error-handler

Requirements
------------

- CiviCRM >= 5.0

Contributors
------------

* CiviCRM extension/integration written & maintained by Mathieu Lutfy (Coop SymbioTIC),
  co-authored by Lola S (Freeform), Nicolas Ganivet (CiviDesk) and Young-Jin Kim (Emphanos).
* Based on the civicrm_error Drupal module by Dave Hansen-Lange (dalin):  
  https://drupal.org/project/civicrm_error

Logging PEAR/DB errors
-------------------

Some PEAR and database errors are handled by a separate handler of CiviCRM.
For this extension to handle those errors, apply this patch:

```
diff --git a/sites/all/modules/civicrm/CRM/Core/Error.php b/sites/all/modules/civicrm/CRM/Core/Error.php
--- a/civicrm/CRM/Core/Error.php
+++ b/civicrm/CRM/Core/Error.php
@@ -221,6 +221,19 @@ class CRM_Core_Error extends PEAR_ErrorStack {
       }
     }

+    if ($config->fatalErrorHandler && function_exists($config->fatalErrorHandler)) {
+      $name = $config->fatalErrorHandler;
+      $vars = [
+        'pearError' => $pearError,
+      ];
+      $ret = $name($vars);
+      if ($ret) {
+        // the call has been successfully handled
+        // so we just exit
+        self::abend(CRM_Core_Error::FATAL_ERROR);
+      }
+    }
+
```

(this patches the `handle($pearError)` function)

Support
-------

Please post bug reports in the issue tracker of this project on github:  
https://lab.civicrm.org/extensions/reporterror/issues

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

Please consider financially contributing to support and further develop this extension.

Commercial support is available through Coop SymbioTIC:  
https://www.symbiotic.coop/en

Copyright
---------

License: AGPL 3

Copyright (C) 2012-2018 CiviCRM LLC (info@civicrm.org)  
https://civicrm.org

Copyright (C) 2012-2020 Mathieu Lutfy (mathieu@symbiotic.coop)  
https://www.symbiotic.coop/en
