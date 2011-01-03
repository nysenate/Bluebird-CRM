{*
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
*}
{if $notConfigured} {* Case types not present. Component is not configured for use. *}
    {include file="CRM/Case/Page/ConfigureError.tpl"}
{else}
<tr id='case_search_form'>
  <td class="crm-case-common-form-block-case_type" width="25%"><label>{ts}Case Type{/ts}</label>
    <br />
      <div class="listing-box" style="width: auto; height: 120px">
       {foreach from=$form.case_type_id item="case_type_id_val"}
        <div class="{cycle values="odd-row,even-row"}">
                {$case_type_id_val.html}
        </div>
      {/foreach}
      </div><br />
  </td>
  
  <td class="crm-case-common-form-block-case_status_id" width="25%">
    {$form.case_status_id.label}<br /> 
    {$form.case_status_id.html}<br /><br />	
    {if $accessAllCases}
    {$form.case_owner.html} <span class="crm-clear-link">(<a href="javascript:unselectCaseRadio('case_owner', '{$form.formName}')">{ts}clear{/ts}</a>)</span><br />
    {/if}
    {if $form.case_deleted}	
        {$form.case_deleted.html}	
        {$form.case_deleted.label}	
    {/if}
  </td>
  {if $form.case_tags }
  <td class="crm-case-common-form-block-case_tags">
  <label>{ts}Case Tag(s){/ts}</label>
    <div id="Tag" class="listing-box">
      {foreach from=$form.case_tags item="tag_val"} 
        <div class="{cycle values="odd-row,even-row"}">
        	{$tag_val.html} 
        </div>
      {/foreach}
  </td>
{/if}
</tr>    
{literal}
<script type="text/javascript">
    var verifyCaseInput = new Array();
    cj( function() {
       
        var countCaseInputs = 1;
        cj("#case_search_form input,#case_search_form select").each(function () {
            cj(this).attr('case_pref', countCaseInputs);
            countCaseInputs++;
        });

        cj("#case_search_form input,#case_search_form select").each(function () {
        if (  cj(this).attr('case_pref') ) {
            switch( cj(this).attr('type') ) { 
          
                case 'checkbox':
                    var caseRef =  cj(this).attr('case_pref');
                    if( cj(this).attr('checked') ) {
      		            verifyCaseInput[caseRef] = 1;
    		        } else {
                        verifyCaseInput[caseRef] = 0;
                    } 

                    cj(this).click( function(){
                    if( cj(this).attr('checked') ) {
      		            verifyCaseInput[caseRef] = 1;
    		        } else {
 		                verifyCaseInput[caseRef] = 0;
                    }
                        alterCaseFilters( ); 
                    });
                    countCaseInputs++;
                break;

                case 'select-one':
                    var caseRef =  cj(this).attr('case_pref');
                    if ( cj(this).val( ) ) {
                        verifyCaseInput[caseRef] = 1;
                    } else {
                        verifyCaseInput[caseRef] = 0;
                    }
                    cj(this).change( function() {
                        if( cj(this).val() ) {
                            verifyCaseInput[caseRef] = 1;
                        } else {
                            verifyCaseInput[caseRef] = 0;  
                        }
                        alterCaseFilters( ); 
                    });
                    countCaseInputs++;
                break;    
            }
        }     
      });
    });

           
    function alterCaseFilters( ) {
        var isChecked = 0;
        cj("#case_search_form input[name=case_owner]").each( function( ) {
            if ( (cj(this).attr('type') == 'radio' && cj(this).attr('checked') ) ) {
                isChecked = 1;
            }    
        });
           
        if ( isChecked ) {
            return true;
        }

        if ( cj.inArray( 1, verifyCaseInput ) != -1 ) {
            cj("#case_search_form input[name=case_owner]").each( function( ) {
                if ( (cj(this).attr('type') == 'radio' && cj(this).val( ) == 1) ) {
                    cj(this).click();
                }    
            });
        }
    }
 
    function unselectCaseRadio( eleName, thisForm ) {
        if ( cj.inArray( 1, verifyCaseInput ) != -1 ) {
            alert( 'It is mandatory to select either Search All Cases or Only My Cases if any of the case serach criteria is selected' );
            return;
        }
        unselectRadio( eleName, thisForm);

    }

</script>      
{/literal} 
{/if}
 