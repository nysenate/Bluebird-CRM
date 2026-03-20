# New York State Senate Greeting Generation Utilities
This extension adds features to facilitate New York State Senate greeting generation.

This is an [extension for CiviCRM](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/), licensed under [AGPL-3.0](LICENSE.txt).

## Generate Constituent Greetings Form

This tool generates gives the user the option to generate missing postal greetings, email greetings and addressees 
on Individuals. This does not run against Organizations and other contact types.

The page shows how many individuals are missing one or more of the mentioned greeting types.

1. Go to /civicrm/nys/generate-greetings
2. Click the Generate Greetings button

The task will be spawned and executed via CiviCRM's Queue Runner framework. So, the user will first be directed to the 
Queue Runner status page. When all jobs have finished, the user will be directed back to the Generate Constituent Greetings
page.
