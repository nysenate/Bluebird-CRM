## CiviCRM Attachment Indexing with Solr and Drupal 7.x

This module has been copied over and adapted for CiviCRM from [Drupal's apachesolr_attachments](https://drupal.org/project/apachesolr_attachments).

## Installation

Here's a quick, dirty, and thoroughly-untested guide:

1. Install *AMP, Drupal and CiviCRM (4.5+) as usual. (I used civicrm-buildkit, but use whatever process you like.)
2. Install Java
3. Install Solr standalone (eg https://github.com/civicrm/civicrm-infra/blob/master/solr.md ) or WAR (eg http://www.lullabot.com/blog/article/installing-solr-use-drupal ) per preference. Note the base URL. If you configure password-protection, note the username+password.
4. Download two Drupal modules -- https://drupal.org/project/apachesolr and https://github.com/civicrm/apachesolr_civiAttachments . Put these in your Drupal "modules" folder.
5. Download Apache Tika (tika-app-1.5.jar) -- http://tika.apache.org/download.html . Place this anywhere on the server. It needs to be readable by PHP (but it doesn't matter if remote users can download it).
6. In Drupal, enable the modules "apachesolr" and "apachesolr_civiAttachments".
7. In Drupal, go to the Solr admin ( /admin/config/search/apachesolr ). 
 - Set the Solr URL (If authentication is required, include the username+password in the URL.)
 - Set CiviAttachments to use the Tika JAR file you downloaded earlier. Test.
 - In the list of indexable content types, enable indexing for CiviCRM files.
8. Finally, you may need to forcibly index the content. This is useful during installation, upgrade, and development:
 - In Drupal's Solr admin, queue all content for indexing.
 - In Drupal's Solr admin, execute the queued tasks. (Or invoke Drupal cron.)
 - In Solr, commit the changes (http://example.org:8983/solr/update?commit=true )
