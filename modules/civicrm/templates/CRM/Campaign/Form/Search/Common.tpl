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
{* Search form and results for voters *}
  <div class="crm-block crm-form-block crm-search-form-block">
  
  {assign var='searchForm' value='searchForm'}
  {if $searchVoterFor}
  {assign var='searchForm' value="search_form_$searchVoterFor"}
  {/if}

  <div id="{$searchForm}" class="crm-accordion-wrapper crm-contribution_search_form-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
    <div class="icon crm-accordion-pointer"></div> 
        {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->

    <div class="crm-accordion-body">
    {strip} 
        <table class="form-layout">
	  <tr>
              <td class="font-size12pt">
                  {$form.campaign_survey_id.label}
              </td>
              <td>
	          {$form.campaign_survey_id.html}
              </td>

	      {if $showInterviewer}
	      <td class="font-size12pt">
	          {$form.survey_interviewer_name.label}
              </td>
              <td class="font-size12pt ">
	          {$form.survey_interviewer_name.html}
              </td>  
	      {/if}		    

	  </tr>
          <tr>
              <td class="font-size12pt">
                  {$form.sort_name.label}
              </td>
              <td>			
		  {$form.sort_name.html|crmReplace:class:'twenty'}
              </td>

              <td class="font-size12pt">
                  {$form.street_address.label}
	      </td>
              <td>
                  {$form.street_address.html}
              </td>       
          </tr>
	  <tr>
	      <td class="font-size12pt">
                  {$form.street_name.label}
       	      </td>
              <td>	
                  {$form.street_name.html}
              </td>
              <td class="font-size12pt">
                  {$form.street_unit.label}
	      </td>
              <td>
                  {$form.street_unit.html}
              </td> 
	  </tr>	
          <tr>
              <td class="font-size12pt">
                  {$form.city.label}
              </td>
              <td>
                  {$form.city.html}
              </td>
	      <td class="font-size12pt">
                  {$form.street_number.label}
       	      </td>
              <td>	
                  {$form.street_number.html}
              </td>
	  </tr>
	  {if $customSearchFields.ward || $customSearchFields.precinct}
	  <tr>
	      {if $customSearchFields.ward}
	      {assign var='ward' value=$customSearchFields.ward}
              <td class="font-size12pt">
                  {$form.$ward.label}
              </td>
              <td>
                  {$form.$ward.html}
              </td>
	      {/if}

	      {if $customSearchFields.precinct}
	      {assign var='precinct' value=$customSearchFields.precinct}
	      <td class="font-size12pt">
                  {$form.$precinct.label}
              </td>
              <td>
                  {$form.$precinct.html}
              </td>
	      {/if}
	  </tr>
	  {/if}
          <tr>
             <td colspan="2">
             {if $context eq 'search'}    
	         {$form.buttons.html}
	     {else}
	         <a class="searchVoter button" style="float:left;" href="#" title={ts}Search{/ts} onClick="searchVoters( '{$qfKey}' );return false;">{ts}Search{/ts}</a>
	     {/if}	 
	     </td>
          </tr>
        </table>
    {/strip}

</div>
</div>
</div>
    
{literal}
<script type="text/javascript">

    {/literal}{if !$doNotReloadCRMAccordion}{literal}	
    cj(function() {
      cj().crmaccordions(); 
    });
    {/literal}{/if}{literal}

  //load interviewer autocomplete.
  var interviewerDataUrl = "{/literal}{$dataUrl}{literal}";
  var hintText = "{/literal}{ts}Type in a partial or complete name of an existing contact.{/ts}{literal}";
  cj( "#survey_interviewer_name" ).autocomplete( interviewerDataUrl, 
                                                 { width : 256, 
                                                   selectFirst : false, 
                                                   hintText: hintText, 
                                                   matchContains: true, 
                                                   minChars: 1
                                                  }
                                                 ).result( function( event, data, formatted ) { 
				                              cj( "#survey_interviewer_id" ).val( data[1] );
                                                         }).bind( 'click', function( ) { 
                                                              cj( "#survey_interviewer_id" ).val(''); 
                                                         });

</script>
{/literal}