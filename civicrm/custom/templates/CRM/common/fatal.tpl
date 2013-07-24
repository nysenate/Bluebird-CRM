{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{* error.tpl: Display page for fatal errors. Provides complete HTML doc.*}
{if $config->userFramework != 'Joomla' and $config->userFramework != 'WordPress'}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
  <title>{$pageTitle}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <base href="{$config->resourceBase}" />
  <style type="text/css" media="screen">@import url({$config->resourceBase}css/civicrm.css);</style>
  <style type="text/css" media="screen">@import url({$config->resourceBase}css/extras.css);</style>
  {literal}
  <style media="screen" type="text/css">
    body {
      background: url("/sites/default/themes/Bluebird/nyss_skin/images/bluebird_back.jpg") repeat-x scroll left top #7FCDFE;
    }
    div#crm-container {
      padding: 25px;
    }
    p, ul, li, div {
      font-family: helvetica, arial;
      line-height: 150%;
    }
    #crm-container div.status {
      padding: 15px 20px;
      box-shadow: 3px 3px 10px #333;
    }
    .large {
      font-size: 150%;
      color: #003366;
      margin: 10px 0;
    }
    .crm-accordion-header {
      font-size: 12px;
    }
    .crm-accordion-body {
      font-size: 11px;
    }
    .seal-img {
      padding-right: 15px;
    }
  </style>
  {/literal}
</head>
<body>
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
{else}
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
  <style type="text/css" media="screen">@import url({$config->resourceBase}css/civicrm.css);</style>
  <style type="text/css" media="screen">@import url({$config->resourceBase}css/extras.css);</style>
{/if}

{*NYSS*}
{if $config->debug}
<div class="messages status">  <div class="icon red-icon alert-icon"></div>
 <span class="status-fatal">{ts}Sorry but we are not able to provide this at the moment.{/ts}</span>
    <div class="crm-section crm-error-message">{$message}</div>
    {if $error.message && $message != $error.message}
        <hr style="solid 1px" />
        <div class="crm-section crm-error-message">{$error.message}</div>
    {/if}
    {if ($code OR $mysql_code OR $errorDetails) AND $config->debug}
        <div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-fatal-error-details-block" onclick="toggle(this);";>
         <div class="crm-accordion-header">
          <div class="icon crm-accordion-pointer"></div>
        	{ts}Error Details{/ts}
         </div><!-- /.crm-accordion-header -->
         <div class="crm-accordion-body">
            {if $code}
                <div class="crm-section">{ts}Error Code:{/ts} {$code}</div>
            {/if}
            {if $mysql_code}
                <div class="crm-section">{ts}Database Error Code:{/ts} {$mysql_code}</div>
            {/if}
            {if $errorDetails}
                <div class="crm-section">{ts}Additional Details:{/ts} {$errorDetails}</div>
            {/if}
         </div><!-- /.crm-accordion-body -->
        </div><!-- /.crm-accordion-wrapper -->
    {/if}
    <p><a href="{$config->userFrameworkBaseURL}" title="{ts}Main Menu{/ts}">{ts}Return to home page.{/ts}</a></p>
</div>
{else}
  <div class="messages status">
<p class="large"><img src="/sites/default/themes/Bluebird/nyss_skin/images/seal-bluebird.png" alt="Seal" align="right" class="seal-img">We've encountered a problem returning your requested page.</p>
    <p>This may happen for a number of reasons:
      <ul>
        <li>Did you leave your computer for some time with the browser left open, then attempt to conduct a search? If so, your user session (the software's short term memory) may have expired. Please return to the Bluebird homepage and try your search again.</li>
        <li>Did you try conducting a search and then save it as a bookmark in your browser? Sorry &mdash; browsers are more like fish than elephants &mdash; they have short memories. Try running your search and saving it as a smart group, which works as a saved search.</li>
        <li>Were you working with contact records or performing an action that you are confident should not have caused a problem? Don't worry, detailed information about the issue has been already sent to the development team. Please call the STS Helpline, at 518-455-2011 and provide as much detail about what steps you took when the problem appeared. We will investigate and follow-up as required.</li>
      </ul>
    </p>
    <p class="large"><a href="{$config->userFrameworkBaseURL}" title="Bluebird Home">Click here</a> to return to Bluebird.</p>
    <div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-fatal-error-details-block" onclick="toggle(this);";>
      <div class="crm-accordion-header">
        <div class="icon crm-accordion-pointer"></div>
        {ts}Additional Details{/ts}
      </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body">
        {$message}
      </div><!-- /.crm-accordion-body -->
    </div><!-- /.crm-accordion-wrapper -->
  </div>
{/if}{*NYSS*}
</div> {* end crm-container div *}
{literal}
<script language="JavaScript">
function toggle( element ) {
    var className = element.className;
    if ( className  == 'crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-fatal-error-details-block') {
        element.className = 'crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-open crm-fatal-error-details-block';
    } else {
        element.className = 'crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-fatal-error-details-block';
    }
}
</script>
{/literal}
{if $config->userFramework != 'Joomla' and $config->userFramework != 'WordPress'}
</body>
</html>
{/if}
