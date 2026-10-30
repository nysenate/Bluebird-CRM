<?php

/**
 * PHP Attribute used to track upstream fixes that will allow local overrides
 * in the Bluebird repository to be removed.
 *
 * Example:
 *   #[CRM_NYSS_Attribute_UpstreamFix('civicrm', '6.20', 'https://github.com/civicrm/civicrm-core/pull/37210')]
 */
#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class CRM_NYSS_Attribute_UpstreamFix {

  /**
   * Common upstream extensions (used for IDE autocompletion to keep params consistent).
   */
  public const REPOS = [
    'civicrm',
    'mosaico',
    'mosaicoextras',
    'ode',
    'contactlayout',
    'recentmenu',
  ];

  /**
   * @param 'civicrm'|'mosaico'|'mosaicoextras'|'ode'|'contactlayout'|'recentmenu'|string $repo
   *   Name of upstream project
   * @param string $version
   *   Upstream fix version
   * @param string|null $url
   *   Optional link to upstream fix
   */
  public function __construct(
    #[\JetBrains\PhpStorm\ExpectedValues(values: self::REPOS)]
    public string $repo,
    public string $version,
    public ?string $url = NULL,
  ) {
  }

}
