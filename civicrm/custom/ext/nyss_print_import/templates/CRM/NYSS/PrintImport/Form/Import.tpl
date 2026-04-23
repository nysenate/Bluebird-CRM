{* HEADER *}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT)

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

*}

<fieldset>
  <legend>{ts}Import Source and Processing Options{/ts}</legend>
    <div class="crm-section">
        <div class="label">{$form.csv.label}</div>
        <div class="content">{$form.csv.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.servercsv.label}</div>
        <div class="content">{$form.servercsv.html}
            <p class="description">{ts}Specify a filename on the server in the '/data/importData' dir.{/ts}</p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.runnum.label}</div>
        <div class="content">{$form.runnum.html}
            <p class="description">{ts}Applies to Dry Run mode only. Should be set to ALL for data import{/ts}</p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.dryrun.label}</div>
        <div class="content">{$form.dryrun.html} <span class="description">{ts}(parses data but does not alter the instance) {/ts}</span></div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.sendemail.label}</div>
        <div class="content">{$form.sendemail.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.debug.label}</div>
        <div class="content">{$form.debug.html}</div>
        <div class="clear"></div>
    </div>
</fieldset>

<fieldset>
    <legend>{ts}Dedupe Process{/ts}</legend>
    <div class="crm-section">
        <div class="label">{$form.dedupe.label}</div>
        <div class="content">{$form.dedupe.html} <span class="description">{ts}When enabled, imported records will be compared and matched with existing contact records using the selected rule. Records should have first name, last name, suffix, street address, and postal code or email in order to effectively match.{/ts}</span></div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.deduperule.label}</div>
        <div class="content">{$form.deduperule.html}</div>
        <div class="clear"></div>
    </div>
</fieldset>

<fieldset>
    <legend>{ts}Data handling{/ts}</legend>
    <div class="crm-section">
        <div class="label">{$form.boeimport.label}</div>
        <div class="content">{$form.boeimport.html} </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.emailimport.label}</div>
        <div class="content">{$form.emailimport.html} <span class="description">{ts}Enabling will automatically engage dedupe on import using the email import rule. New contacts created during import (which do not match any existing contacts) will be added to an "Email Only" group and excluded from postal mailings.{/ts}</span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.parsename.label}</div>
        <div class="content">{$form.parsename.html} <span class="description">{ts}Enable this option if your import file contains a full_name field that you want to parse into first/middle/last name. Note, if you enable this option, parsed field names will be ignored.{/ts}</span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.fieldoverried.label}</div>
        <div class="content">{$form.fieldoverried.html} <span class="description">{ts}Enabling this option will allow you to override existing fields with empty values. Standard behavior ignores empty values in your import file, thus retaining existing values in Bluebird. Use with caution.{/ts}</span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.allowdelete.label}</div>
        <div class="content">{$form.allowdelete.html} <span class="description">{ts}Delete contacts when <em>delete_contact</em> field is specified.{/ts}</span>
        </div>
        <div class="clear"></div>
    </div>
</fieldset>
<fieldset>
    <legend>{ts}Add to Group{/ts}</legend>
    <div class="crm-section">
        <div class="label">{$form.addtogroup_handling.label}</div>
        <div class="content">{$form.addtogroup_handling.html} <p class="description">{ts}Enabling this option will allow you to override existing fields with empty values. Standard behavior ignores empty values in your import file, thus retaining existing values in Bluebird. Use with caution.{/ts}</p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.addtogroup.label}</div>
        <div class="content">{$form.addtogroup.html} <p class="description">{ts}If the group name does not exist, it will be created.{/ts}</p>
        </div>
        <div class="clear"></div>
    </div>
</fieldset>
<fieldset>
    <legend>{ts}Greeting Generation{/ts}</legend>
    <div class="crm-section">
        <div class="label">{$form.greetinggeneration.label}</div>
        <div class="content">{$form.greetinggeneration.html} <p class="description">{ts}Greeting Generation has moved and will be removed from the importer in the near future. Please use the new greeting generation tool instead. If you must do greeting generation here, then you may run the greeting generation script across the entire database, or only on imported contacts.{/ts}</p>
        </div>
        <div class="clear"></div>
    </div>
</fieldset>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
