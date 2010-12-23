/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
*/
/*
* Copyright (C) 2009-2010 Xavier Dutoit
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*/

/*
TO BE VERIFIED
If you do not use clean urls on drupal, you have to define a variable to set the url of the server to be used for the rest
<script type="text/javascript">
var options {ajaxURL:"{$config->userFrameworkResourceURL}";
</script>


*/

(function($){
      var defaults = {
    	  success: function(result,settings){
    	      var successMsg = 'Saved &nbsp; <a href="#" id="closerestmsg">'+ settings.closetxt +'</a>'; 
    	      $(settings.msgbox).addClass('msgok').html( successMsg ).show();
    	      $("#closerestmsg").click(function(){$(settings.msgbox).fadeOut("slow");return false;});
    	      return true;
    	  },
    	  callBack: function(result,settings){
    	      if (result.is_error == 1) {
    		  $(settings.msgbox).addClass('msgnok').html(result.error_message);
    		  return false;
    	      }
    	      return settings.success(result,settings);
    	  },
    	  closetxt: "<div class='icon close-icon' title='Close'>[X]</div>",
    	  ajaxURL: 'civicrm/ajax/rest',
    	  msgbox: '#restmsg'
      };

      $.fn.crmAPI = function(entity,action,params,options) {
    	  params ['fnName'] = "civicrm/"+entity+"/"+action;
    	  params ['json'] = 1;
    	  var settings = $.extend({}, defaults, options);
    	  $(settings.msgbox).removeClass('msgok').removeClass('msgnok').html("");
    	  $.getJSON(settings.ajaxURL,params,function(result){return settings.callBack(result,settings);});
      };

      $.fn.crmAutocomplete = function (options) {
	  var defaultsContact = {
	        returnParam: ['sort_name','email'],
	        params: {
	            rowCount:35,
		        json:1,
		        fnName:'civicrm/contact/search'
		    }
	  };
	  
	  settings = $.extend(true,{},defaultsContact, options);
	  
	  var contactUrl = defaults.ajaxURL + "?";
	  // How to loop on all the attributes ??
	  for  (param in settings.params) {
	      contactUrl = contactUrl + param +"="+ settings.params[param] + "&"; 
	  }
	  
	  //    contactUrl = contactUrl + "fnName=civicrm/contact/search&json=1&";
	  for (var i=0; i < settings.returnParam.length; i++) {
	      contactUrl = contactUrl + 'return['+settings.returnParam[i] + "]&"; 
	  }
	  
	  //var contactUrl = "/civicrm/ajax/rest?fnName=civicrm/contact/search&json=1&return[sort_name]=1&return[email]&rowCount=25";
	  
	  return this.each(function() {
		  var selector = this;
		  if (typeof $.fn.autocomplete != 'function') 
		      $.fn.autocomplete = cj.fn.autocomplete;//to work around the fubar cj
		      $(this).autocomplete( contactUrl, {
    			  dataType:"json",
    			      extraParams:{sort_name:function () {
    				  return $(selector).val();}//how to fetch the val ?
    			  },
    			  formatItem: function(data,i,max,value,term){
    			      if (data['email'])
    				    return value + ' ('+ data['email'] + ")";
    			      else 
    				    return value;
    			  },    			
    			  parse: function(data){
    			     var acd = new Array();
    			     for(cid in data){
    				     acd.push({ data:data[cid], value:data[cid].sort_name, result:data[cid].sort_name });
    			     }
    			     return acd;
    			  },
    			  
    			  width: 250,
    			  delay:100,
    			  max:25,
    			  minChars:0,
    			  selectFirst: true
    		 });
       });
     }

})(jQuery);

/* Depreciated as of 3.2. kept for backward compatibility reason. */
function civiREST (entity,action,params,close) {
    var options = null;
    if( close ){
	    options = {closetxt : close}; 
    }
    if ( typeof close == "function"){
	    options = {success : close}; 
    }
  
    cj.fn.crmAPI(entity,action,params,options);
}
