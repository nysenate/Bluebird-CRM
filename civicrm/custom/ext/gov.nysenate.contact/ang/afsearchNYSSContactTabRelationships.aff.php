<?php
// NYSS #17217
// Creates a new afform that is similar to CiviCRM's packaged afsearchTabRel
// Afform, but excludes case related relationships to simplify the list
// when a contact might have many cases.
use CRM_NYSS_Contact_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Relationships'),
  'placement' => [
    'contact_summary_tab',
  ],
  'icon' => 'fa-handshake-o',
];
