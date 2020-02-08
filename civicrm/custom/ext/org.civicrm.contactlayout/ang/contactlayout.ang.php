<?php
// Declare contactlayout angular module

return [
  'js' => [
    'ang/contactlayout/*.js',
    'ang/contactlayout/*/*.js',
  ],
  'css' => [
    'ang/contactlayout.css',
  ],
  'partials' => [
    'ang/contactlayout',
  ],
  'requires' => ['crmUi', 'crmUtil', 'ngRoute', 'ui.sortable', 'api4'],
];
