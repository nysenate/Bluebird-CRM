# Recalculate recipients

CiviCRM calculates the recipients for a mailing based on the contacts that are in your include/exclude groups/mailings at the time that you hit the submit button.

This means that any contacts that are added or removed to your groups in the time between when you hit the submit button and the date it is scheduled to go out will not receive your mailing.

This extension causes the recipients of a mailing to be recalculated just before the mail is sent out meaning you'll be sending based on the most up to date recipients.

## Heath warning

**Recalculate recipients is currently in beta - use at your own risk.**

The extension has been in production in at least one organisation for 3 months without issue. We we have not had any reports of it breaking anything from other organisations but even so, it has not been widely tested.

*If this extension does work for you, please feedback by creating an issue in the github repostory with details of your use case. Once we have received a few positive reviews, we will move it out of beta.*

### Not ACL friendly

This extension is not ACL friendly. The CiviMail author's ACLs are not taken into account when the recipients are recalculated (see [issue #1](https://github.com/3sd/civicrm-recalculate-recipients/issues/1) for more details).

Not not use it if you are relying on ACLs to limit the contacts to which CiviMail users can send

## Requirements

CiviCRM 4.7

## Installation

1. Read the [health warning](#health-warning)
2. Download a release from https://github.com/3sd/civicrm-recalculate-recipients/releases to your extensions directory
3. Browse to **Administer > System Settings > Extensions**
4. Find **Recalculate Recipients** and click **Install**

Note: for help on installing extensions, consult the [extensions chapter of the CiviCRM system administrator guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions).

## Getting started.

Recalculate recipients is a simple extension with no configuration options. Once enabled it will start working automatically; recalculating recipients just before each mailing is sent. To stop recalculating recipients, simply disable the extension.

## Help

If you have any questions regarding this extension that are not answered in this README, please check post a question on http://civicrm.stackexchange.com or contact info@thirdsectordesign.org.

## Credits

This extension has been developed by [Michael McAndrew](https://twitter.com/michaelmcandrew) from [Third Sector Design](https://thirdsectordesign.org/) who you can [contact](https://thirdsectordesign.org/contact) for help, support and further development.

Funding for this extension was generously provided by [eLife Sciences](https://elifesciences.org/), a unique, non-profit collaboration between the funders and practitioners of research to improve the way important results are presented and shared.

## Contributing

Contributions to this repository are very welcome. For small changes, feel free to submit a pull request. For larger changes, we suggest you create an issue first so we can discuss the idea and approach.

## License

This extension is licensed under [AGPL-3.0](LICENSE.txt).
