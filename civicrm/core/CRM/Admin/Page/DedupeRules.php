<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Page/Basic.php';
require_once 'CRM/Dedupe/DAO/Rule.php';
require_once 'CRM/Dedupe/DAO/RuleGroup.php';

class CRM_Admin_Page_DedupeRules extends CRM_Core_Page_Basic
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * Get BAO Name
     *
     * @return string Classname of BAO.
     */
    function getBAOName()
    {
        return 'CRM_Dedupe_BAO_RuleGroup';
    }

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
    {
          if (!(self::$_links)) {
              $deleteExtra  = ts('Are you sure you want to delete this Rule?');
              $defaultExtra = ts('Are you sure you want to make this Rule default?');

              // helper variable for nicer formatting
              self::$_links = array(
                  CRM_Core_Action::VIEW  => array(
                      'name'  => ts('Use Rule'),
                      'url'   => 'civicrm/admin/dedupefind',
                      'qs'    => 'reset=1&rgid=%%id%%&action=preview',
                      'title' => ts('Use DedupeRule'),
                      ),
                  CRM_Core_Action::UPDATE  => array(
                      'name'  => ts('Edit Rule'),
                      'url'   => 'civicrm/admin/deduperules',
                      'qs'    => 'action=update&id=%%id%%',
                      'title' => ts('Edit DedupeRule'),
                  ),
                  CRM_Core_Action::MAP  => array(
                      'name'  => ts('Make Default'),
                      'url'   => 'civicrm/admin/deduperules',
                      'qs'    => 'action=map&id=%%id%%',
                      'extra' => 'onclick = "return confirm(\'' . $defaultExtra . '\');"',
                      'title' => ts('Default DedupeRule'),
                  ),
                  CRM_Core_Action::DELETE  => array(
                      'name'  => ts('Delete'),
                      'url'   => 'civicrm/admin/deduperules',
                      'qs'    => 'action=delete&id=%%id%%',
                      'extra' => 'onclick = "return confirm(\'' . $deleteExtra . '\');"',
                      'title' => ts('Delete DedupeRule'),
                  ),
              );
        }
        return self::$_links;
    }

    /**
     * Run the page
     *
     * This method is called after the page is created. It checks for the type
     * of action and executes that action. Finally it calls the parent's run
     * method.
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        // get the requested action, default to 'browse'
        $action = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse');

        // assign vars to templates
        $this->assign('action', $action);
        $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, false, 0);

        // which action to take?
        if ($action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD)) {
            $this->edit($action, $id);
        }
        if ($action & CRM_Core_Action::DELETE ) {
            $this->delete($id);
        }
        if ($action & CRM_Core_Action::MAP ) {
            $rgDao             = new CRM_Dedupe_DAO_RuleGroup();
            $rgDao->id         = $id;
            $rgDao->find(true);
            $rgDao->is_default = 1;
            $query = "UPDATE civicrm_dedupe_rule_group SET is_default = 0 WHERE contact_type = '{$rgDao->contact_type}' AND LEVEL = '{$rgDao->level}'";
            CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);
            $rgDao->save();
        }

        // browse the rules
        $this->browse();

        // parent run
        parent::run();
    }

    /**
     * Browse all rule groups
     *  
     * @return void
     * @access public
     */
    function browse()
    {
        // get all rule groups
        $ruleGroups = array();
        $dao = new CRM_Dedupe_DAO_RuleGroup();
        $dao->orderBy('contact_type,level,is_default DESC');
        $dao->find();
        
        while ($dao->fetch()) {
            $ruleGroups[$dao->id] = array();
            CRM_Core_DAO::storeValues($dao, $ruleGroups[$dao->id]);
     
            // form all action links
            $action = array_sum(array_keys($this->links()));
            $links = self::links();
            if ($dao->is_default) {
                unset($links[CRM_Core_Action::MAP]);
                unset($links[CRM_Core_Action::DELETE]);
            } 
            $ruleGroups[$dao->id]['action'] = CRM_Core_Action::formLink($links, $action, array('id' => $dao->id));
            CRM_Dedupe_DAO_RuleGroup::addDisplayEnums($ruleGroups[$dao->id]);
        }
        $this->assign('rows', $ruleGroups);
    }

    /**
     * Get name of edit form
     *
     * @return string  classname of edit form
     */
    function editForm()
    {
        return 'CRM_Admin_Form_DedupeRules';
    }
    
    /**
     * Get edit form name
     *
     * @return string  name of this page
     */
    function editName()
    {
        return 'DedupeRules';
    }
    
    /**
     * Get user context
     *
     * @return string  user context
     */
    function userContext($mode = null)
    {
        return 'civicrm/admin/deduperules';
    }

    function delete($id)
    {
        $ruleDao = new CRM_Dedupe_DAO_Rule();
        $ruleDao->dedupe_rule_group_id = $id;
        $ruleDao->delete();

        $rgDao            = new CRM_Dedupe_DAO_RuleGroup();
        $rgDao->id        = $id;
        $rgDao->delete();
    }
}


