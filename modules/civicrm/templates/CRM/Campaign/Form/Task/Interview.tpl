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

{if $votingTab and $errorMessages}
  <div class='messages status'>
     <div class="icon inform-icon"></div>
        <ul>
	   {foreach from=$errorMessages item=errorMsg}	
             <li>{ts}{$errorMsg}{/ts}</li>
           {/foreach}
       </ul>
     </div>
  </div>

{elseif $voterDetails}
<div class="form-item">
<fieldset>

{if $surveyValues.instructions}
   <div id='survey_instructions' class='help'>{ts 1=$surveyValues.instructions}%1{/ts}</div>
{/if}

<div id='help'>
    {if $votingTab}
    {ts}Click <strong>record response</strong> button to update values for each respondent as needed.{/ts}
    {else}
    {ts}Click <strong>record response</strong> button to update values for each respondent as needed. <br />Click <strong>Release Respondents >></strong> button below to release any respondents for whon you haven't recorded a response. <br />Click <strong>Reserve More Respondents >></strong> button if you need to get more respondents to interview.{/ts}
    {/if}
</div>

<table id="voterRecords" class="display">
    <thead>
       <tr class="columnheader">
             {foreach from=$readOnlyFields item=fTitle key=fName}
	        <th {if $fName neq 'contact_type'} class="contact_details"{/if}>{$fTitle}</th>
	     {/foreach}
	    
	     {* display headers for profile survey fields *}
	     {if $surveyFields}
	     {foreach from=$surveyFields item=field key=fieldName}
                  <th>{if $field.data_type eq 'Date' } <img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=$field.title}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValuesDate('{$field.name}')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" /> {else} <img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=$fieldName}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValues('{$fieldName}')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{/if}{$field.title}</th>
             {/foreach}
	     {/if}

	     <th><img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=note}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValues('note')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{ts}Note{/ts}</th>
	     <th><img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=result}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValues('result')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{ts}Result{/ts}</th>
	     <th></th> 
       </tr>
    </thead>

    <tbody>
	{foreach from=$componentIds item=voterId}
	<tr id="row_{$voterId}" class="{cycle values="odd-row,even-row"}">
	    {foreach from=$readOnlyFields item=fTitle key=fName}
	       <td {if $fName neq 'contact_type'} class="name"{/if}>{$voterDetails.$voterId.$fName}</td>
	    {/foreach}

	    {* here build the survey profile fields *}
	    {if $surveyFields}
	    {foreach from=$surveyFields item=field key=fieldName}
		<td class="compressed">
                {if ( $field.data_type eq 'Date') or 
		    ( $fieldName eq 'thankyou_date' ) or ( $fieldName eq 'cancel_date' ) or ( $fieldName eq 'receipt_date' ) or (  $fieldName eq 'activity_date_time') }
                    {include file="CRM/common/jcalendar.tpl" elementName=$fieldName elementIndex=$voterId batchUpdate=1}
                {else}
                   {$form.field.$voterId.$fieldName.html}
                {/if}
		</td> 
            {/foreach}
	    {/if}
	    
	    <td class='note'>{$form.field.$voterId.note.html}</td>
	    <td class='result'>{$form.field.$voterId.result.html}</td>

	    <td>
		<a id = "interview_voter_button_{$voterId}" class='button' style="float:left;" href="#" title={ts}Vote{/ts} onClick="registerInterview( {$voterId} );return false;">
		{ts}record response{/ts}
		</a>
		{if $allowAjaxReleaseButton}
		   <a id="release_voter_button_{$voterId}" class='button'  href="#" title={ts}Release{/ts} onClick="releaseOrReserveVoter( {$voterId} );return false;">
		   {ts}release{/ts}
		   </a>
		{/if}
		<span id='restmsg_vote_{$voterId}' class="ok" style="display:none;float:right;">
		     {ts}Response Saved.{/ts}
		</span>
		
		<span id='restmsg_release_or_reserve_{$voterId}' class="ok" style="display:none;float:right;">
		  {ts}Released.{/ts}
		</span>	
	    </td>

	</tr>
	{/foreach}
    </tbody>
</table>

 {if !$votingTab}
 <div class="spacer"></div>
 <div class="crm-submit-buttons">{$form.buttons.html}</div>
 {/if}

</fieldset>
</div>


{literal}
<script type="text/javascript">
    var updateVote = "{/literal}{ts}Update Response{/ts}{literal}";	
    cj( function( ) {
        var count = 0; var columns=''; var sortColumn = '';
	
        cj('#voterRecords th').each( function( ) {
          if ( cj(this).attr('class') == 'contact_details' ) {
	    sortColumn += '[' + count + ', "asc" ],'; 
	    columns += '{"sClass": "contact_details"},';
	  } else {
	    columns += '{ "bSortable": false },';
	  }
	  count++; 
	});

	columns    = columns.substring(0, columns.length - 1 );
	sortColumn = sortColumn.substring(0, sortColumn.length - 1 );
	eval('sortColumn =[' + sortColumn + ']');
	eval('columns =[' + columns + ']');

	//load jQuery data table.
        cj('#voterRecords').dataTable( {
		"sPaginationType": "full_numbers",
		"aaSorting"  : sortColumn,
		"aoColumns"  : columns
        });        

    });

    function registerInterview( voterId )
    {
    	var data = new Object;
    	var fieldName = 'field_' + voterId + '_custom_';
	cj( '[id^="'+ fieldName +'"]' ).each( function( ) {
	    if( cj(this).attr( 'type' ) == 'select-multiple' ) {
	      var eleId = cj(this).attr('id');
	      cj('#' + eleId +" option").each( function(i) {
	        if ( cj(this).attr('selected') == true ) {
		  data[eleId + '['+cj(this).val()+']'] = cj(this).val();
		} 
	      });
	    } else {
	      data[cj(this).attr( 'id' )] = cj( this ).val( );
            }
        });
		
	var multiValueFields = 'field['+ voterId +'][custom_';		
	cj( '[id^="'+ multiValueFields +'"]' ).each( function( ) {
	   if ( cj(this).attr( 'type' ) == 'checkbox' ) {
	     if ( cj(this).attr('checked') == true ) {
	       data[cj(this).attr( 'id' )] = 1;
             } else {
	       data[cj(this).attr( 'id' )] = '';
	     }
           }
	   
        });
	
	var radioFields = 'field['+ voterId +'][custom_';		
	cj( '[name^="'+ radioFields +'"]' ).each( function( ) {
	   if ( cj(this).attr( 'type' ) == 'radio' ) {
               if ( cj(this).attr('checked') == true ) {
                  data[cj(this).attr( 'name' )] = cj(this).val();
               }
           }
        });
	
	var surveyActivityIds = {/literal}{$surveyActivityIds}{literal};
	activityId =  eval( "surveyActivityIds.activity_id_" + voterId );
	if ( !activityId ) return; 	

	data['voter_id']         = voterId;
	data['interviewer_id']   = {/literal}{$interviewerId}{literal};
	data['activity_type_id'] = {/literal}{$surveyTypeId}{literal};
	data['activity_id']      = activityId;
	data['result']           = cj( '#field_' + voterId + '_result' ).val( ); 
	data['note']             = cj( '#field_' + voterId + '_note' ).val( );
	data['surveyTitle']      = {/literal}'{$surveyValues.title|escape:javascript}'{literal};

	var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Campaign_Page_AJAX&fnName=registerInterview' }"{literal}	          
	
	//post data to create interview.
	cj.post( dataUrl, data, function( interview ) {
	       if ( interview.status == 'success' ) {
	       	 cj("#row_"+voterId+' td.name').attr('class', 'name disabled' );
		 cj( '#restmsg_vote_' + voterId ).fadeIn("slow").fadeOut("slow");
		 cj( '#interview_voter_button_' + voterId ).html(updateVote);
	       }		 
	}, 'json' );
    }
    
    function releaseOrReserveVoter( voterId ) 
    {
	if ( !voterId ) return; 

	var surveyActivityIds = {/literal}{$surveyActivityIds}{literal};
	activityId =  eval( "surveyActivityIds.activity_id_" + voterId );
	if ( !activityId ) return;
	
	var operation  = 'release';	
	var isReleaseOrReserve = cj( '#field_' + voterId + '_is_release_or_reserve' ).val( );
	if ( isReleaseOrReserve == 1 ) {
	     operation = 'reserve';
	     isReleaseOrReserve = 0;
	} else {
	     isReleaseOrReserve = 1;
	}

	var data = new Object;
	data['operation']   = operation;
	data['isDelete']    = ( operation == 'release' ) ? 1 : 0;
	data['activity_id'] = activityId; 

	var actUrl = {/literal}
	             "{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Campaign_Page_AJAX&fnName=processVoterData'}"
	             {literal};

        //post data to release / reserve voter.
        cj.post( actUrl, 
  	         data, 
	         function( response ) {
	    	      if ( response.status == 'success' ) {
			 if ( operation == 'release' ) {
			      cj( '#interview_voter_button_' + voterId ).hide( );
			      cj( '#restmsg_release_or_reserve' + voterId ).fadeIn( 'slow' ).fadeOut( 'slow' );
			      cj( '#row_' + voterId + ' td.name' ).addClass( 'disabled' );
			      cj( '#release_voter_button_'+ voterId ).html( "{/literal}{ts}reserve{/ts}{literal}"  );
			      cj( '#release_voter_button_' + voterId ).attr('title',"{/literal}{ts}Reserve{/ts}{literal}");
			  } else {
			      cj( '#interview_voter_button_' + voterId ).show( );
			      cj( '#restmsg_release_or_reserve' + voterId ).fadeIn( 'slow' ).fadeOut( 'slow' );
			      cj( '#row_' + voterId + ' td.name' ).removeClass( 'disabled' ); 
			      cj( '#release_voter_button_'+ voterId ).html( "{/literal}{ts}release{/ts}{literal}"  );
			      cj( '#release_voter_button_' + voterId ).attr('title',"{/literal}{ts}Release{/ts}{literal}");
			  }
		      	  cj( '#field_' + voterId + '_is_release_or_reserve' ).val( isReleaseOrReserve );  
		      }	     
	         }, 
		 'json' );		     
    }
    
</script>
{/literal}
{*include batch copy js js file*}
{include file="CRM/common/batchCopy.tpl"}
{/if}

