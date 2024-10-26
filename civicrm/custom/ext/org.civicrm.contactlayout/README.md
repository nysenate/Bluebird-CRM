# Contact Summary Layout Editor

**Customize the contact summary screen.**

Have you ever wanted to rearrange the contact summary screen? Move the most important information to the top? Remove unnecessary stuff? Create a simplified layout for your volunteers and interns but a more robust layout for your fundraising team? This extension will let you do just that.

![Screencast](/images/screencast.gif)

![Screenshot](/images/summary.png)

## Basic Usage

* Once installed, navigate to **Administer -> Customize Data and Screens -> Contact Summary Layouts** to open the editor.
* Create one or more layouts, dragging the desired blocks from the palette.
* Click the "New Block" button to create a block combining any contact fields you desire (including custom fields).
* Your blocks can be added to one or more layouts.
* Rearrange tabs along the top, change their labels or icons, per-layout or system-wide. 
* **Note:** Block titles, collapsibility & positioning can be set on a per-layout basis, but editing the fields _within_ a block will affect all layouts the block appears in.

### Managing Multiple Layouts

* The "Show" option can specify a contact type, e.g. if a layout is specifically designed for _Organization_ contacts rather than _Individuals_.
* The "To" option can restrict a layout to be visible to only certain logged-in users (e.g. show one layout to your volunteers and another to your staff).
* When a user views a contact, the first layout in the list which meets the "Show" and "To" criteria will be shown.
* If no layout matches the criteria, the default system layout will be used.

## Integrates with
* [Shoreditch theme](https://github.com/civicrm/org.civicrm.shoreditch) - works with or without full theme override mode.
* [Relationship block extension](https://github.com/eileenmcnaughton/org.wikimedia.relationshipblock) - provides a block for displaying important relationships.
* [Extended Reports extension](https://civicrm.org/extensions/extended-reports) - allows reports to be displayed as blocks on the summary screen.
* Form Builder & Search Kit - CiviCRM core extensions which can display custom forms & searches as contact summary blocks & tabs.

## For developers

This extension provides `hook_civicrm_contactSummaryBlocks` to allow other extensions to supply blocks for the layout editor.
It also provides an api (v3 and v4 compatible) to facilitate managing summary layouts.

Hook example:

```php
/**
 * Implements hook_civicrm_contactSummaryBlocks().
 *
 * @link https://github.com/civicrm/org.civicrm.contactlayout
 */
function example_civicrm_contactSummaryBlocks(&$blocks) {
  // Register our block with the layout editor.
  $blocks['core']['blocks']['example_block'] = [
    'title' => ts('Example Block'),
    'tpl_file' => 'CRM/Example/ExampleBlock.tpl',
    'sample' => [ts('Example field'), ts('Another example field')],
    'edit' => FALSE,
    'foo' => 'bar', // add any data you want passed to ExampleBlock.tpl
  ];
}
```

To _display_ the block your extension needs to provide a smarty template (declare path relative to your extensions' `templates` directory per `tpl_file` in the example above).
The template will receive `{$contactId}` and `{$block}` as available variables (in the example above ,`{$block.foo == 'bar'}`, which may be sufficient for your block to [call the smary api](https://github.com/eileenmcnaughton/nz.co.fuzion.extendedreport/blob/master/templates/CRM/Extendedreport/Page/Inline/ExtendedReport.tpl#L5) and fetch whatever data you need.
If your block needs additional preprocessing in PHP, you'll need to use [hook_civicrm_pageRun](https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pageRun/) to assign more data to the contact summary page (all data assigned to that page is available to every block template).

For a fully _editable_ block you'll also need:

* A form class, e.g. `CRM_Example_Form_Inline_Example` and a corresponding smarty template.
* A page class, e.g. `CRM_Example_Page_Inline_Example` which assigns variables for your tpl when the block reloads the view from ajax.

You can optionally use a region hook to inject the template onto the summary screen if you want your block to work even without this extension.

For a working example of how to do all of the above, see the [relationship block extension](https://github.com/eileenmcnaughton/org.wikimedia.relationshipblock),
which works well on its own; the only addition needed to add its block to the editor palette was [implementing `hook_civicrm_contactSummaryBlocks`](https://github.com/eileenmcnaughton/org.wikimedia.relationshipblock/pull/14).

-----

This extension licensed under [AGPL-3.0](LICENSE.txt).
