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
{* Advanced Search Criteria Fieldset *}
{literal}
<script type="text/javascript">
// bind first click of accordion header to load crm-accordion-body with snippet
// everything else taken care of by cj().crm-accordions()
cj(document).ready( function() {
    cj('.crm-ajax-accordion .crm-accordion-header').one('click', function() { 
    	loadPanes(cj(this).attr('id')); 
    	});
    cj('.crm-ajax-accordion.crm-accordion-open .crm-accordion-header').each(function(index) { 
    	loadPanes(cj(this).attr('id')); 
    	});
});
// load panes function calls for snippet based on id of crm-accordion-header
function loadPanes( id ) {
    var url = "{/literal}{crmURL p='civicrm/contact/search/advanced' q="snippet=1&qfKey=`$qfKey`&searchPane=" h=0}{literal}" + id;
   if ( ! cj('form#Advanced div.'+id).html() ) {
	    var loading = '<div class="crm-loading-element"><span class="loading-text">{/literal}{ts}Loading{/ts}{literal}...</span></div>';
	    cj('form#Advanced div.'+id).html(loading);
	    cj.ajax({
	        url    : url,
	        success: function(data) { cj('form#Advanced div.'+id).html(data); }
	        });
    	}
	}
</script>
{/literal}
		
		{if $context EQ 'smog' || $context EQ 'amtg' || $savedSearch}
        	<h3>
        	{if $context EQ 'smog'}{ts}Find Contacts within this Group{/ts}
        	{elseif $context EQ 'amtg'}{ts}Find Contacts to Add to this Group{/ts}
        	{elseif $savedSearch}{ts 1=$savedSearch.name}%1 Smart Group Criteria{/ts} &nbsp; {help id='id-advanced-smart'}
        	{/if}
        	</h3>
        {/if}

{strip}
<div class="crm-accordion-wrapper crm-search_criteria_basic-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  {ts}Basic Criteria{/ts} 
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
        {include file="CRM/Contact/Form/Search/Criteria/Basic.tpl"}
    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
    {foreach from=$allPanes key=paneName item=paneValue}
    {if $paneValue.id eq 'location'}
      <div class="crm-accordion-wrapper crm-ajax-accordion crm-{$paneValue.id}-accordion crm-accordion-open">
       <div class="crm-accordion-header" id="{$paneValue.id}">
       	<div class="icon crm-accordion-pointer"></div>
       	{$paneName}
       </div>
       <div class="crm-accordion-body {$paneValue.id}"></div>
       </div>
    {/if}
    {/foreach}
    
    
    {foreach from=$allPanes key=paneName item=paneValue}
    {if $paneValue.id neq 'location'}
      <div class="crm-accordion-wrapper crm-ajax-accordion crm-{$paneValue.id}-accordion {if $paneValue.open eq 'true'}crm-accordion-open{else}crm-accordion-closed{/if}">
       <div class="crm-accordion-header" id="{$paneValue.id}">
       	<div class="icon crm-accordion-pointer"></div>
       	{$paneName}
       </div>
       <div class="crm-accordion-body {$paneValue.id}"></div>
       </div>
    {/if}
    {/foreach}
    <div class="spacer"></div>

    <table class="form-layout">
        <tr>
            <td>{$form.buttons.html}<a href="/civicrm/contact/search/advanced&reset=1" class="resetbutton"><span>Reset Form</span></a></td>
        </tr>
    </table>
{/strip}

{literal}
<script>
cj( function() {
      var element_date   = "#custom_24_-1";var element_time  = "#custom_24_-1_time";var time_format   = cj( element_time ).attr('timeFormat');
              cj(element_time).timeEntry({ show24Hours : time_format, spinnerImage: '' });
          var currentYear = new Date().getFullYear();var date_format = cj( element_date ).attr('format');var alt_field   = 'input#custom_24_-1_hidden';var yearRange   = currentYear - parseInt( cj( element_date ).attr('startOffset') );yearRange  += ':';yearRange  += currentYear + parseInt( cj( element_date ).attr('endOffset'  ) );
 
      cj(element_date).datepicker({
                                    closeAtTop        : true, 
                                    dateFormat        : date_format,
                                    changeMonth       : true,
                                    changeYear        : true,
                                    altField          : alt_field,
                                    altFormat         : 'mm/dd/yy',
                                    yearRange         : yearRange
                                });
    
      cj(element_date).click( function( ) {
          hideYear( this );
      });  
      cj('.ui-datepicker-trigger').click( function( ) {
          hideYear( cj(this).prev() );
      });  
    });
    
    function hideYear( element ) {
        var format = cj( element ).attr('format');
        if ( format == 'dd-mm' || format == 'mm/dd' ) {
            cj(".ui-datepicker-year").css( 'display', 'none' );
        }
    }
    
    function clearDateTime( element ) {
        cj('input#' + element + ',input#' + element + '_time').val('');
    }

</script>
{/literal}

   