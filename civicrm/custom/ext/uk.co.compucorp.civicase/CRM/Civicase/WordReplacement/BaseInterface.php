<?php

/**
 * Interface CRM_Civicase_WordReplacement_Base.
 */
interface CRM_Civicase_WordReplacement_BaseInterface {

  /**
   * Returns the words to be replaced and the replacement words.
   *
   * @return array
   *   [
   *     'Word to be replaced' => 'Replacement Word'
   *   ]
   */
  public function get();

}
