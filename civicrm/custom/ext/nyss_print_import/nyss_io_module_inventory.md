# nyss_io.module Function Inventory

Checklist for migrating functions from `modules/nyss_io/nyss_io.module`
into the `nyss_print_import` CiviCRM extension.

## Drupal Glue (likely drop or replace with CiviCRM equivalents)

- [ ] `nyss_io_menu()` _(line 17)_ — Drupal menu router; replaced by `xml/Menu/nyss_print_import.xml`
- [x] `nyss_ioimportdata_page()` _(line 33)_ — Drupal page callback; replaced by the CiviCRM form controller
- [x] `nyss_ioimport_form()` _(line 40)_ — Drupal Form API form builder; replaced by `Form/Import::buildQuickForm()`
- [x] `nyss_ioimport_form_validate()` _(line 206)_ — Drupal form validate hook; replaced by `Form/Import::validate()`
- [x] `nyss_iovalidateuploadform()` _(line 212)_ — validation logic called by above; absorbed into `Form/Import::validate()`
- [x] `nyss_ioimport_form_submit()` _(line 247)_ — Drupal form submit hook; refactored to `Form/Import::postProcess()` (per-record orchestration and post-loop complete; cleanup scripts still pending)

## Core Import Logic

- [x] `nyss_ioimportData()` _(line 713)_ — main per-record import loop; refactored to `CRM_NYSS_PrintImport_Importer::importData()`

## Option / Lookup Helpers

- [x] `fillIoGetOptions()` _(line 1025)_ — populates `$ioOptions` from CiviCRM option groups; Eliminated in favor of API entity getFields()
- [x] `ioGetOptions()` _(line 1047)_ — fetches a single option group by name; refactored to CRM_NYSS_PrintImport_Utils::getCiviOptions()
- [x] `ioGetStates()` _(line 1056)_ — fetches state/province list; refactored to CRM_NYSS_PrintImport_Utils::getCiviOptions()
- [x] `ioGetLocType()` _(line 1073)_ — fetches location type list; refactored to CRM_NYSS_PrintImport_Utils::getCiviOptions()

## Data Transformation / Normalization

- [x] `convertProperCase()` _(line 1085)_ — title-cases a string with special-case handling; refactored to CRM_NYSS_PrintImport_Handler::convertProperCase()
- [x] `convertSize()` _(line 1205)_ — converts PHP `memory_limit` string to bytes; refactored to CRM_NYSS_PrintImport_Utils::convertSize()
- [x] `_fixGender()` _(line 1212)_ — normalizes gender value on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixGender()
- [x] `_fixBirthdate()` _(line 1239)_ — normalizes birth date on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixBirthdate()
- [x] `_fixLocType()` _(line 1255)_ — normalizes location type on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixLocType()
- [x] `_fixBOERegDate()` _(line 1264)_ — normalizes BOE registration date on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixBOERegDate()
- [x] `_fixParseName()` _(line 1280)_ — parses full name into parts on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixParseName()
- [x] `_fixPrefix()` _(line 1298)_ — normalizes name prefix on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixPrefix()
- [x] `_fixSuffix()` _(line 1316)_ — normalizes name suffix on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixSuffix()
- [x] `_fixState()` _(line 1335)_ — normalizes state value on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixState()
- [x] `_fixStreetUnit()` _(line 1350)_ — normalizes street unit on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixStreetUnit()
- [x] `_fixStreetAddress()` _(line 1382)_ — normalizes street address on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixStreetAddress()
- [x] `_fixStreetNumber()` _(line 1407)_ — normalizes street number on a record; refactored to CRM_NYSS_PrintImport_Preparer::fixStreetNumber()
- [x] `_cleanData()` _(line 1989)_ — general data cleanup pass on a record; refactored to CRM_NYSS_PrintImport_Preparer::cleanData()

## Contact / Record Finders

- [x] `_findContact()` _(line 1423)_ — looks up an existing contact by dedupe rule; refactored to CRM_NYSS_PrintImport_Finder::findContact()
- [x] `_findRelatedCustom()` _(line 1505)_ — finds related custom data for a contact; refactored to CRM_NYSS_PrintImport_Finder::findRelatedCustom()
- [x] `_findAddress()` _(line 1522)_ — looks up an existing address record; refactored to CRM_NYSS_PrintImport_Finder::findAddress()
- [x] `_findDistrict()` _(line 1551)_ — looks up district info record; refactored to CRM_NYSS_PrintImport_Finder::findDistrict()
- [x] `_findEmail()` _(line 1570)_ — looks up an existing email record; refactored to CRM_NYSS_PrintImport_Finder::findEmail()
- [x] `_addressExists()` _(line 2007)_ — absorbed into `CRM_NYSS_PrintImport_Preparer::prepareAddressPrimary()` via parameterized `SELECT 1` query

## Post-Import Processors

- [x] `_processCurrentEmployer()` _(line 1595)_ — handles current employer org linkage; refactored to CRM_NYSS_PrintImport_PostProcessor::processCurrentEmployer()
- [x] `_processNote()` _(line 1681)_ — inserts a contact note; refactored to CRM_NYSS_PrintImport_PostProcessor::processNote()
- [x] `_processTags()` _(line 1715)_ — assigns tags to imported contacts; refactored to CRM_NYSS_PrintImport_PostProcessor::processTags(); tag/parent ID lookup extracted to findOrCreateTag() and findParentTagId() with in-memory caching
- [x] `_processGroup()` _(line 1841)_ — adds imported contacts to a CiviCRM group; refactored to CRM_NYSS_PrintImport_PostProcessor::processGroup()
- [x] `_processEmailImport()` _(line 1912)_ — handles email-only import mode; refactored to CRM_NYSS_PrintImport_PostProcessor::processEmailImport()
- [x] `_processAddressDelete()` _(line 1975)_ — removes stale address records; refactored to CRM_NYSS_PrintImport_PostProcessor::processAddressDelete()
- [x] `sendSummaryEmail()` _(new)_ — sends import summary to logged-in user via CRM_Utils_Mail::send(); replaces Drupal drupal_mail_send() and nyss_getfile link

## Misc / Utility

- [x] `nyss_vars()` _(line 1186)_ — debug memory profiler; refactored to `CRM_NYSS_PrintImport_Utils::logVarMemory()`
- [x] Cleanup script — `fixDupePrimary.sh`; runs after import to fix duplicate primary flags; wired into `Form/Import::postProcess()`
- [x] Cleanup script — `updateAllGreetings.php`; runs greeting generation with optional temp table for imported-only mode; wired into `Form/Import::postProcess()`
- [x] Cleanup script — `flagBadEmails.sh`; flags malformed emails and places them on hold; wired into `Form/Import::postProcess()`
- [x] `checkTagIndex()` _(line 1142)_ — dropped; unique index UI_entity_id_entity_table_tag_id is defined in CiviCRM core schema (EntityTag.entityType.php) and confirmed present in all live databases
