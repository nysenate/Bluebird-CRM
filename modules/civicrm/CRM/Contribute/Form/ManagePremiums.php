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

require_once 'CRM/Contribute/Form.php';

/**
 * This class generates form components for Premiums
 * 
 */
class CRM_Contribute_Form_ManagePremiums extends CRM_Contribute_Form
{
    /**
     * Function to pre  process the form
     *
     * @access public
     * @return None
     */
    public function preProcess() 
    {
        parent::preProcess();
 
    }

     /**
     * This function sets the default values for the form. Manage Premiums that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        require_once 'CRM/Utils/Rule.php';
        $defaults = parent::setDefaultValues( );
        if ( $this->_id ) {
            $params = array( 'id' => $this->_id );
            CRM_Contribute_BAO_ManagePremiums::retrieve( $params , $tempDefaults );
            $imageUrl  = (isset($tempDefaults['image'])) ? $tempDefaults['image'] : "";
            if( isset($tempDefaults['image']) &&  isset($tempDefaults['thumbnail'])) {
                $defaults ['imageUrl']     = $tempDefaults['image'];
                $defaults ['thumbnailUrl'] = $tempDefaults['thumbnail'];
                $defaults ['imageOption' ] = 'thumbnail'; 
                // assign thumbnailUrl to template so we can display current image in update mode
                $this->assign( 'thumbnailUrl', $defaults['thumbnailUrl'] );
            } else {
                $defaults ['imageOption' ] = 'noImage';
            }
            if ( isset($tempDefaults['thumbnail']) && isset($tempDefaults['image']) ) {
                $this->assign('thumbURL',$tempDefaults['thumbnail']);
                $this->assign('imageURL',$tempDefaults['image']);
            }
            if ( isset ($tempDefaults['period_type'] )  ) {
                $this->assign('showSubscriptions' , true );
            }
            
        }
       
        return $defaults;
    }


    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        //parent::buildQuickForm( );
        
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            require_once 'CRM/Contribute/BAO/Premium.php';
            CRM_Contribute_BAO_Premium::buildPremiumPreviewBlock( $this, $this->_id);
            
            $this->addButtons(array(
                                    array ('type'      => 'next',
                                           'name'      => ts('Done with Preview'),
                                           'isDefault' => true),
                                    )
                              );
            
            return;
        }
        
        if ($this->_action & CRM_Core_Action::DELETE ) { 
            $this->addButtons(array(
                                    array ('type'      => 'next',
                                           'name'      => ts('Delete'),
                                           'isDefault' => true),
                                    array ('type'      => 'cancel',
                                           'name'      => ts('Cancel')),
                                    )
                              );
            return;
        }

        $this->applyFilter('__ALL__', 'trim');
        $this->add('text', 'name', ts('Name'), CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'name' ),true );
        $this->addRule( 'name', ts('A product with this name already exists. Please select another name.'), 'objectExists', array( 'CRM_Contribute_DAO_Product', $this->_id ) );
        $this->add('text', 'sku', ts('SKU'), CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'sku' ));

        $this->add('textarea', 'description', ts('Description'), 'rows=3, cols=60' );

        $image['image']     = $this->createElement('radio',null, null,ts('Upload from my computer'),'image','onclick="add_upload_file_block(\'image\');');
        $image['thumbnail'] = $this->createElement('radio',null, null,ts('Display image and thumbnail from these locations on the web:'),'thumbnail', 'onclick="add_upload_file_block(\'thumbnail\');');
        $image['default_image']   = $this->createElement('radio',null, null,ts('Use default image'),'default_image', 'onclick="add_upload_file_block(\'default\');');
        $image['noImage']   = $this->createElement('radio',null, null,ts('Do not display an image'),'noImage','onclick="add_upload_file_block(\'noImage\');');

        $this->addGroup($image,'imageOption',ts('Premium Image'));
        $this->addRule( 'imageOption', ts('Please select an option for the premium image.'), 'required' );
        
        $this->addElement( 'text', 'imageUrl',ts('Image URL'));
        $this->addRule('imageUrl','Please enter the valid URL to display this image.','url');
        $this->addElement( 'text', 'thumbnailUrl',ts('Thumbnail URL'));
        $this->addRule('thumbnailUrl','Please enter the valid URL to display a thumbnail of this image.','url');

        $this->add( 'file','uploadFile',ts('Image File Name'), 'onChange="select_option();"');

               
        $this->add( 'text', 'price',ts('Market Value'),CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'price' ), true );
        $this->addRule( 'price', ts('Please enter the Market Value for this product.'), 'money' );
        
        $this->add( 'text', 'cost',ts('Actual Cost of Product'),CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'cost' ) );
        $this->addRule( 'price', ts('Please enter the Actual Cost of Product.'), 'money' );

        $this->add( 'text', 'min_contribution',ts('Minimum Contribution Amount'),CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'min_contribution' ), true );
        $this->addRule( 'min_contribution', ts('Please enter a monetary value for the Minimum Contribution Amount.'), 'money' );

        $this->add('textarea', 'options', ts('Options'), 'rows=3, cols=60' );

        $this->add('select', 'period_type', ts('Period Type'),array(''=>'- select -','rolling'=> 'Rolling','fixed'=>'Fixed'));
               
        $this->add('text', 'fixed_period_start_day', ts('Fixed Period Start Day'),CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'fixed_period_start_day' ));    

        
        $this->add('Select', 'duration_unit', ts('Duration Unit'),array(''=>'- select period -','day'=> 'Day','week'=>'Week','month'=>'Month','year'=>'Year'));    
    
        $this->add('text', 'duration_interval', ts('Duration'),CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'duration_interval' ));
        
        $this->add('Select', 'frequency_unit', ts('Frequency Unit'),array(''=>'- select period -','day'=> 'Day','week'=>'Week','month'=>'Month','year'=>'Year'));    

        $this->add('text', 'frequency_interval', ts('Frequency'),CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Product', 'frequency_interval' ));
       
    
        $this->add('checkbox', 'is_active', ts('Enabled?'));
        
        $this->addFormRule(array('CRM_Contribute_Form_ManagePremiums', 'formRule'));
        
        $this->addButtons( array( 
                                 array ( 'type'      => 'upload', 
                                         'name'      => ts('Save'), 
                                         'isDefault' => true   ), 
                                 array ( 'type'      => 'cancel', 
                                         'name'      => ts('Cancel') ), 
                                 ) 
                           );
             
        $this->assign('productId', $this->_id);
    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public function formRule( $params, $files) {
        if ( isset ( $params['imageOption'] ) ) {
            if ( $params['imageOption'] == 'thumbnail' ) {
                if ( ! $params['imageUrl']) {
                    $errors ['imageUrl']= 'Image URL is Required ';
                }
                if ( ! $params['thumbnailUrl']) {
                    $errors ['thumbnailUrl']= 'Thumbnail URL is Required ';
                }
            }
        }
        
        $fileLocation  = $files['uploadFile']['tmp_name'];
        if( $fileLocation != "") {
            list($width, $height ) = getimagesize($fileLocation); 
            
            if ( ($width < 80 || $width > 500) ||  ( $height  < 80 || $height > 500) ) {
                //$errors ['uploadFile'] = "Please Enter files with dimensions between 80 x 80 and 500 x 500," . " Dimensions of this file is ".$width."X".$height;
            }
        }

        if ( ! $params['period_type'] ) {
            if ( $params['fixed_period_start_day'] || $params['duration_unit'] || $params['duration_interval'] ||
                 $params['frequency_unit'] || $params['frequency_interval'] ) {
                $errors ['period_type']= ts('Please select the Period Type for this subscription or service.');
            }
        }
        
        if ( $params['period_type'] == 'fixed' && ! $params['fixed_period_start_day'] ) {
            $errors['fixed_period_start_day'] = ts('Please enter a Fixed Period Start Day for this subscription or service.');
        }

        if( $params['duration_unit'] && ! $params['duration_interval'] ) {
            $errors ['duration_interval']= ts('Please enter the Duration Interval for this subscription or service.');
        }

        if( $params['duration_interval'] && ! $params['duration_unit'] ) {
            $errors ['duration_unit']= ts('Please enter the Duration Unit for this subscription or service.');
        }

        if( $params['frequency_interval'] && ! $params['frequency_unit'] ) {
            $errors ['frequency_unit']= ts('Please enter the Frequency Unit for this subscription or service.');
        }

        if( $params['frequency_unit'] && ! $params['frequency_interval'] ) {
            $errors ['frequency_interval']= ts('Please enter the Frequency Interval for this subscription or service.');
        }


        return empty($errors) ? true : $errors;
    }
   
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        require_once 'CRM/Contribute/BAO/ManagePremiums.php';
        
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            return;
        }
        
        if($this->_action & CRM_Core_Action::DELETE) {
            CRM_Contribute_BAO_ManagePremiums::del($this->_id);
            CRM_Core_Session::setStatus( ts('Selected Premium Product type has been deleted.') );
        } else { 
            $params = $this->controller->exportValues( $this->_name );
            $imageFile = CRM_Utils_Array::value( 'uploadFile', $params );
            $imageFile = $imageFile['name'];

            $config = & CRM_Core_Config::singleton();
           
            $ids   = array( );
            $error = false; 
            // store the submitted values in an array

            // FIX ME 
            if(CRM_Utils_Array::value( 'imageOption',$params, false )) {
                $value = CRM_Utils_Array::value( 'imageOption',$params, false );
                if ( $value == 'image' ) {
                    if ( $imageFile ) {
                        $fileName = basename( $imageFile );
                        $params['image'] = $config->imageUploadURL . $fileName;

                        // to check wether GD is installed or not
                        require_once 'CRM/Utils/System.php';
                        $gdSupport  = CRM_Utils_System::getModuleSetting( 'gd', 'GD Support');
                        $jpgSupport = CRM_Utils_System::getModuleSetting( 'gd', 'JPG Support');
                        $gifSupport = CRM_Utils_System::getModuleSetting( 'gd', 'GIF Read Support');
                        $pngSupport = CRM_Utils_System::getModuleSetting( 'gd', 'PNG Support');
                        $error      = false; 

                        if ( $gdSupport  == 'enabled' &&
                             $jpgSupport == 'enabled' &&
                             $gifSupport == 'enabled' &&
                             $pngSupport == 'enabled' ) {
                            list($width_orig, $height_orig) = getimagesize($imageFile);
                            $imageInfo = getimagesize($imageFile);
                            $width_orig . "<br>";
                            $height_orig . "<br>";    
                            $path = explode( '/', $imageFile );
                            $thumbFileName = $path[count($path) - 1];
                            $info   = pathinfo($thumbFileName);
                            $basename = substr($info['basename'], 0,
                                               -(strlen($info['extension']) + ($info['extension'] == '' ? 0 : 1)));
                            $thumbFileName = $basename . "_thumb." . $info['extension'];
                            $path[count($path) - 1] = $thumbFileName;
                            $path = implode('/',$path);
                            
                            $width = $height = 100;
                            
                            $thumb = imagecreate($width, $height);
                            if( $imageInfo['mime']  == 'image/gif' ) {
                                $source = imagecreatefromgif($imageFile);
                            } else if ( $imageInfo['mime']  == 'image/png' ) {
                                $source = imagecreatefrompng($imageFile);
                            } else {
                                $source = imagecreatefromjpeg($imageFile);
                            }
                            imagecopyresized($thumb,$source, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                            
                            $fp=fopen( $path ,'w+');
                            ob_start();
                            ImageJPEG($thumb);
                            $image_buffer = ob_get_contents();
                            ob_end_clean();
                            ImageDestroy($thumb);
                            fwrite($fp, $image_buffer); 
                            rewind($fp);
                            fclose($fp);
                            $params['thumbnail'] = $config->imageUploadURL .$thumbFileName;
                        } else {
                            $error = true;
                            $params['thumbnail'] =
                                $config->resourceBase .'i/contribute/default_premium_thumb.jpg'; 
                        }
                    }
                } else if (  $value == 'thumbnail' ) {
                    $params['image']     =  $params['imageUrl']    ;
                    $params['thumbnail'] =  $params['thumbnailUrl'];
                } else if ( $value == 'default_image' ) {
                    $url = parse_url($config->userFrameworkBaseURL);
                    $params['image']     = $config->resourceBase . 'i/contribute/default_premium.jpg';
                    $params['thumbnail'] = $config->resourceBase . 'i/contribute/default_premium_thumb.jpg'; 
                } else {
                    $params['image']     = "";
                    $params['thumbnail'] = "";
                }
            }

            if ($this->_action & CRM_Core_Action::UPDATE ) {
                $ids['premium'] = $this->_id;
            }
            
            // fix the money fields
            foreach ( array( 'cost', 'price', 'min_contribution' ) as $f ) {
                $params[$f] = CRM_Utils_Rule::cleanMoney( $params[$f] );
            }

            $premium = CRM_Contribute_BAO_ManagePremiums::add($params, $ids);           
            if ( $error ) {
                CRM_Core_Session::setStatus(ts('NOTICE: No thumbnail of your image was created because the GD image library is not currently compiled in your PHP installation. Product is currently configured to use default thumbnail image. If you have a local thumbnail image you can upload it separately and input the thumbnail URL by editing this premium.'));
            } else {
                CRM_Core_Session::setStatus( ts('The Premium \'%1\' has been saved.', array( 1 => $premium->name )) );
            }
        }
    }
}


