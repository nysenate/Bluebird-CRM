# Bluebird Core Mods

For the most part, Bluebird follows CiviCRM best practices for customizations and
avoids [editing core files](https://docs.civicrm.org/dev/en/latest/core/hacking/#when-should-i-edit-core-civicrm)

In some cases, though, it has been necessary. Core file modifications are [documented internally](https://dev.nysenate.gov/projects/bluebird/wiki/Core_Modifications#CiviCRM-files-modified-or-added-in-core-which-could-not-be-overridden)

The `patches` directory contains patch files to help re-apply Bluebird Core Mods when upgrading CiviCRM Core.

# Minimizing Core Mods

Whenever reasonable and possible -- time permitting -- try to eliminate Core Mods 
by moving code to an extension, using a hook, event or override file.

Whenever reasonable, consider contributing the modification to CiviCRM Core.

# How to make a Patch

1. Make sure you start with a "clean" version of the CiviCRM Core file.
    
    "clean" means that it is the version of the code distributed by CiviCRM without any local customizations
2. Check the latest Bluebird codebase for customizations
3. Apply necessary customizations to the file.
4. `$ git diff file > $PATCH_DIRECTORY/[relevant path]/file.patch`
5. Test the patch:
   1. `$ git checkout file` to bring it back to its "clean" state
   2. `$ git apply $PATCH_DIRECTORY/[relevant path]/file.patch`
   3. Take a look and see if the patch was correctly applied.

# How to Apply a Patch

1. Refer to [internal wiki](https://dev.nysenate.gov/projects/bluebird/wiki/Core_Modifications#CiviCRM-files-modified-or-added-in-core-which-could-not-be-overridden) for a list of modified files
2. Visually compare the latest CiviCRM Core version against the last known Bluebird version
3. Take note of new CiviCRM updates vs. existing Bluebird modifications, which are typically denoted in a comment that includes *NYSS*.
4. If the Bluebird modification is still relevant, look for an appropriate patch file in this directory.
5. Apply the patch:

```shell
$ git apply $PATCH_DIRECTORY/[relevant path]/file.patch
```

Patches are stored relative to where they live in the CiviCRM Core codebase. For example:

`core/ang/crmUi.js` would have a related patch file in `civicrm/patches/core/ang/crmUi.js`

6. Verify that the patch did what you expected it to do.
7. Add and commit your change to git.