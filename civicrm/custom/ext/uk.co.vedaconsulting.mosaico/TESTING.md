# Testing

## Automated Tests: Requirements

 * Working CiviCRM installation
 * PHPUnit (https://phpunit.de/)
 * cv (https://github.com/civicrm/cv)

## Automated Tests: End to end

This extension includes a handful of end-to-end API tests.  These tests
verify that the Mosaico data can be read and written when installed on a
CiviCRM site.

```
phpunit4 --group headless
phpunit4 --group e2e
```

## Manual Tests: Save/Load Mailing

1. Create a mailing
    1. Navigate to "Mailings => New Mailing" (`civicrm/a/#/mailing/new` or `civicrm/a/#/mailing/new/mosaico`)
    2. _Observe_: Redirect to `civicrm/a/#/mailing/123` and open with Mosaico layout
    3. Enter a mailing name, etc. (Make a mental note of the name.)
    4. Under "Design", choose a template.
    5. _Observe_: A full-screen dialog opens with Mosaico.
    6. Create a block. Edit some text. (Make a mental note of the content.)
    7. Save.
2. Immediately re-edit
    1. Under "Design", open the template again.
    2. _Observe_: A full-screen dialog opens with Mosaico. It restores the content from before.
    3. Save or close
3. Try a full reload
    1. Navigate to "Mailings => Draft and Unscheduled"
    2. Find your mailing. Click "Continue".
    3. Under "Design", open the template again.
    4. _Observe_: A full-screen dialog opens with Mosaico. It restores the content from before.

## Manual Tests: Tokens

This extension defines a TinyMCE plugin called `civicrmtoken`.  To test this
plugin, create a mailing with a block of content. Then try each of the following:

1. Edit a paragraph. In the toolbar, click the token dropdown and see a hotlist of 3-5 tokens. Pick one. Observe the new token in the paragraph.
2. Edit a paragraph. In the toolbar, click the token icon and see a dialog. Pick one. Observe the new token in the paragraph.
3. Edit a paragraph. Press `Ctrl-Shift-T` and see a dialog. Enter a filter and pick a token using the keyboard. Observe the new token in the paragraph.
4. Edit a heading or button. Ensure that the the token icon/dropdown/hotkey work as expected. Observe the new token in the heading or button.

## Manual Tests: Images and Links

Mosaico handles a few different kinds of images and links. To test these, create a
mailing with content:

* (CON-1) Create a new mailing. Fill in placeholders for "Name", "Subject", etc.
* (CON-2) Choose "Empty Template (versafix-1)".
* (CON-3) Add a block which supports one image with text.
    * Upload an image.
    * Set the button's link to an external page (eg `https://www.google.com/search?q=asdf&oq=asdf`)
    * Highlight some text. Make it a a hyperlink to a token (eg `{action.forward}`)
* (CON-4) Add a block which supports one image with text.
    * Go to the "Gallery". Re-use the previously uploaded image.
    * Set the button's link to an internal page (eg `http://dmaster.l/civicrm/event/info?reset=1&id=1`)
* (CON-5) Add a footer block which uses the built-in Twitter/Facebook icons.
    * Set the link for at least one social media button. Disable any others.

Now, we're going test that content appears correctly in several scenarios.  Each scenario references the "Message Evaluation Procedure" (defined further down):

* (SC-1) Using the "Test", open the "Preview" in HTML. Perform the "Message Evaluation Procedure" (in the browser).
* (SC-2) Using the "Test", send a message to an email address. Perform the "Message Evaluation Procedure" (in the email).
* (SC-3) Finalize and submit the mailing. Trigger cron (eg `cv api -U admin job.process_mailing`). Perform the "Message Evaluation Procedure" (in the email).
* (SC-4) In the email, click the link to "View in Browser". Perform the "Message Evaluation Procedure" (in the browser).

The "Message Evaluation Procedure" is:

* (MEP-1) Check the the first block:
     * (a) The image should appear. Inspect it to see that the URL is absolute.
     * (b) The button should appear. Inspect it to see that the URL is absolute. Click it and see that it opens.
     * (c) The highlighted text should be a link. Inspect it to see that the URL is absolute. Click it and see that it opens.
* (MEP-2) Check the second block:
     * (a) The image should appear. Inspect it to see that the URL is absolute.
     * (b) The button should appear. Inspect it to see that the URL is absolute. Click it and see that it opens.
* (MEP-3) Check the footer block.
     * (a) The social media icons should appear. Inspect one to see that the image URL is absolute.
     * (b) The social media icons should be links. Inspect one to see that the link is absolute. Click it and see that it opens.

## Manual Tests: Save/Load Template

1. Create a new template
    1. Navigate to "Mailings => Mosaico Templates"
    2. _Observe_: There is a section for "Create new template from...".
    3. _Observe_: There *may be* a section "Configured templates" if some templates exist.
    4. Under "Create new template from...", select one of the base templates like "Versafix 1"
    5. Enter a template name. (Make a mental note of the name.)
    6. _Observe_: A full-screen dialog opens with Mosaico.
    7. Create a block. Edit some text. (Make a mental note of the content.)
    8. Save.
2. Immediately re-edit
    1. Under "Configured templates", find your template and click the thumbnail.
    2. _Observe_: A full-screen dialog opens with Mosaico. It restores the content from before.
    3. Save
3. Try a full reload
    1. Go to any other page (such as the dashboard).
    2. Navigate to "Mailings => Mosaico Templates"
    3. Under "Configured templates", find your template and click the thumbnail.
    4. _Observe_: A full-screen dialog opens with Mosaico. It restores the content from before.
4. Copy a template
    1. Under "Configured templates", find your template and click the copy icon.
    2. Enter a new template name. (Make a mental note of the name.)
    3.  _Observe_: A full-screen dialog opens with Mosaico. It restores the content from before.
    4. Create another block.  Edit some text.
    5. Save
    6. Open both the old and new templates. Check that they have the right content.
5. Delete the copied template
    1. Under "Configured templates", find your new copied template and click the red X.
    2. _Observe_: The template goes away.
6. Rename a template
    1. Under "Configured templates", find your template and click the wrench icon.
    2. Enter a new template name.
    3. _Observe_: The name updates.

## BackstopJS Visual Regression testing
This test suite is based on [BackstopJS](https://garris.github.io/BackstopJS) plugin. Backstop JS uses pupetter and headless chrome to create reference screenshots and use them to compare new screenshots and raise any incosistency in the visuals of the page (if introduced while developing something)

Documentation available [here](https://github.com/garris/BackstopJS#backstopjs)

### Requirements
 * A sample Mosaico custom configured template.
    #### Steps to create
     * Go to *Mailing > Mosaico Template* ('civicrm/a/#/mosaico-template')
     * Click on any new Base template and create a new custom template.

 * A sample Unscheduled mailing draft with the first two steps forms prefilled.
     #### Steps to create
     * Go to *Mailing > New Mailing*
     * Fill up the first form and second form with some random data and click 'Save Draft'.
     * This new draft should be available under *Mailing > Draft and Unscheduled Mailings* (`/civicrm/mailing/browse/unscheduled?reset=1&scheduled=false`)
     * Click on any new Base template and create a new custom template.

#### Steps to setup

1. Install node package depedencies using . (Skip this step if already installed)
```shell
npm install 
```
2. Create a `test/backstop/site-config.json` file with the following content.
    ```json
    {
      "url": "your_local_url",
      "root": "absolute_path_to_site"
    }
    ```
3. Create the reference screenshots
    ```shell
    gulp backstopjs:reference
    ```
4. Create the test screenshots and compare
    ```shell
    gulp backstopjs:test
    ```

#### Parallel capturing
BackstopJS supports taking multiple screenshot at once. Change the value of `asyncCaptureLimit` in _backstop.tpl.json_ to decide how many screenshots you want to take in parallel

***Note**:Please be aware that BackstopJS performance is heavily dependent on the specs of the machine it runs on, so make sure to choose a value that the tool can handle on your machine (otherwise you will encounter random timeout errors)*
