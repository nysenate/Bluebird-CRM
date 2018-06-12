# CiviCase v5

The CiviCase v5 extension (`org.civicrm.civicase`) is an overhaul of the
CiviCase UI.  It provides a richer experience with more thoughtful layouts
and interactions.

![Screenshot](/img/screenshot.png)

> At time of writing, this extension is still in active development.  It may
> require bleeding-edge software.  These instructions are currently aimed at
> developers.

## Requirements

 * Latest CiviCRM v4.7.x, preferrably the latest `master`
 * [Shoreditch](https://github.com/civicrm/org.civicrm.shoreditch) 
 * (*Recommended*) Migrate from embedded activity revisions to full system logging
   ([CRM-21051](https://issues.civicrm.org/jira/browse/CRM-21051))

## Installation (git/cli)

To install the extension on an existing CiviCRM site:

```
mkdir sites/all/modules/civicrm/ext
cd sites/all/modules/civicrm/ext
git clone https://github.com/civicrm/org.civicrm.shoreditch shoreditch
git clone https://github.com/civicrm/org.civicrm.civicase civicase
cv en shoreditch civicase
cv api setting.create customCSSURL=$(cv url -x shoreditch/css/custom-civicrm.css --out=list)
```

## Installation (civibuild)

To setup a new developer site pre-configured with CiviCRM, Shoreditch, CiviCase, and
any other dependencies, [install and configure Buildkit](https://docs.civicrm.org/dev/en/latest/tools/buildkit/).

Buildkit provides the [`civibuild`](https://docs.civicrm.org/dev/en/latest/tools/civibuild/)
command, which can produce new sites running Drupal 7 or WordPress, e.g.:

```
## Create a build with Drupal 7, CiviCRM 4.7, and CiviCase 5
civibuild create dcase --url http://dcase.localhost --admin-pass s3cr3t

## Create a build with WordPress, CiviCRM 4.7, and CiviCase 5
civibuild create wpcase --url http://wpcase.localhost --admin-pass s3cr3t
```
