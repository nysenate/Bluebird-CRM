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
<div id="menu-container" style="display:none;">
    <ul id="civicrm-menu">
        {if call_user_func(array('CRM_Core_Permission','giveMeAllACLs'))}
        <li id="crm-qsearch" class="menumain">
            <form action="{crmURL p='civicrm/contact/search/basic' h=0 }" name="search_block" id="id_search_block" method="post" onsubmit="getSearchURLValue( );">
            	<div>
                <input type="text" class="form-text" id="sort_name_navigation" name="sort_name" style="width: 12em;"/>
                <input type="hidden" id="sort_contact_id" value="" />
                <input type="submit" value="{ts}Go{/ts}" name="_qf_Basic_refresh" class="form-submit default" style="display: none;"/>
            	</div>
            </form>
        </li>
	{/if}
        {$navigation}
    </ul>
</div>

{literal}
<script type="text/javascript">
function getSearchURLValue( )
{
    var contactId =  cj( '#sort_contact_id' ).val();
    if ( ! contactId || isNaN( contactId ) ) {
        var sortValue = cj( '#sort_name_navigation' ).val();
        if ( sortValue ) { 
            //using xmlhttprequest check if there is only one contact and redirect to view page
            var dataUrl = {/literal}"{crmURL p='civicrm/ajax/contact' h=0 q='name='}"{literal} + sortValue;

            var response = cj.ajax({
                url: dataUrl,
                async: false
                }).responseText;

            contactId = response;
        }
    }
    
    if ( contactId ) {
        var url = {/literal}"{crmURL p='civicrm/contact/view' h=0 q='reset=1&cid='}"{literal} + contactId;
        document.getElementById('id_search_block').action = url;
    }
}

/* Need to fix this properly*/
cj( function() {
    cj("#admin-menu").find("li :contains('CiviCRM')").click(function() {
        cj("#civicrm-menu").toggle();
        return false;
    });

    var contactUrl = {/literal}"{crmURL p='civicrm/ajax/rest' q='className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=navigation' h=0 }"{literal};

    cj( '#sort_name_navigation' ).autocomplete( contactUrl, {
        width: 200,
        selectFirst: false,
        minChars:1,
        matchContains: true 	 
    }).result(function(event, data, formatted) {
       document.location={/literal}"{crmURL p='civicrm/contact/view' h=0 q='reset=1&cid='}"{literal}+data[1];
       return false;
    });    
});

var framework = "{/literal}{$config->userFramework}{literal}";
if( framework != 'Joomla') {
	cj('body').prepend( cj("#menu-container").html() );

	//Track Scrolling
	cj(window).scroll( function () { 
	   var scroll = document.documentElement.scrollTop || document.body.scrollTop;
	   cj('#civicrm-menu').css({top: "scroll", position: "fixed", top: "0px"}); 
	   cj('div.sticky-header').css({ 'top' : "23px", position: "fixed" });
	});
} else {
	   cj('div#toolbar-box div.m').html(cj("#menu-container").html());
	   cj('#civicrm-menu').ready( function(){ 
			cj('.outerbox').css({ 'margin-top': '6px'});
			cj('#root-menu-div .menu-ul li').css({ 'padding-bottom' : '2px', 'margin-top' : '2px' });
			cj('img.menu-item-arrow').css({ 'top' : '4px' }); 
		});
}
	var resourceBase   = {/literal}"{$config->resourceBase}"{literal};
	cj('#civicrm-menu').menu( {arrowSrc: resourceBase + 'packages/jquery/css/images/arrow.png'} );
</script>
{/literal}
