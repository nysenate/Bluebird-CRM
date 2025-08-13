<?php
use CRM_NYSS_Reports_ExtensionUtil as E;

/**
 * Generates a "shell" or "wrapper" page that adds some contextual information
 * to the afform search/report. I wanted to show what tag was being reported on.
 * So, I created a wrapper page around the afform that also includes the tag
 * label above the saved search table listing.
 */
class CRM_NYSS_Reports_Page_ContactTagReport extends CRM_Core_Page {

  public function run() {
    // Get the tag id from the query string
    $tag_id_param = CRM_Utils_Request::retrieve('tag', 'Positive', $this, TRUE);
    // Find the tag
    $tags = \Civi\Api4\Tag::get(TRUE)
      ->addSelect('name', 'label')
      ->addWhere('id', '=', $tag_id_param)
      ->setLimit(1)
      ->execute();

    $tag = $tags[0] ?? NULL;
    if (! $tag) {
      CRM_Core_Session::setStatus(ts("The specified tag was not found"), ts("Tag not found"), 'alert');
    }

    $this->assign('tag', $tag);
    // This sets the search kit filter --
    // note how $afformOptions is used in the template and passed to the search
    $this->assign('afformOptions', ['tag' => $tag['id']]);

    // important -- loads the afform via CiviCRM's angular framework
    Civi::service('angularjs.loader')->addModules(['afsearchConstituentTagSearch']);
    parent::run();
  }

}
