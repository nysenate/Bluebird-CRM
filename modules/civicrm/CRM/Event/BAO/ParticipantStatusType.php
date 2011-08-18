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


require_once 'CRM/Event/DAO/ParticipantStatusType.php';

class CRM_Event_BAO_ParticipantStatusType extends CRM_Event_DAO_ParticipantStatusType
{
    static function add(&$params)
    {
        if (empty($params)) return null;
        $dao = new CRM_Event_DAO_ParticipantStatusType;
        $dao->copyValues($params);
        return $dao->save();
    }

    static function &create(&$params)
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction;
        $statusType = self::add($params);
        if (is_a($statusType, 'CRM_Core_Error')) {
            $transaction->rollback();
            return $statusType;
        }
        $transaction->commit();
        return $statusType;
    }

    static function deleteParticipantStatusType($id)
    {
        // return early if there are participants with this status
        require_once 'CRM/Event/DAO/Participant.php';
        $participant = new CRM_Event_DAO_Participant;
        $participant->status_id = $id;
        if ($participant->find()) return false;

        require_once 'CRM/Utils/Weight.php';
        CRM_Utils_Weight::delWeight('CRM_Event_DAO_ParticipantStatusType', $id);

        $dao = new CRM_Event_DAO_ParticipantStatusType;
        $dao->id = $id;
        $dao->find(true);
        $dao->delete();
        return true;
    }

    static function retrieve(&$params, &$defaults)
    {
        $result = null;

        $dao = new CRM_Event_DAO_ParticipantStatusType;
        $dao->copyValues($params);
        if ($dao->find(true)) {
            CRM_Core_DAO::storeValues($dao, $defaults);
            $result = $dao;
        }

        return $result;
    }

    static function setIsActive($id, $isActive)
    {
        return CRM_Core_DAO::setFieldValue('CRM_Event_BAO_ParticipantStatusType', $id, 'is_active', $isActive);
    }
}
