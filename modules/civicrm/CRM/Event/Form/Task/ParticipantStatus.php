<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Event/Form/Task/Batch.php';

class CRM_Event_Form_Task_ParticipantStatus extends CRM_Event_Form_Task_Batch
{
    function buildQuickForm()
    {
        // CRM_Event_Form_Task_Batch::buildQuickForm() gets ufGroupId 
        // from the form, so set it here to the id of the reserved profile
        require_once 'CRM/Core/DAO/UFGroup.php';
        $dao = new CRM_Core_DAO_UFGroup;
        $dao->name = 'participant_status';
        $dao->find(true);
        $this->set('ufGroupId', $dao->id);

        require_once 'CRM/Event/PseudoConstant.php';
        $statuses =& CRM_Event_PseudoConstant::participantStatus();
        asort($statuses, SORT_STRING);
        $this->add('select', 'status_change', ts('Change All Statuses'),  
                   array( '' => ts('- select status -')) + $statuses, null,
                   array('onchange' => "if (this.value) setStatusesTo(this.value);") );
        $this->assign('context', 'statusChange');

        # CRM-4321: display info on users being notified if any of the below statuses is enabled
        require_once 'CRM/Event/PseudoConstant.php';
        $notifyingStatuses = array(ts('Pending from waitlist'), ts('Pending from approval'), ts('Expired'), ts('Cancelled'));
        $notifyingStatuses = array_intersect($notifyingStatuses, CRM_Event_PseudoConstant::participantStatus());
        $this->assign('notifyingStatuses', implode(', ', $notifyingStatuses));

        parent::buildQuickForm();
    }
}
