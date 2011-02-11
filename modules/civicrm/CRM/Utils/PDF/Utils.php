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


class CRM_Utils_PDF_Utils {

    static function domlib( $text,
                            $fileName = 'civicrm.pdf',
                            $output = false,
                            $orientation = 'landscape',
                            $paperSize   = 'a3' ) {
        require_once 'packages/dompdf/dompdf_config.inc.php';
        $dompdf = new DOMPDF( );
        
        $values = array( );
        if ( ! is_array( $text ) ) {
            $values =  array( $text );
        } else {
            $values =& $text;
        }

        $first = true;
        
        $html = '
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <style>
      .page_break {
        page-break-before: always;
      }
    </style>
  </head>
  <body>';

        $htmlElementstoStrip = array(
                                     '@<head[^>]*?>.*?</head>@siu',
                                     '@<body>@siu',
                                     '@</body>@siu',
                                     '@<html[^>]*?>@siu',
                                     '@</html>@siu',
                                     '@<!DOCTYPE[^>]*?>@siu',
                                     );
        $htmlElementsInstead = array("","","","","","");                     
        
        foreach ( $values as $value ) {
            if ( $first ) {
                $first = false;
                $pattern = '@<html[^>]*?>.*?<body>@siu';
                preg_match($pattern, $value['html'], $matches);
                //If there is a header in the template it will be used instead of the default header
                $html = $matches[0] ? $matches[0] : nothing;
                //$html .= "<h2>{$value['to']}: {$value['subject']}</h2><p>"; //If needed it should be generated through the message template
            } else {
                $html .= "<div style=\"page-break-after: always\"></div>";
                //$html .= "<h2 class=\"page_break\">{$value['to']}: {$value['subject']}</h2><p>"; //If needed it should be generated through the message template
            }
            if ( $value['html'] ) {
                //Strip the header from the template to avoid multiple headers within one document causing invalid html
                $value['html'] = preg_replace( $htmlElementstoStrip,
                                               $htmlElementsInstead,
                                               $value['html'] );
                $html .= "{$value['html']}\n";              
            } else {
                $html .= "{$value['body']}\n";
            }
        }

        $html .= '
          </body>
        </html>';
                        
        $dompdf->load_html(utf8_decode($html));
        $dompdf->set_paper ($paperSize, $orientation);
        $dompdf->render( );
        
        if ( $output ) {
            return $dompdf->output( );
        } else {
            $dompdf->stream( $fileName );
        }
    }

    static function html2pdf( $text,
                              $fileName = 'civicrm.pdf',
                              $orientation = 'landscape',
                              $paperSize   = 'a3',
                              $output = false ) {
        require_once 'packages/dompdf/dompdf_config.inc.php';
        spl_autoload_register('DOMPDF_autoload');
        $dompdf = new DOMPDF( );
        
        $values = array( );
        if ( ! is_array( $text ) ) {
            $values =  array( $text );
        } else {
            $values =& $text;
        }

        $html = "
<style>
.page_break {
  page-break-before: always;
}
</style>
";

        foreach ( $values as $value ) {
            $html .= "{$value}\n";
        }
        $dompdf->load_html(utf8_decode($html));
        $dompdf->set_paper ($paperSize, $orientation);
        $dompdf->render( );
        if ( $output ) {
            return $dompdf->output( );
        } else {
            $dompdf->stream( $fileName );
        }
    }

    static function &pdflib( $fileName,
                             $searchPath,
                             &$values,
                             $numPages = 1,
                             $echo    = true,
                             $output  = 'College_Match_App',
                             $creator = 'CiviCRM',
                             $author  = 'http://www.civicrm.org/',
                             $title   = '2006 College Match Scholarship Application' ) {
        try {
            $pdf = new PDFlib( );
            $pdf->set_parameter( "compatibility", "1.6");
            $pdf->set_parameter( "licensefile", "/home/paras/bin/license/pdflib.txt");

            if ( $pdf->begin_document( '', '' ) == 0 ) {
                CRM_Core_Error::statusBounce( "PDFlib Error: " . $pdf->get_errmsg( ) );
            }

            $config = CRM_Core_Config::singleton( );
            $pdf->set_parameter( 'resourcefile', $config->templateDir . '/Quest/pdf/pdflib.upr' );
            $pdf->set_parameter( 'textformat', 'utf8' );

            /* Set the search path for fonts and PDF files */
            $pdf->set_parameter( 'SearchPath', $searchPath );

            /* This line is required to avoid problems on Japanese systems */
            $pdf->set_parameter( 'hypertextencoding', 'winansi' );

            $pdf->set_info( 'Creator', $creator );
            $pdf->set_info( 'Author' , $author  );
            $pdf->set_info( 'Title'  , $title   );

            $blockContainer = $pdf->open_pdi( $fileName, '', 0 );
            if ( $blockContainer == 0 ) {
                CRM_Core_Error::statusBounce( 'PDFlib Error: ' . $pdf->get_errmsg( ) );
            }

            for ( $i = 1; $i  <= $numPages; $i++ ) {
                $page = $pdf->open_pdi_page( $blockContainer, $i, '' );
                if ( $page == 0 ) {
                    CRM_Core_Error::statusBounce( 'PDFlib Error: ' . $pdf->get_errmsg( ) );
                }
                
                $pdf->begin_page_ext( 20, 20, '' ); /* dummy page size */
                
                /* This will adjust the page size to the block container's size. */
                $pdf->fit_pdi_page( $page, 0, 0, 'adjustpage' );

             
                $status = array( );
                /* Fill all text blocks with dynamic data */
                foreach ( $values as $key => $value ) {
                    if ( is_array( $value ) ) {
                        continue;
                    }

                    // pdflib does like the forward slash character, hence convert
                    $value = str_replace( '/', '_', $value );

                    $res = $pdf->fill_textblock( $page,
                                                 $key,
                                                 $value,
                                                 'embedding encoding=winansi' );
                    /**
                    if ( $res == 0 ) {
                        CRM_Core_Error::debug( "$key, $value: $res", $pdf->get_errmsg( ) );
                    } else {
                        CRM_Core_Error::debug( "SUCCESS: $key, $value", null );
                    }
                    **/
                }
                
                $pdf->end_page_ext( '' );
                $pdf->close_pdi_page( $page );
            }

            $pdf->end_document( '' );
            $pdf->close_pdi( $blockContainer );

            $buf = $pdf->get_buffer();
            $len = strlen($buf);

            if ( $echo ) {
                header('Content-type: application/pdf');
                header("Content-Length: $len");
                header("Content-Disposition: inline; filename={$output}.pdf");
                echo $buf;
                CRM_Utils_System::civiExit( ); 
            } else {
                return $buf;
            }
        }
        catch ( PDFlibException $excp ) {
            CRM_Core_Error::statusBounce( 'PDFlib Error: Exception' .
                                          "[" . $excp->get_errnum( ) . "] " . $excp->get_apiname( ) . ": " .
                                          $excp->get_errmsg( ) );
        }
        catch (Exception $excp) {
            CRM_Core_Error::statusBounce( "PDFlib Error: " . $excp->get_errmsg( ) );
        }
    }
}


