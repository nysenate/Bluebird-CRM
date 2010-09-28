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

function buildCustomData( type, subType, subName, cgCount, groupID, isMultiple )
{
	var dataUrl = {/literal}"{crmURL p=$urlPath h=0 q='snippet=4&type='}"{literal} + type; 

	if ( subType ) {
		dataUrl = dataUrl + '&subType=' + subType;
	}

	if ( subName ) {
		dataUrl = dataUrl + '&subName=' + subName;
		cj('#customData' + subName ).show();
	} else {
		cj('#customData').show();		
	}
	
	{/literal}
		{if $urlPathVar}
			dataUrl = dataUrl + '&' + '{$urlPathVar}'
		{/if}
		{if $groupID}
			dataUrl = dataUrl + '&groupID=' + '{$groupID}'
		{/if}
		{if $qfKey}
			dataUrl = dataUrl + '&qfKey=' + '{$qfKey}'
		{/if}
		{if $entityID}
			dataUrl = dataUrl + '&entityID=' + '{$entityID}'
		{/if}
	{literal}

	if ( !cgCount ) {
		cgCount = 1;
		var prevCount = 1;		
	} else if ( cgCount >= 1 ) {
		var prevCount = cgCount;	
		cgCount++;
	}

	dataUrl = dataUrl + '&cgcount=' + cgCount;


	if ( isMultiple ) {
		var fname = '#custom_group_' + groupID + '_' + prevCount;
		cj("#add-more-link-"+prevCount).hide();
	} else {
		if ( subName && subName != 'null' ) {		
			var fname = '#customData' + subName ;
		} else {
			var fname = '#customData';
		}		
	}
	
	var response = cj.ajax({
						url: dataUrl,
						async: false
					}).responseText;

	cj( fname ).html( response );
}

</script>
{/literal}
