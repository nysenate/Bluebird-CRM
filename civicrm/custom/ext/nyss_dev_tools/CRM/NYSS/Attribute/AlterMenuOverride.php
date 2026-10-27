<?php

/**
 * PHP Attribute used to mark classes that replace a core (or upstream
 * extension) page/form by re-routing its menu path to them in
 * hook_civicrm_alterMenu.
 *
 * Records the overridden path, the class that normally serves it, and the
 * upstream project that defines the route, so that tooling can verify upstream
 * still routes the path to that class (i.e. the override still applies after an
 * upgrade) and that our alterMenu routes it to the annotated class.
 *
 * Examples:
 *   #[CRM_NYSS_Attribute_AlterMenuOverride('civicrm/mailing/unsubscribe', CRM_Mailing_Page_Unsubscribe::class)]
 *   class CRM_NYSS_Mail_Page_Unsubscribe extends CRM_Mailing_Page_Unsubscribe {
 *
 *   #[CRM_NYSS_Attribute_AlterMenuOverride('civicrm/mosaico/iframe', CRM_Mosaico_Page_EditorIframe::class, 'mosaico')]
 *   class CRM_NYSS_Page_MosaicoEditor extends CRM_Mosaico_Page_Editor {
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CRM_NYSS_Attribute_AlterMenuOverride {

  /**
   * @param string $path
   *   Menu path re-routed to the annotated class
   * @param string $originalClass
   *   page_callback class that serves the path without the override
   * @param 'civicrm'|'mosaico'|'mosaicoextras'|'ode'|'contactlayout'|'recentmenu'|string $repo
   *   Upstream project that defines the route
   *   (see CRM_NYSS_Attribute_UpstreamFix::REPOS)
   */
  public function __construct(
    public string $path,
    public string $originalClass,
    public string $repo = 'civicrm',
  ) {
  }

}
