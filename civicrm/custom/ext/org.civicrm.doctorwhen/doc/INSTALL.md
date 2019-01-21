# Doctor When: Installation, Usage, and Uninstallation

## Installation

```
## Navigate to your extension folder
$ cv path -x.
/srv/buildkit/build/dmaster/sites/default/files/civicrm/ext
$ cd /srv/buildkit/build/dmaster/sites/default/files/civicrm/ext

## Clone the repo
$ git clone https://github.com/civicrm/org.civicrm.doctorwhen

## Enable the extension
$ cv en doctorwhen
```

## Usage (Web)

Navigate to `civicrm/doctorwhen?reset=1`, e.g.

```
$ cv url civicrm/doctorwhen?reset=1
"http://dcase.l/civicrm/doctorwhen?reset=1"
$ cv url civicrm/doctorwhen?reset=1 --open
```

## Usage (CLI)

You can execute the full list of DoctorWhen tasks by calling the `DoctorWhen.run` API, e.g.

```
cv api DoctorWhen.run tasks=*
```

## Usage (PHP)

You can execute the full list of DoctorWhen tasks by calling the `DoctorWhen.run` API, e.g.

```php
civicrm_api3('DoctorWhen', 'run', array(
  'tasks' => '*',
));
```

## Uninstall (General)

DoctorWhen has no ongoing functionality at this time and should be disabled and uninstalled after successfully running.  Disabling and uninstalling the extension is done in the normal fasion, as outlined in the System Administrator Guide.

* [Sysatem Administrator Guide: Disabling an extension](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#enabling-and-disabling-extensions)
* [Sysatem Administrator Guide: Uninstalling an extension](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#uninstalling-extensions)

Don't forget to delete the extension from the extensions directory.

## Uninstall (CLI)

```
## Disable the extension
$ cv ext:disable doctorwhen
$ cv ext:uninstall doctorwhen

## Delete the folder and extension files
$ cv path -x doctorwhen
/srv/buildkit/build/dmaster/sites/default/files/civicrm/ext/org.civicrm.doctorwhen
$ rm -rf /srv/buildkit/build/dmaster/sites/default/files/civicrm/ext/org.civicrm.doctorwhen
```
