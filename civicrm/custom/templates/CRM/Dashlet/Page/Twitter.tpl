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

<div id="tweets"></div>

{literal}
<script>
	function crm_relative_time(time_value) {
    var parsed_date = Date.parse(time_value);
    var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
    var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
    var pluralize = function (singular, n) {
      return '' + n + ' ' + singular + (n == 1 ? '' : 's');
    };

    if(delta < 60) {
      return 'less than a minute ago';
    }
    else if(delta < (60*60)) {
      return 'about ' + pluralize("minute", parseInt(delta / 60)) + ' ago';
    }
    else if(delta < (24*60*60)) {
      return 'about ' + pluralize("hour", parseInt(delta / 3600)) + ' ago';
    }
    else {
      return 'about ' + pluralize("day", parseInt(delta / 86400)) + ' ago';
    }
  };
	
	function crm_add_twitter_links(tweet){
    var user_links = /[\@]+([A-Za-z0-9-_]+)/gi;
    var hash_links = / [\#]+([A-Za-z0-9-_]+)/gi;
    var url_links = /((ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/gi;

		linked_tweet = tweet.replace(url_links,"<a href=\"$1\">$1</a>");
		linked_tweet = linked_tweet.replace(user_links,"<a href=\"http://twitter.com/$1\">@$1</a>");
		linked_tweet = linked_tweet.replace(hash_links,"<a href=\"http://search.twitter.com/search?q=$1\">#$1</a>");
        
    return linked_tweet;
	};
	
	$(document).ready(function() {
	$.getJSON("https://api.twitter.com/1.1/statuses/user_timeline.json?count=6&screen_name=NYSenate&callback=?",
    function(data){
      $.each(data, function(i,item){
        $("#tweets").append('<div class="tweet">'+ crm_add_twitter_links(item.text) +'</div>');

        datesplit = item.created_at.split('+');
        date = datesplit[0];

        $("#tweets").append('<div class="created-date">'+ crm_relative_time(item.created_at) +'</div>');
      });
	  });
	});
	
</script>
{/literal}
