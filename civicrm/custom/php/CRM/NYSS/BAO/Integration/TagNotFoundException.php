<?php

class CRM_NYSS_BAO_Integration_TagNotFoundException extends Exception {

  public function __construct(string $tag = '', string $parent_tag = '', Exception $previous = null) {
    // Call the parent constructor to ensure all functionality is inherited
    parent::__construct('No Tag Found for ' . $tag . ' of parent ' . $parent_tag, 80001, $previous);
  }

}