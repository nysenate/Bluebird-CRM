{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
<style type="text/css">
/*<![CDATA[*/
  @import url({$config->resourceBase}/packages/jquery/css/clickmenu.css);
  /*]]>*/
{literal}
  #menu form {margin:0}
{/literal}

</style>
  <script type="text/javascript" src="{$config->resourceBase}packages/jquery/plugins/jquery.clickmenu.pack.js"> </script>
  <script type="text/javascript">
//<![CDATA[
   var contactViewUrl="{crmURL p='civicrm/contact/view?reset=1'}";
{literal}
  jQuery(document).ready(function($)  
  {  
     $('#menu').clickMenu(); 
			var contactUrl = {/literal}"{crmURL p='civicrm/ajax/rest?fnName=civicrm/contact/search&json=1&return[display_name]=1&return[contact_type]&return[email]&rowCount=25'}"{literal};

     $("#Qsearch").autocomplete( contactUrl, {
       dataType:"json",
       extraParams:{sort_name:function () { //extra % to force looking to the data typed anywhere in the name
	      return "%"+$("#Qsearch").val();}
       },
       formatMatch: function (data,i,max) { 
         data.display_name+ " " + data.email;
       }, 
       parse: function(data){ 
         //either an array of objects or {is_error":0} ???
         if ("is_error" in data) { return [{data:{contact_type:"Individual"},value:"create an individual"},
                                           {data:{contact_type:"Organization"},value:"create an Organization"},
                                           {data:{contact_type:"Household"},value:"create an Household"}
                                          ];};;
         var parsed = new Array();
         for(cid in data){  
           parsed.push({ data:data[cid], value:data[cid].sort_name, result:data[cid].sort_name });  
         }  
         return parsed;  
       },
       formatItem: function(data,i,max,value,term){ 
	  if ("email" in data) 
            email = " ("+ data["email"]+")";
          else
            email = ""; 
          return "<span class='"+data["contact_type"]+ "'>"+ value + email + "</span>";  
       }, 
       width: 500,
       delay:200,
       max:25,
       minChars:1,
       selectFirst: true,
       matchContains: true	
     }).result(function(event, data, formatted) {
         document.location= contactViewUrl+'&cid='+data["contact_id"];
     });
			
//seems to leak on the blocks ???     $('#searchType li').click (function (){alert ("the idea is to set the search type from here, ala firefox");return false}); 
  });
/*  
*/
  //]]>
  </script>
{/literal}

  <ul id="menu">

<li id="searchType">
<img  src="{$config->resourceBase}/i/contact_all.ico" alt =""/>
<ul>
<li><img  src="{$config->resourceBase}/i/contact_all.ico" alt =""/>All contacts</li>
<li><img  src="{$config->resourceBase}/i/contact_ind.gif" alt ="Search only individuals"/>Only individuals</li>
<li><img  src="/sites/all/modules/civicrm/i/contact_org.gif" alt ="Search only Organizations"/>Only organisations</li>
<li><img  src="/sites/all/modules/civicrm/i/contact_house.png" alt ="Search only Household"/>Only households</li>
<li>by email</li>
<li>by phone number</li>
</ul>
</li>
<li class="search">
<form action={crmURL p="civicrm/contact/search/basic"} method="post"><input type="hidden" name="reset" value="1" />
<input type="hidden" name="_qf_Basic_refresh" value="WTF" />
<input id="Qsearch" name="sort_name"/>
</form>
</li>
</li>
    <li>Contacts

      <ul>
        <li>Create new...

          <ul>
            <li>Using profile
             <ul>
               <li><a href={crmURL p="civicrm/profile/create" q="reset=1&gid=1"}>Profile A</a></li>
               <li>Profile B</li>
               <li>Profile C</li>
             </ul>
            </li>

            <li><a href={crmURL p="civicrm/contact/add" q='reset=1&ct=Individual'}>Individual</a></li>
            <li><a href={crmURL p="civicrm/contact/add" q='reset=1&ct=Household'}>Household</a></li>
            <li><a href={crmURL p="civicrm/contact/add" q='reset=1&ct=organization'}>Organisation</a></li>
          </ul>
        </li>

        <li>Import...

          <ul>
            <li><a href={crmURL p="civicrm/import" q="reset=1"}>Individual</a></li>
            <li>Household</li>
            <li>Organisation</li>
          </ul>
        </li>

        <li>Search...

          <ul>
            <li><a href={crmURL p="civicrm/contact/search" q="reset=1"}>Standard</a></li>
            <li><a href={crmURL p="civicrm/contact/search/advanced" q="reset=1"}>Advanced</a></li>
            <li><a href={crmURL p="civicrm/contact/search/custom" q="reset=1&csid=6"}>By proximity</a></li>
            <li><a href={crmURL p="civicrm/contact/search/custom" q="reset=1&csid=11"}>By creation date</a></li>
            <li>...</li>
          </ul>
        </li>
      </ul>
    </li>

    <li>Activities
      <ul>
        <li>My activities
          <ul>
            <li>Assigned to me</li>
            <li>Created by me</li>
          </ul>
        <li>Create new...
          <ul>
            <li>Mail</li>
            <li>Meeting</li>
            <li>...</li>
          </ul>
        </li>
            <li><a href={crmURL p="civicrm/contact/search/custom" q="reset=1&csid=8"}>Search</a></li>
      </ul>
    </li>
    <li class="groups">Groups &amp; Tags

      <ul>
        <li>Create new...

          <ul>
            <li><a href={crmURL p="civicrm/group/add" q="reset=1"}>Group</a></li>
            <li><a href={crmURL p="civicrm/admin/tag" q="action=add&reset=1"}>Tag</a></li>
            <li>Tag</li>
          </ul>
        </li>

        <li>Groups

          <ul>
            <li><a href={crmURL p="civicrm/group" q="reset=1"}>All groups</a></li>
            <li>Main group 1</li>

            <li>Main group 2</li>

            <li>Main group 3</li>

          </ul>
        </li>

        <li>Tags

          <ul>
            <li><a href={crmURL p="civicrm/admin/tag" q="reset=1"}>All tags</a></li>
            <li>Main tag 1</li>

            <li>Main tag 2</li>

            <li>Main tag 3</li>

          </ul>
        </li>
      </ul>
    </li>

    <li class="civievent">
      <a href={crmURL p="civicrm/event" q="reset=1"}>CiviEvent</a>

      <ul>
        <li><a href="{crmURL p="civicrm/event/search" q="reset=1"}">Find Participants</a></li>

        <li><a class="active" href="{crmURL p="civicrm/event/manage" q="reset=1"}">Manage Events</a></li>

        <li><a href="{crmURL p="civicrm/event/add" q="action=add&reset=1"}">New Event</a></li>

        <li><a href="{crmURL p="civicrm/event/import" q="reset=1"}">Import Participants</a></li>

        <li><a href="{crmURL p="civicrm/event/price" q="reset=1"}">Manage Price Sets</a></li>
      </ul>
    </li>

    <li class="civimail">CiviMail

      <ul>
        <li>Manage

          <ul>
            <li>Next Event 1</li>

            <li>Next Event 2</li>

            <li>all Events</li>
          </ul>
        </li>

        <li>Create new</li>

        <li>Participants</li>
      </ul>
    </li>

    <li><a href="{crmURL p="civicrm/admin" q="reset=1"}">Configuration</a></li>
  </ul>
