# Mosaico - Responsive email template editor

This extension integrates a responsive email template editor, [Mosaico](https://mosaico.io/), with CiviCRM.

View/Download this extension in the [Extension Directory](https://civicrm.org/extensions/email-template-builder).

## Requirements
* PHP-ImageMagick - recommended.
* [FlexMailer](https://docs.civicrm.org/flexmailer/en/latest/)

## History and blog posts
 * [Initial Blog Post](https://civicrm.org/blogs/parvez/a-new-beginning-for-civimail)
 * [Beta Blog Post](https://civicrm.org/blog/deepaksrivastava/email-template-builder-civimosaico-is-now-beta)
 * [Initial Video](https://vimeo.com/156633077)
 * [v1.0 Stable Release](https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/releases/tag/1.0)
 * [v2.0 Plans](https://civicrm.org/blog/jamienovick/email-template-builder-mosaico-phase-2-plans)
 * [v2.0 and Styling Blog Post](https://civicrm.org/blog/jamienovick/extreme-makeovers-civicrm-style-introducing-the-shoreditch-theme-civicrms-new-user)

## Installation

For help installing extensions, please see the [Install a New Extension section of the Extensions chapter](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#installing-a-new-extension) in the SysAdmin Guide.

!!! warning "DO NOT DOWNLOAD DIRECTLY FROM GITHUB"
    Download Mosaico from the [Mosaico page on the CiviCRM extensions directory](https://civicrm.org/extensions/email-template-builder) as the Mosaico extension requires additional packaging steps for a working release!

## Getting started (Usage)

If you haven't used Mosaico before, consult the the demo and tutorial materials from [Mosaico.io](https://mosaico.io/index.html).

To send a new mailing, simply navigate to *Mailings > New Mailing*. The CiviMail-Mosaico UI should appear.

Optionally, you may design reusable templates by navigating to *Mailings > Mosaico Templates*.

When composing a new mailing with Mosaico, the screen may be configured as a three-step wizard or as a single-step form. To
choose a layout, navigate to *Administer > CiviMail > Mosaico Settings*.

If you would like to compose mailings with the *old* CiviMail screen, navigate to *Mailings > New Mailing (Traditional)*.

## Using Mosaico with Scheduled Reminders / Personal Messages
There is an experimental extension that allows Mosaico templates to be used for scheduled reminders, personal messages and a few other email related template-y things. It is called [mosaicomsgtpl](https://github.com/civicrm/org.civicrm.mosaicomsgtpl).

!!! warning "Experimental"
    To avoid confusion the above extension is experimental, whilst it is in use on production sites it would benefit from more extensive testing! If you can help [open an issue](https://github.com/civicrm/org.civicrm.mosaicomsgtpl/issues).

## Support and Maintenance

This extension is supported and maintained by the CiviCRM community.

Please make sure you have followed installation instructions.

Open issues on [github](https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/issues) with:
- screenshot of failure with any possible errors in firebug or js console
- any related logs or backtrace from civicrm
- tell us what version of CiviCRM and extension, you using.
- tell us the browser you are using (name and version) and test at least a second browser to tell us if this happen in both or only one (tell us the details about the second browser too).

## Migration to a new Domain

If you move CiviCRM to a new domain, you must update the template paths using the `replaceurls` API method:

```
cv api MosaicoTemplate.replaceurls from_url="http://old.server.org" to_url="https://new.server.org"
```
