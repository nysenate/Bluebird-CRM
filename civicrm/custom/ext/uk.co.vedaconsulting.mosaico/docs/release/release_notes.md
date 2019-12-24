**DO NOT DOWNLOAD DIRECTLY FROM GITHUB** 
Download via https://civicrm.org/extensions/mosaico-civicrm-integration as there are additional packaging requirements not handled by github.

## Release 2.3

* Change image placeholder permissions (*Fixes issue with image placeholders for some sites*).

## Release 2.2

* Allow sending traditional emails as a search task.
* Improve validation in image processing.
* Move maximum image size into a setting.
* Drop system check for shoreditch theme as it is not required for Mosaico to function.
* Improve browser incompatibility message.
* Sort templates alphabetically when displaying 'New Mailing'.
* Add translation support - the Mosaico editor will now appear in the same language as CiviCRM if language files are available.

## Release 2.1

* Always place iframe below KAM menu instead of behind.
* Replace '<title>TITLE<title>' with subject (render-time).
* Document hook_civicrm_mosaicoConfig.

## Release 2.0

All users of Mosaico should evaluate and upgrade to this release.

* Simplify requirements:
  * The shoreditch theme is not required - the extension will work with the current CiviCRM theme.
  * ImageMagick is not required - the extension will auto-detect between imagemagick and gd depending on what the server supports.
* Reduce memory usage which previously caused issues sending mail on some servers.
* Add a hook for mosaico editor configuration.

**Download link: https://civicrm.org/extensions/mosaico-civicrm-integration/version-20**
