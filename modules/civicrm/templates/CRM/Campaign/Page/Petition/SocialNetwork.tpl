{* 
Default Thank-you page for verified signers.
You might have a specific page that displays more information that the form.

Check SocialNetwork.drupal as an example
*}

{capture assign=petitionURL}{crmURL p='civicrm/petition/sign' q="sid=$petition_id" a=true}{/capture}
<h2>{ts}Help spread the word about our petition{/ts}</h2>
<div class="crm-section">
	<p>{ts}Please help us and let your friends, colleagues and followers know about our campaign.{/ts}</p>
</div>

<h3>{ts}Do you use Facebook or Twitter ?{/ts}</h3>
<div id="crm_socialnetwork" class="crm-section">
	<p>{ts}Share it on Facebook or tweet it on Twitter.{/ts}</p>
	<div class="crm_fb_tweet_buttons">
		<a href="http://www.facebook.com/sharer.php?u={$petitionURL}" id="crm_fbshare">
			<img src="{$config->userFrameworkResourceURL}/i/fbshare.png" width="70px" height="28px" alt="Facebook Share Button">
		</a>
		<a href="http://twitter.com/share?url={$petitionURL}&amp;text=Sign this, I did" id="crm_tweet">
			<img src="{$config->userFrameworkResourceURL}/i/tweet.png" width="55px" height="20px"  alt="Tweet Button">
		</a>
	</div>
 </div>

<h3>{ts}Do you have a website for your organisation or yourself?{/ts}</h3>
<div class="crm-section">
    {ts 1=$petitionURL}You can write a story about it - don't forget to add the link to <a href="%1">%1.</a>{/ts}
</div>


