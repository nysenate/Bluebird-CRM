# Non-primary QuickSearch

CiviCRM 5.66+ has a new QuickSearch.  It doesn't respect the "search on non-primary" flag.  This extension searches on non-primary contact fields (email, address, phone). It will also show all email/address/phone info for a contact in the search results.
The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.4+
* CiviCRM 5.66+

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Getting Started

Simply install.

## Known Issues
The "show all non-primary contact info" is currently non-configurable.  If you want to disable it, you can find this line:
```
if ($apiRequest['entity'] === 'Contact' && $apiRequest['action'] === 'autocomplete' && $apiRequest->getFieldName() === 'crm-qsearch-input') {
```
and place `return;` directly below it.

## Development Notes
The flow of the code is:
* `Contact.autocomplete` is called.
* This loads a SearchDisplay (which is hard-coded) with `SearchDisplay.run`.
* `SearchDisplay.run` has an event emitter `civi.search.defaultDisplay`.
* The display is passed back to `Contact.autocomplete, which displays it on screen.

We modify the search in 3 places:

* We intercept `SearchDisplay.run` to modify the hard-coded SearchDisplay (adding joins to the Address/Email/Phone tables and replacing searches for primary fields with non-primary fields). We also group by contact ID since these joins can return multiple results for a contact.
* We listen for `civi.search.defaultDisplay`. Normally QuickSearch will add whatever field we are searching on to the search results.  But we return all contact fields, so we remove it. This also prevents the core `civi.search.defaultDisplay` from firing (since it checks to see if `$e->display` was modified).
* We intercept the output of `Contact.autocomplete` (with hook_civicrm_apiWrappers, `toApiOutput`) to display the non-primary contact info.
