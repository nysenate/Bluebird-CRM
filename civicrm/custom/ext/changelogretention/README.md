# biz.lcdservices.logretention

This extension creates an API that can be run as a scheduled job to purge changelog records prior to the configured retention window.

After installing the extension through the normal means, navigate to Administer > System Settings > Log Retention Settings. Indicate the retention period in months. Logging records prior to that window will be purged when this API is run. Optionally select tables which will be excluded from the purging process. For example, if you want to retain all relationship history for contacts, you can exclude the civicrm_relationship table. Note that the exclusion process does not take into consideration any dependencies that may exist between tables.

Now navigate to Administer > System Settings > Scheduled Jobs and locate the Log Retention job. Edit the job, enable it, and set it to the desired frequency. Because the job is potentially long running, we suggest you run it weekly and schedule it for the weekend (assuming that is a low traffic time for your site).

Additional optional parameteres that can be passed to the API:

* limit = integer: Limits how many records per table will be processed per job run.
* logoutput = boolean: If set to 1, details about the purging process will be logged to a CiviCRM log file with a "logretention" prefix.

Because this can be a long running job you may find the script will timeout or exceed allowed memory consumption before completing, especially if the first time you run it involves purging a large number of records. As the script cycles through tables and rows it logs progress and can pick up where it left off within a 1-day period. If you find the script terminates prematurely you may want to set the limit parameter and run the job every day until it catches up with the full purging process.

<b>Note: </b>The log purging/retention process can only be performed on logging tables configured using the InnoDB or MyISAM table engine type. The default engine used by the enhanced logging feature in CiviCRM is ARCHIVE, which does not support row deletion. If you have installed this extension before enabling enhanced logging, it will create your tables using InnoDB. If your logging tables have already been created, enable this extension and then run the system.updatelogtables API function which will rebuild those tables using InnoDB as defined in this extension.
