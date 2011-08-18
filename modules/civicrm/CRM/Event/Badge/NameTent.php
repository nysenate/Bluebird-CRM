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

/*
* Copyright (C) 2010 Tech To The People
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*/
/**
 *
 * @package CRM
 *
 */

require_once 'CRM/Event/Badge.php';

class CRM_Event_Badge_NameTent extends CRM_Event_Badge {

    function __construct() {
      parent::__construct();
      $pw=297; $ph=210;// A4
      $this->lMargin = 10;
      $this->tMargin = 0;
      $w=$pw - 2*$this->lMarging; 
      $h=$ph - 2*$this->tMargin;
      $this->format = array('name' => 'A4 horiz', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => 0,
                              'tMargin' => 0, 'NX' => 1, 'NY' => 1, 'SpaceX' => 0, 'SpaceY' => 0,
                              'width' => $w, 'height' => $h, 'font-size' => 36);
//      $this->setDebug ();
    }

    function pdfExtraFormat() {
        $this->pdf->setPageFormat('A4', 'L');
    }

    protected function writeOneSide(&$participant) {
        $this->pdf->SetXY(0,$this->pdf->height/2);
        $this->printBackground (true);
        $txt = $participant['first_name']. " ".$participant['last_name'];
        $this->pdf->SetXY(0,$this->pdf->height/2+20);
        $this->pdf->SetFontSize(54);
        $this->pdf->Write (0, $txt,null,null,'C');
        $this->pdf->SetXY(0,$this->pdf->height/2+50);
        $this->pdf->SetFontSize(36);
        $this->pdf->Write (0, $participant['current_employer'],null,null,'C');

    }

    public function generateLabel($participant) {
        $this->writeOneSide($participant);
        $this->pdf->StartTransform();
        $this->pdf->Rotate(180,$this->pdf->width/2+$this->pdf->marginLeft,$this->pdf->height/2);
        $this->writeOneSide($participant);
        $this->pdf->StopTransform();
    }

}

