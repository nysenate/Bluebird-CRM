<?php

/*
 * Copyright (C) 2007 Jacob Singh, Sam Lerner
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Modified and improved upon by CiviCRM LLC (c) 2007
 */

require_once '../../../civicrm.config.php';
require_once 'CRM/Widget/Widget.php';
require_once 'CRM/Core/Error.php';

class Widget {

    public $methodTable;

	function __construct( ) {
		$this->methodTable =& CRM_Widget_Widget::methodTable( );

    }

	/**
	 * Not implemented - registers an action and unique widget ID.  Useful for stats and debugging
	 *
	 * @param int $contributionPageID
	 * @param string $widgetID
	 * @param string $action
	 * @return string
	 */
	private function registerRequest( $contributionPageID, $widgetID, $action) {
        return CRM_Widget_Widget::registerRequest( $contributionPageID,
                                                   $widgetID,
                                                   $action );
	}

	/**
	 * Gets all campaign related data and returns it as a std class.
	 *
	 * @param int $contributionPageID
	 * @param string $widgetID
	 * @return stdClass
	 */
	public function getContributionPageData( $contributionPageID, $widgetID ) {
        $data = CRM_Widget_Widget::getContributionPageData( $contributionPageID, $widgetID );
        return $data;
    }

	/**
	 * Gets embed code.  Perhaps overkill, but we can track dropoffs in this case.
     * by # of people reqeusting emebed code / number of unique instances.
	 *
     * @param int $contributionPageID
	 * @param string $widgetID
	 * @param string $format - either myspace or normal
	 * @return string
	 */
	public function getEmbedCode( $contributionPageID,
                                  $widgetID,
                                  $format = "normal" ) {
        return CRM_Widget_Widget::getEmbedCode( $contributionPageID, $widgetID, $format );
	}

}

?>