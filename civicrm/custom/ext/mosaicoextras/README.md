# Mosaico Extras

This extension includes new features for [Mosaico CiviCRM Integration](https://civicrm.org/extensions/email-template-builder)

# Features

#### Mosaico toolbar configuration

- This extension integrates the [Toolbar Configurator for Mosaico](https://github.com/ginkgostreet/com.ginkgostreet.mosaicotoolbarconfig) in CiviCRM. Special thanks to [Ginkgo Street Labs](https://github.com/ginkgostreet) for making it posible.
- This tool allows an admin user to edit the settings of the tinymceConfigFull i.e. Mosaico Full Toolbar configuration by entering a list of plugins and buttons into text fields on the Mosaico settings screen. This extension does not allow changes to be made to the tinymceConfig configuration, which is the standard/base configuration used for headings, etc.

#### Mosaico toolbar plugin

- Added new tool called "mailto" in mosaico toolbar to create mailto with cc, content and body. (Disabled in toolbar by default)

#### Mosaico delete template permission

- Grants the necessary API permissions to access mosaico templates and delete without Administer CiviCRM permission
