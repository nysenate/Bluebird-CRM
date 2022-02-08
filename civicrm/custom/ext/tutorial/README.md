# CiviTutorial

Create and display walkthrough tutorials for CiviCRM screens.

![Screenshot](/images/view-tour.gif)

View the latest version of this extension on CiviCRM's Gitlab:  
https://lab.civicrm.org/extensions/tutorial

## Requirements

* CiviCRM 5.12 or (preferably) later

## Usage

Once installed, visit the CiviCRM Dashboard screen to view the default tutorial. Select *Support* from the top menu bar, then *Welcome to CiviCRM*. It will be displayed to each user once.

If you are an administrator, this extension will let you edit the default tutorial on the contact summary screen, or create new tutorials for other screens. Visit the *Support* menu to get started.

New or overridden tutorials are saved as .js files in your `files/civicrm/crm-tutorials` directory.

![Screenshot](/images/edit-tour.gif)

## Extension Authors

You can easily package tutorials in an extension for any core or custom page. For example, if you want to provide a tutorial about your extension's new *Whizbang* page:
1. Navigate to the desired page, e.g. `civicrm/whizbang`
2. Select "Create new tutorial" from the *Support* menu. Configure as desired (e.g. set to "Auto run" if you want it displayed automatically the first time a user visits your page).
3. Save the tutorial. Move the resulting .js file from your `files/civicrm/crm-tutorials` directory into your extension. It must be placed in a directory named `crm-tutorials` in the root of your extension.
4. Commit the file to git and you're done! People will see the tutorial when they use your extension, and translators can localize it the same way they translate other strings in your extension.

## To do:

* ~~Support more than one tutorial per screen.~~
* ~~Support angular pages.~~
* ~~Support multilingual sites.~~
* More control over user preferences; add ability to reset whether a user has viewed a tutorial.

The extension is licensed under [AGPL-3.0](LICENSE.txt).
