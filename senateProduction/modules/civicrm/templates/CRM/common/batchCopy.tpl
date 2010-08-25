{*
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
*}
{literal}
<script type="text/javascript">
function setStatusesTo(statusId)
{
    var cId = new Array();
    var i = 0;
    {/literal}
    {foreach from=$componentIds item=field}
    {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}
    for (k = 0; k < cId.length; k++) {
        document.getElementById("field_"+cId[k]+"_participant_status_id").value = statusId;
    }
}

function copyValues(fieldName, source)
{
    var cId = new Array();
    var i = 0;
    var editor = {/literal}"{$editor}"{literal};
    {/literal}
    {foreach from=$componentIds item=field}
        {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}

    if (source === undefined) source = "field_"+cId[0]+"_"+fieldName;

    if ( document.getElementById(source) ) {
        if ( document.getElementById(source).type == 'select-multiple' ) {
            var multiSelectList = document.getElementById(source).options;
            for ( k=0; k<cId.length; k++ ) {
                for ( i=0; i<multiSelectList.length; i++ ){
                    if ( multiSelectList[i].selected == true ){
                        document.getElementById( "field_"+cId[k]+"_"+fieldName ).options[i].selected = true ;
                    } else {
                        document.getElementById( "field_"+cId[k]+"_"+fieldName ).options[i].selected = false ;
                    }
                }
            }
        } else if ( document.getElementById(source).getAttribute("class") == "tinymce" ) {
            if ( editor == "tinymce" ) {
                for ( k=0; k<cId.length; k++ ) {
                    cj( '#field_' + cId[k] + '_' + fieldName ).html( cj('#'+ source).html( ) );
                }
            }
        } else {
            for ( k=0; k<cId.length; k++ ) {
                document.getElementById("field_"+cId[k]+"_"+fieldName).value = document.getElementById(source).value;
            }
        }
    } else if ( document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]") &&
            document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length > 0 ) {
        if ( document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[0].type == "radio" ) {
            for ( t=0; t<document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length; t++ ) {
                if  (document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[t].checked == true ) {break}
            }
            if ( t == document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length ) {
                for ( k=0; k<cId.length; k++ ) {
                    for ( t=0; t<document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length; t++ ) {
                        document.getElementsByName("field"+"["+cId[k]+"]"+"["+fieldName+"]")[t].checked = false;
                    }
                }
            } else {
                for ( k=0; k<cId.length; k++ ) {
                    document.getElementsByName("field"+"["+cId[k]+"]"+"["+fieldName+"]")[t].checked = document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[t].checked;
                }
            }
        } else if ( document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[0].type == "checkbox" ) {
            for ( k=0; k<cId.length; k++ ) {
                document.getElementsByName("field"+"["+cId[k]+"]"+"["+fieldName+"]")[0].checked = document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[0].checked;
            }
        } else if ( document.getElementById( "field"+"["+cId[0]+"]"+"["+fieldName+"]___Frame" ) ) {
            //currently FckEditor field is mapped accross ___frames. It can also be mapped accross ___config.
            if ( editor == "fckeditor" ) {
                fckEditor = FCKeditorAPI.GetInstance( "field"+"["+cId[0]+"]"+"["+fieldName+"]" );
                for ( k=0; k<cId.length; k++ ) {
                    FCKeditorAPI.GetInstance( "field"+"["+cId[k]+"]"+"["+fieldName+"]" ).SetHTML( fckEditor.GetHTML() );
                }
            }
        }
    } else {
        if ( f = document.getElementById('Batch') ) {
            if ( ts = f.getElementsByTagName('table') ) {
                if ( t = ts[0] ) {
                    tRows = t.getElementsByTagName('tr') ;
                    if ( tRows[1] ) {
                        secondRow = tRows[1] ;
                        inputs = secondRow.getElementsByTagName('input') ;
                        for ( ii = 0 ; ii<inputs.length ; ii++ ) {
                            pattern = 'field['+cId[0]+']['+fieldName+']';
                            if ( inputs[ii].name.search(pattern) && inputs[ii].type == 'checkbox' ) {
                                for ( k=1; k<cId.length; k++ ) {
                                    target = document.getElementsByName(inputs[ii].name.replace('field['+cId[0]+']', 'field['+cId[k]+']')) ;
                                    if ( target.length > 0 ) {
                                        target[1].checked = inputs[ii].checked ;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


function copyValuesDate(fieldName)
{
    var cId = new Array();
    var i = 0;
    {/literal}
    {foreach from=$componentIds item=field}
        {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}
    var firstDate = cj( "#field_" + cId[0] + '_' + fieldName ).val( );
    var firstTime = null;
    if( cj( "#field_" + cId[0] + '_' + fieldName + '_time') ){
    	firstTime = cj( "#field_" + cId[0] + '_' + fieldName + '_time' ).val( );
    }
 	cj(cId).each(function(i,id){
 		cj( '#field_' + id + '_' + fieldName ).val( firstDate );
		cj( '#field_' + id + '_' + fieldName + '_time').val( firstTime );
 	});    
}

</script>
{/literal}
