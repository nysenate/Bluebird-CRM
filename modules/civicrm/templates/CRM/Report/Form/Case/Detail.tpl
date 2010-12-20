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
{include file="CRM/Report/Form.tpl"}
<div id ="casedetails"></div>
{literal}
<script type="text/javascript">
function viewCase( caseId ,contactId ) {
   cj("#casedetails").dialog({
        title: "Case Details",
        modal: true,
        bgiframe: true,
        width : 700,
	height: 400,
        overlay: { 
                   opacity: 0.5, 
            	   background: "black" 
               },  
        open:function() {
 	    var dataUrl = {/literal}"{crmURL p='civicrm/case/ajax/details' h=0 q="snippet=4" }"{literal};
	    dataUrl     = dataUrl + '&caseId=' +caseId + '&contactId=' +contactId ;
		cj.ajax({
                         url     : dataUrl,
                         dataType: "html",
                         timeout : 5000, //Time in milliseconds
                         success : function( data ){
                             cj( "#casedetails").html( data );
                       },
                   });
	    var dataUrl = {/literal}"{crmURL p='civicrm/case/details' h=0 q="snippet=4" }"{literal};
            dataUrl     = dataUrl + '&caseId=' +caseId + '&contactId=' +contactId ;
	        cj.ajax({
                         url     : dataUrl,
                         dataType: "html",
                         timeout : 5000, //Time in milliseconds
                         success : function( data ){
                             cj( "#casedetails").append(data);

                       },
                   });  

		},

                     buttons: { "Done": function() { cj(this).dialog("destroy"); }}
    });
}
 </script>{/literal}