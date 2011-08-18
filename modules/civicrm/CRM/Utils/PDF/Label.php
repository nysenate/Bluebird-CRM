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
     *  Class to print labels in Avery or custom formats
     * functionality and smarts to the base PDF_Label.
     *
     * @copyright CiviCRM LLC (c) 2004-2011
     * 
     *
     */

require_once 'tcpdf/tcpdf.php';
class CRM_Utils_PDF_Label extends TCPDF {

    // make these properties public due to
    // CRM-5880
    public $defaults;           // Default label format values
    public $format;             // Current label format values
    public $formatName;         // Name of format
    public $marginLeft;         // Left margin of labels
    public $marginTop;          // Top margin of labels
    public $xSpace;             // Horizontal space between 2 labels
    public $ySpace;             // Vertical space between 2 labels
    public $xNumber;            // Number of labels horizontally
    public $yNumber;            // Number of labels vertically
    public $width;              // Width of label
    public $height;             // Height of label
    public $paddingLeft;        // Space between text and left edge of label
    public $paddingTop;         // Space between text and top edge of label
    public $charSize;           // Character size (in points)
    public $metricDoc;          // Metric used for all PDF doc measurements
    public $fontName;           // Name of the font
    public $fontStyle;          // 'B' bold, 'I' italic, 'BI' bold+italic
    public $paperSize;          // Paper size name
    public $orientation;        // Paper orientation
    public $paper_dimensions;   // Paper dimensions array (w, h)
    public $countX = 0;         // Counter for positioning labels
    public $countY = 0;         // Counter for positioning labels
    
    /**
     * Constructor 
     *
     * @param $format   Either the name of a Label Format in the Option Value table
     *                  or an array of Label Format values.
     * @param $unit     Unit of measure for the PDF document
     *
     * @access public
     */

   function __construct ($format, $unit='mm') {
       if (is_array($format)) {
           // Custom format
           $tFormat = $format;
       } else {
           // Saved format
           require_once "CRM/Core/BAO/LabelFormat.php";
           $tFormat = CRM_Core_BAO_LabelFormat::getByName( $format );
       }
       
       $this->LabelSetFormat($tFormat, $unit);
       parent::__construct($this->orientation, $this->metricDoc, $this->paper_dimensions );
       $this->generatorMethod = null;
       $this->SetFont($this->fontName, $this->fontStyle);
       $this->SetFontSize($this->charSize);
       $this->SetMargins(0,0);
       $this->SetAutoPageBreak(false);
       $this->setPrintHeader(false);
       $this->setPrintFooter(false);
   }
    
    function SetGenerator ($objectinstance, $methodname = 'generateLabel') {
       $this->generatorMethod = $methodname;
       $this->generatorObject = $objectinstance; 
    }
    
    function getFormatValue($name, $convert = false) {
        if (isset($this->format[$name])) {
            $value  = $this->format[$name];
            $metric = $this->format['metric'];
        } else {
            $value  = $this->defaults[$name];
            $metric = $this->defaults['metric'];
        }
        if ($convert) {
            require_once "CRM/Utils/PDF/Utils.php";
            $value = CRM_Utils_PDF_Utils::convertMetric($value, $metric, $this->metricDoc);
        }
        return $value;
    }
    
    /*
     * Function to initialize label format settings
     */ 
    function LabelSetFormat(&$format, $unit) {
        require_once "CRM/Core/BAO/LabelFormat.php";
        $this->defaults    = CRM_Core_BAO_LabelFormat::getDefaultValues();
        $this->format      =& $format;
        $this->formatName  = $this->getFormatValue('name');
        $this->paperSize   = $this->getFormatValue('paper-size');
        $this->orientation = $this->getFormatValue('orientation');
        $this->fontName    = $this->getFormatValue('font-name');
        $this->charSize    = $this->getFormatValue('font-size');
        $this->fontStyle   = $this->getFormatValue('font-style');
        $this->xNumber     = $this->getFormatValue('NX');
        $this->yNumber     = $this->getFormatValue('NY');
        $this->metricDoc   = $unit;
        $this->marginLeft  = $this->getFormatValue('lMargin', true);
        $this->marginTop   = $this->getFormatValue('tMargin', true);
        $this->xSpace      = $this->getFormatValue('SpaceX', true);
        $this->ySpace      = $this->getFormatValue('SpaceY', true);
        $this->width       = $this->getFormatValue('width', true);
        $this->height      = $this->getFormatValue('height', true);
        $this->paddingLeft = $this->getFormatValue('lPadding',true);
        $this->paddingTop  = $this->getFormatValue('tPadding',true);
        require_once "CRM/Core/BAO/PaperSize.php";
        $paperSize = CRM_Core_BAO_PaperSize::getByName( $this->paperSize );
        $w = CRM_Utils_PDF_Utils::convertMetric( $paperSize['width'],  $paperSize['metric'], $this->metricDoc );
        $h = CRM_Utils_PDF_Utils::convertMetric( $paperSize['height'], $paperSize['metric'], $this->metricDoc );
        $this->paper_dimensions = array( $w, $h );
    }
    
    /*
     * function to Generate the pdf of one label (can be modified using SetGenerator)
     */
    function generateLabel($text) {
        $this->MultiCell($this->width, 0, $text, 0, 'L', 0, 0, '', '', true, 0, false, false, $this->height);
    }
 
    /*
     * function to Print a label
     */
    function AddPdfLabel($texte) {
        $posX = $this->marginLeft+($this->countX*($this->width+$this->xSpace));
        $posY = $this->marginTop+($this->countY*($this->height+$this->ySpace));
        $this->SetXY($posX + $this->paddingLeft, $posY + $this->paddingTop);
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
    
    function getFontNames() {
        // Define labels for TCPDF core fonts
        $fontLabel = array(
            'courier'    => ts('Courier'),
            'helvetica'  => ts('Helvetica'),
            'times'      => ts('Times New Roman'),
            'dejavusans' => ts('Deja Vu Sans (UTF-8)')
        );
        $tcpdfFonts = $this->fontlist;
        foreach ( $tcpdfFonts as $fontName ) {
            if ( array_key_exists( $fontName, $fontLabel ) ) {
                $list[$fontName] = $fontLabel[$fontName];
            }
        }
        return $list;
    }
    
}


