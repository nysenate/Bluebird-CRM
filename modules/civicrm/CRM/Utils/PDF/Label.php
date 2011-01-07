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
     *  Class to print labels in Avery or custom formats
     * functionality and smarts to the base PDF_Label.
     *
     * @copyright CiviCRM LLC (c) 2004-2010
     * 
     *
     */

require_once 'tcpdf/tcpdf.php';
class CRM_Utils_PDF_Label extends TCPDF {

    // make these properties public due to
    // CRM-5880
    public $averyName  = '';       // Name of format
    public $marginLeft = 0;        // Left margin of labels
    public $marginTop  = 0;        // Top margin of labels
    public $xSpace     = 0;        // Horizontal space between 2 labels
    public $ySpace     = 0;        // Vertical space between 2 labels
    public $xNumber    = 0;        // Number of labels horizontally
    public $yNumber    = 0;        // Number of labels vertically
    public $width      = 0;        // Width of label
    public $height     = 0;        // Height of label
    public $charSize   = 10;       // Character size
    public $lineHeight = 10;       // Default line height
    public $metric     = 'mm';     // Type of metric for labels.. Will help to calculate good values
    public $metricDoc  = 'mm';     // Type of metric for the document
    public $fontName   = 'symbol'; // Name of the font
    public $countX     = 0;
    public $countY     = 0;
    
    // Listing of labels size
    protected  $averyLabels =
        array (
               '5160' => array('name' => '5160', 'paper-size' => 'letter', 'metric' => 'mm',
                               'lMargin' => 4.7625, 'tMargin' => 12.7, 'NX' => 3, 'NY' => 10,
                               'SpaceX' => 3.96875, 'SpaceY' => 0, 'width' => 65.875, 'height' => 25.4,
                               'font-size' => 8),
               '5161' => array('name' => '5161', 'paper-size' => 'letter', 'metric' => 'mm',  
                               'lMargin' => 0.967, 'tMargin' => 10.7, 'NX' => 2, 'NY' => 10, 
                               'SpaceX' => 3.967, 'SpaceY' => 0, 'width' => 101.6,
                               'height' => 25.4, 'font-size' => 8),
               '5162' => array('name' => '5162', 'paper-size' => 'letter', 'metric' => 'mm', 
                               'lMargin' => 0.97, 'tMargin' => 20.224, 'NX' => 2, 'NY' => 7, 
                               'SpaceX' => 4.762, 'SpaceY' => 0, 'width' => 100.807, 
                               'height' => 35.72, 'font-size' => 8),
               '5163' => array('name' => '5163', 'paper-size' => 'letter', 'metric' => 'mm',
                               'lMargin' => 1.762,'tMargin' => 10.7, 'NX' => 2,
                               'NY' => 5, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 101.6,
                               'height' => 50.8, 'font-size' => 8),
               '5164' => array('name' => '5164', 'paper-size' => 'letter', 'metric' => 'in',
                               'lMargin' => 0.148, 'tMargin' => 0.5, 'NX' => 2, 'NY' => 3, 
                               'SpaceX' => 0.2031, 'SpaceY' => 0, 'width' => 4.0, 'height' => 3.33,
                               'font-size' => 12),
               '8600' => array('name' => '8600', 'paper-size' => 'letter', 'metric' => 'mm',
                               'lMargin' => 7.1, 'tMargin' => 19, 'NX' => 3, 'NY' => 10,
                               'SpaceX' => 9.5, 'SpaceY' => 3.1, 'width' => 66.6,
                               'height' => 25.4, 'font-size' => 8),
               'L7160' => array('name' => 'L7160', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => 6,
                                'tMargin' => 15.1, 'NX' => 3, 'NY' => 7, 'SpaceX' => 2.5, 'SpaceY' => 0,
                                'width' => 63.5, 'height' => 38.1, 'font-size' => 9),
               'L7161' => array('name' => 'L7161', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => 6,
                                'tMargin' => 9, 'NX' => 3, 'NY' => 6, 'SpaceX'=> 5, 'SpaceY' => 2,
                                'width' => 63.5, 'height' => 46.6, 'font-size' => 9),
               'L7163' => array('name' => 'L7163', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => 5,
                                'tMargin' => 15, 'NX' => 2, 'NY' => 7, 'SpaceX' => 2.5, 'SpaceY' => 0,
                                'width' => 99.1, 'height' => 38.1, 'font-size' => 9)
               );
   
    /**
     * Constructor 
     *
     * @param $format type of label ($_AveryValues)
     * @param unit type of unit used we can define your label properties in inches by setting metric to 'in'
     *
     * @access public
     */

   function __construct ($format, $unit='mm') {
       if (is_array($format)) {
           // Custom format
           $tFormat = $format;
       } else {
           // Avery format
           $tFormat = $this->averyLabels[$format];
       }
       
       parent::__construct('P', $tFormat['metric'], $tFormat['paper-size']);
       $this->SetFormat($tFormat);
       $this->generatorMethod = null;
//     $this->SetFontName('Arial'); uncomment this to use non-default font
       $this->SetMargins(0,0);
       $this->SetAutoPageBreak(false);
       
       $this->metricDoc = $unit;
       // Start at the given label position
       //  if ($posX > 1) $posX--; else $posX=0;
       //         if ($posY > 1) $posY--; else $posY=0;
       //         if ($posX >=  $this->xNumber) $posX =  $this->xNumber-1;
       //         if ($posY >=  $this->yNumber) $posY =  $this->yNumber-1;
       //         $this->countX = $posX;
       //         $this->countY = $posY;
       
       if($format == CRM_Utils_Array::value('name',$averyLabels) ){
           if ($averyLabels['lMargin'] > 1) $averyLabels['lMargin']--; else $averyLabels['lMargin']=0;
           if ($averyLabels['tMargin'] > 1) $averyLabels['tMargin']--; else $averyLabels['tMargin']=0;
           if ($averyLabels['lMargin'] >=  $this->xNumber) $averyLabels['lMargin'] =  $this->xNumber-1;
           if ($averyLabels['tMargin'] >=  $this->yNumber) $averyLabels['tMargin'] =  $this->yNumber-1;
           $this->countX = $averyLabels['lMargin'];
           $this->countY = $averyLabels['tMargin'];
       }
   }
    
   /*
    * function to convert units (in to mm, mm to in)
    *
    */ 
    function ConvertMetric ($value, $src, $dest) {
        if ($src != $dest) {
            $tab['in'] = 39.37008;
            $tab['mm'] = 1000;
            return $value * $tab[$dest] / $tab[$src];
        } else {
            return $value;
        }
    }
    /*
     * function to Give the height for a char size given.
     */
    function GetHeightChars($pt) {
        // Array matching character sizes and line heights
        $tableHauteurChars = array(6 => 2, 7 => 2.5, 8 => 3, 9 => 4, 10 => 5, 11 => 6, 12 => 7, 13 => 8, 14 => 9, 15 => 10);
        if (in_array($pt, array_keys($tableHauteurChars))) {
            return $tableHauteurChars[$pt];
        } else {
            return 100; // There is a prob..
        }
    }


    function SetGenerator ($objectinstance, $methodname = 'generateLabel') {
       $this->generatorMethod = $methodname;
       $this->generatorObject = $objectinstance; 
    }

    function getFormat ($averyName) {
        return self::$averyLabels[$averyName];
    }

    /*
     * function to convert units (in to mm, mm to in)
     * $format Type of $averyName
     */ 
    function SetFormat($format) {
        $this->metric     = $format['metric'];
        $this->averyName  = $format['name'];
        $this->marginLeft = $this->ConvertMetric ($format['lMargin'], $this->metric, $this->metricDoc);
        $this->marginTop  = $this->ConvertMetric ($format['tMargin'], $this->metric, $this->metricDoc);
        $this->xSpace     = $this->ConvertMetric ($format['SpaceX'], $this->metric, $this->metricDoc);
        $this->ySpace     = $this->ConvertMetric ($format['SpaceY'], $this->metric, $this->metricDoc);
        $this->xNumber    = $format['NX'];
        $this->yNumber    = $format['NY'];
        $this->width      = $this->ConvertMetric ($format['width'], $this->metric, $this->metricDoc);
        $this->height     = $this->ConvertMetric ($format['height'], $this->metric, $this->metricDoc);
        $this->LabelSetFontSize($format['font-size']);
    }
    /*
     * function to set the character size
     * $pt weight of character
     */
    function LabelSetFontSize($pt) {
        if ($pt > 3) {
            $this->charSize = $pt;
            $this->lineHeight = $this->GetHeightChars($pt);
            $this->SetFontSize($this->charSize);
        }
    }
    /*
     * Method to change font name
     *
     * $fontname name of font 
     */
    function SetFontName($fontname) {
        if ($fontname != '') {
            $this->fontName = $fontname;
            $this->SetFont($this->fontName);
        }
    }
   
    /*
     * function to Generate the pdf of one label (can be modified using SetGenerator)
     */
    function generateLabel($var) {
        //wrap the text if it's width is greater than maxwidth 
//      $this->wordWrap( $texte, $maxwidth); not supported by TCPDF, which does its own wrapping
        $this->MultiCell($this->width, $this->lineHeight, $var, '', 'L');
    }

 
    /*
     * function to Print a label
     */
    function AddPdfLabel($texte) {
        $posX = $this->marginLeft+($this->countX*($this->width+$this->xSpace));
        $posY = $this->marginTop+($this->countY*($this->height+$this->ySpace));
        $this->SetXY($posX+3, $posY+3);
        $maxwidth = $this->width;
        if ($this->generatorMethod) {
          call_user_func_array (array($this->generatorObject, $this->generatorMethod),array($texte) );
        } else {  
           $this->generateLabel($texte);
        }
        $this->countY++;
        
        if ($this->countY == $this->yNumber) {
            // End of column reached, we start a new one
            $this->countX++;
            $this->countY=0;
        }
        
        if ($this->countX == $this->xNumber) {
            // Page full, we start a new one
            $this->countX=0;
            $this->countY=0;
        }
        
        // We are in a new page, then we must add a page
        if (($this->countX ==0) and ($this->countY==0)) {
            $this->AddPage();
        }
    }
    
}


