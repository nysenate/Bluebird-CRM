# Data Setup/Requirements
 The backstopJS test suite needs following to be setup in prior

## Dashboard Section
* The current calendar month has at least one day with some activities.
* Have at least that many acitivities on activity panel, so that it shows load more button.

## Contact Section
* Make sure 'admin' contact have atleast one activity and atleast 4 cases for itself.

## Activities Section
* Make sure that there exists at least one tag and one tagset for activities.

## Cases
* Make sure there is a `sample.txt` file in the root of the folder. This is to test file upload functionality of files tab.
---

# Covered Screens
The backstop test suite for Civicase 5.1 extension covers following screens

## Dashboard
- [x] Dashboard Main screen - With overview table expanded and tooltip visible on one of the titles
- [x] Dashboard Main screen - With overview table expanded, gear icon opened and case filter dropdown opened
- [x] Dashboard Main screen - with loading screens
- [x] Dashboard Main screen - Calendar - Acitivity card
- [x] Dashboard Main screen - Calendar - Acitivity card - Loading State
- [x] Dashboard Main screen - Add case modal

## Activities Feed Panel
- [x] Activities Feed Panel - Main screen
- [x] Activities Feed Panel - Loading screen
- [x] Activities Feed Panel - Bulk action Checkbox enabled and all checkboxes checked, and bulk action dropdown opened
- [x] Activities Feed Panel - Bulk Actions - Move to case
- [x] Activities Feed Panel - Bulk Actions - Copy to case
- [x] Activities Feed Panel - Bulk Actions - Add tags
- [x] Activities Feed Panel - Bulk Actions - Remove tags
- [x] Activities Feed Panel - Bulk Actions - Delete activities
- [x] Activities Feed Panel - Load more state
- [x] Activities Feed Panel - filter enabled and one dropdown opened
- [x] Activities Feed Panel - one activity selected
- [x] Activities Feed Panel - Selected Activity - Details - Maximise
- [x] Activities Feed Panel - Selected Activity - Details - Status Dropdown
- [x] Activities Feed Panel - Selected Activity - Details - Priority Dropdown
- [x] Activities Feed Panel - Activity card menu on case overview
- [x] Activities Feed Panel - Detail - Edit state
- [x] Activities Feed Panel - Under Manage Cases
- [x] Activities Feed Panel - Under Contact Page

## Manage Cases Screens
- [x] Manage Cases List - Main screen
- [x] Manage Cases List - Other Criterion filter button
- [x] Manage Cases List - Loading screen
- [x] Manage Cases List - selected case
- [x] Manage Cases List - Case Overview - with drawer closed
- [x] Manage Cases List - Case Overview - with calendar activity opened
- [x] Manage Cases List - Case Overview - Loading
- [x] Manage Cases List - Case Overview - Edit custom Data
- [x] Manage Cases List - Case Overview - Add new - Open the popup
- [x] Manage Cases List - People - Case Roles
- [x] Manage Cases List - People - Case Roles - Loading
- [x] Manage Cases List - People - Other Relationships
- [x] Manage Cases List - People - Other Relationships - Loading
- [x] Manage Cases List - Files
- [x] Manage Cases List - Files - Loading
- [x] Manage Cases List - Files - Upload files - *Skip this if is hard to implement*
- [x] Manage Cases List - Case Overview - Action Dropdown - Email Case Manager
- [x] Manage Cases List - Case Overview - Action Dropdown - Export cases
- [x] Manage Cases List - Case Overview - Action Dropdown - Link cases
- [x] Manage Cases List - Case Overview - Action Dropdown - Print Merge Document
- [x] Manage Cases List - Case Overview - Action Dropdown
- [x] Manage Cases List - Case Overview - People tab - Assign role to a client
- [x] Manage Cases List - Case Overview - People tab - Assign role to a non client
- [x] Manage Cases List - Case Overview - People tab - Case role dropdown
- [x] Manage Cases List - Case Overview - People tab - Remove role
- [x] Manage Cases List - Case Overview - Status Dropdown - Change status
- [x] Manage Cases List - Case Overview - Status Dropdown
- [x] Manage Cases List - List - Bulk action with checkbox selected and dropdown opened
- [x] Manage Cases List - Case Overview - Action Dropdown - Delete case
- [x] Manage Cases List - Case Overview - Action Dropdown - Edit tags
- [x] Manage Cases List - Case Overview - Action Dropdown - Change case status

## Contact Page
- [x] Contact Page - Case Tab
- [x] Contact Page - Case Tab - Loading screen
- [x] Contact Page - Case Tab - Loading more results icon (When clicked on load more
- [x] Contact Page - Case Tab - Action Dropdown
- [x] Contact Page - Case Tab - Status Dropdown

## Modals
- [ ] Modals - Contact Popover
- [ ] Modals - Additional Contacts Popover
- [ ] Modals - Status popup for activity details
- [ ] Modals - Manage Cases - Next Activity - card menu
- [ ] Modals - Manage Cases - Case Detail - Actions Menu
- [ ] Modals - Manage Cases - Case Detail - Actions Menu - Edit Tags
- [ ] Modals - Manage Cases List - Files - menu actions

## Empty States
- [ ] Dashboard Main screen - Empty State
- [ ] Activities Feed Panel - Empty State
- [ ] Manage Cases List - Case Overview - Empty States

# Developer Guide

Scenario object by default contains `label` key and `url` key and [some others](https://github.com/garris/BackstopJS#advanced-scenarios)

This test suite is customised for angular app specific to civicase, so it uses some custom prorperties on top

```
waitForAjaxComplete             // Set to true if any event (click/hover) loads some content through AJAX
isUIBPopover                    // Set to true if the hover state opens a uib popover
captureLoadingScreen            // Sets to true if backstop don't want to wait for loading state to complete and capture the loading screen
```
