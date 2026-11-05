<?php
// Relabel the core nick_name field "Preferred Name" everywhere it's displayed
// (schema title used by forms/SearchKit/API/import-export, contact summary,
// reports, search settings).
//
// The "Nick Name" entry already exists on legacy instances (seeded by
// scripts/Old/v120.sh as "Nick Name" => "Nickname"); matching on find_word
// adopts that row instead of colliding with the domain_id+find_word unique key.
// NYSS #17730
return [
  [
    'name' => 'WordReplacement_nick_name_preferred_name',
    'entity' => 'WordReplacement',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'find_word' => 'Nick Name',
        'replace_word' => 'Preferred Name',
        'match_type' => 'wildcardMatch',
        'is_active' => TRUE,
      ],
      'match' => ['find_word'],
    ],
  ],
  [
    'name' => 'WordReplacement_nickname_preferred_name',
    'entity' => 'WordReplacement',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'find_word' => 'Nickname',
        'replace_word' => 'Preferred Name',
        'match_type' => 'wildcardMatch',
        'is_active' => TRUE,
      ],
      'match' => ['find_word'],
    ],
  ],
];
