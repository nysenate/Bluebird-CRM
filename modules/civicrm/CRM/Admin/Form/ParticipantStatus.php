<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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

require_once 'CRM/Admin/Form.php';
require_once 'CRM/Event/BAO/ParticipantStatusType.php';

class CRM_Admin_Form_ParticipantStatus extends CRM_Admin_Form
{
    public function buildQuickForm()
    {
        parent::buildQuickForm();

        if ($this->_action & CRM_Core_Action::DELETE) return;

        $this->applyFilter('__ALL__', 'trim');

        $attributes = CRM_Core_DAO::getAttribute('CRM_Event_DAO_ParticipantStatusType');

        $this->add('text', 'name',  ts('Name'),  $attributes['name'],  true);

        $this->add('text', 'label', ts('Label'), $attributes['label'], true);

        $classes = array();
        foreach (array('Positive', 'Pending', 'Waiting', 'Negative') as $class) {
            $classes[$class] = CRM_Event_DAO_ParticipantStatusType::tsEnum('class', $class);
        }
        $this->add('select', 'class', ts('Class'), $classes, true);

        $this->add('checkbox', 'is_active',  ts('Active?'));
        $this->add('checkbox', 'is_counted', ts('Counted?'));

        $this->add('text', 'weight',  ts('Weight'),  $attributes['weight'], true);

        require_once 'CRM/Core/PseudoConstant.php';
        $this->add('select', 'visibility_id', ts('Visibility'), CRM_Core_PseudoConstant::visibility(), true);
    }

    function setDefaultValues()
    {
        $defaults = parent::setDefaultValues();
        if (!CRM_Utils_Array::value('weight', $defaults)) {
            require_once 'CRM/Utils/Weight.php';
            $defaults['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Event_DAO_ParticipantStatusType');
        }
        $this->_isReserved = $defaults['is_reserved'];
        if ($this->_isReserved) $this->freeze(array('name', 'class', 'is_active'));
        return $defaults;
    }

    function postProcess()
    {
        if ($this->_action & CRM_Core_Action::DELETE) {
            if (CRM_Event_BAO_ParticipantStatusType::deleteParticipantStatusType($this->_id)) {
                CRM_Core_Session::setStatus(ts('Selected participant status has been deleted.'));
            } else {
                CRM_Core_Session::setStatus(ts('Selected participant status has <strong>NOT</strong> been deleted; there are still participants with this status.'));
            }
            return;
        }

        $formValues = $this->controller->exportValues($this->_name);

        $params = array(
            'name'          => CRM_Utils_Array::value('name',          $formValues),
            'label'         => CRM_Utils_Array::value('label',         $formValues),
            'class'         => CRM_Utils_Array::value('class',         $formValues),
            'is_active'     => CRM_Utils_Array::value('is_active',     $formValues, false),
            'is_counted'    => CRM_Utils_Array::value('is_counted',    $formValues, false),
            'weight'        => CRM_Utils_Array::value('weight',        $formValues),
            'visibility_id' => CRM_Utils_Array::value('visibility_id', $formValues),
        );

        // make sure a malicious POST does not change these on reserved statuses
        if ($this->_isReserved) unset($params['name'], $params['class'], $params['is_active']);

        if ($this->_action & CRM_Core_Action::UPDATE) $params['id'] = $this->_id;

        require_once 'CRM/Utils/Weight.php';
        if ($this->_id) {
            $oldWeight = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_ParticipantStatusType', $this->_id, 'weight', 'id');
        } else {
            $oldWeight = 0;
        }
        $params['weight'] = CRM_Utils_Weight::updateOtherWeights('CRM_Event_DAO_ParticipantStatusType', $oldWeight, $params['weight']);

        $participantStatus = CRM_Event_BAO_ParticipantStatusType::create($params);

        if ($participantStatus->id) {
            if ($this->_action & CRM_Core_Action::UPDATE) {
                CRM_Core_Session::setStatus(ts('The Participant Status has been updated.'));
            } else {
                CRM_Core_Session::setStatus(ts('The new Participant Status has been saved.'));
            }
        } else {
            CRM_Core_Session::setStatus(ts('The changes have not been saved.'));
        }
    }
}
