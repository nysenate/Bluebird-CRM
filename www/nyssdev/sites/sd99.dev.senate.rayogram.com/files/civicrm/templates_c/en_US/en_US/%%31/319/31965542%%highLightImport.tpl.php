<?php /* Smarty version 2.6.26, created on 2010-07-06 10:41:50
         compiled from CRM/common/highLightImport.tpl */ ?>
<?php echo '
//Highlight the required field during import
paramsArray = new Array();

//build the an array of highlighted elements
'; ?>

<?php $_from = $this->_tpl_vars['highlightedFields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramName']):
?>	    
    paramsArray["<?php echo $this->_tpl_vars['paramName']; ?>
"] = "1";	    
<?php endforeach; endif; unset($_from); ?>
<?php echo '	             

//get select object of first element
selObj = document.getElementById("mapper\\[0\\]\\[0\\]");   

for ( i = 0; i < selObj.options.length; i++ ) {
    //check value is exist in array
    if (selObj.options[i].value in paramsArray) {
        //change background Color of all element whose ids start with mapper and end with [0];
        cj(\'select[id^="mapper"][id$="[0]"]\').each( function( ) {
            cj(this.options[i]).append(\' *\').css({"color":"#FF0000"});
            });
    }
}

'; ?>
<?php if ($this->_tpl_vars['relationship']): ?><?php echo '

    //Highlight the required field during import (Relationship fields*)
    paramsArrayRel = new Array();
    
    //build the an array of highlighted elements
    '; ?>

    <?php $_from = $this->_tpl_vars['highlightedRelFields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['paramsRel']):
?>
        <?php echo '
        paramsArrayRel["'; ?>
<?php echo $this->_tpl_vars['relId']; ?>
<?php echo '"] = new Array();
        '; ?>

        <?php $_from = $this->_tpl_vars['paramsRel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramNameRel']):
?>
            paramsArrayRel["<?php echo $this->_tpl_vars['relId']; ?>
"]["<?php echo $this->_tpl_vars['paramNameRel']; ?>
"] = "1";
        <?php endforeach; endif; unset($_from); ?>
    <?php endforeach; endif; unset($_from); ?>
    <?php echo '
    
    var object = \'select[id^="mapper"][id$="[0]"]\';
    cj(object).bind( \'change\', function(){highlight(this);});
    cj(\'div#map-field\').one( \'mouseenter\', function(){highlight(object);});
    
    function highlight(obj){ 
        cj(obj).each(function(){
            // get selected element id
            var currentId = this.id;
            
            // create relationship related field ID ( replace last [0] with [1] )
            var newId     = currentId.replace(/\\[0\\]$/, "\\[1\\]");
            
            // get the option value
            var selected  = cj(this).val();
            
            // get obeject of select field
            selObjRel = document.getElementById(newId);
            
            if ( paramsArrayRel[selected] != undefined ) {
                for ( i = 0; i < selObjRel.options.length; i++ ) {
                    //check value is exist in array
                    if (selObjRel.options[i].value in paramsArrayRel[selected]) {
                        cj(selObjRel).each( function( ) {
                            cj(selObjRel.options[i]).append(\' *\').css({"color":"#FF0000"});
                        });
                    }
                }
            }
        });
    }
'; ?>

<?php endif; ?>