<?php /* Smarty version 2.6.26, created on 2010-07-01 11:40:45
         compiled from CRM/Dashlet/Page/Twitter.tpl */ ?>

<div id="tweets">
	
</div>
<?php echo '
<script>
	function crm_relative_time(time_value) {
      var parsed_date = Date.parse(time_value);
      var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
      var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
      var pluralize = function (singular, n) {
        return \'\' + n + \' \' + singular + (n == 1 ? \'\' : \'s\');
      };
      if(delta < 60) {
      return \'less than a minute ago\';
      } else if(delta < (60*60)) {
      return \'about \' + pluralize("minute", parseInt(delta / 60)) + \' ago\';
      } else if(delta < (24*60*60)) {
      return \'about \' + pluralize("hour", parseInt(delta / 3600)) + \' ago\';
      } else {
      return \'about \' + pluralize("day", parseInt(delta / 86400)) + \' ago\';
      }
    };
	
	function crm_add_twitter_links(tweet){

        var user_links = /[\\@]+([A-Za-z0-9-_]+)/gi;
        var hash_links = / [\\#]+([A-Za-z0-9-_]+)/gi;
        var url_links = /((ftp|http|https):\\/\\/(\\w+:{0,1}\\w*@)?(\\S+)(:[0-9]+)?(\\/|\\/([\\w#!:.?+=&%@!\\-\\/]))?)/gi;

		linked_tweet = tweet.replace(url_links,"<a href=\\"$1\\">$1</a>");
		linked_tweet = linked_tweet.replace(user_links,"<a href=\\"http://twitter.com/$1\\">@$1</a>");
		linked_tweet = linked_tweet.replace(hash_links,"<a href=\\"http://search.twitter.com/search?q=$1\\">#$1</a>");
        
        return linked_tweet;
	};
	
	$(document).ready(function() {
	$.getJSON("http://twitter.com/statuses/user_timeline.json?count=6&screen_name=NYSenate&callback=?",
	function(data){
	$.each(data, function(i,item){
		$("#tweets").append(\'<div class="tweet">\'+ crm_add_twitter_links(item.text) +\'</div>\');
		
		datesplit = item.created_at.split(\'+\');
		date = datesplit[0];
		
		$("#tweets").append(\'<div class="created-date">\'+ crm_relative_time(item.created_at) +\'</div>\');
	});

	});
	});
	
</script>
'; ?>