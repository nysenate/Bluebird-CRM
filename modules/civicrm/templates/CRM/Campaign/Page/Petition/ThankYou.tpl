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

{if $status_id eq 2} {* Signer needs to confirm signature. *}

<h2>STEP 2: Please Check Your Email</h2>
<p>

To complete and confirm your signature, please follow the activation instructions sent to the email address you provided.</p>
<p>

<strong>IMPORTANT</strong>: Before we can add your signature, you must validate your email address by clicking on the activation link in the confirmation e-mail. Sometimes our confirmation emails get flagged as spam and are moved to your bulk folder.<br/>
If you haven't received an email within a few minutes, please check your spam folder.
</p>

{/if}

{if $status_id eq 4}
<p>You have already signed this petition but we <strong>need to confirm your email address</strong>.</p>
<b>IMPORTANT</b>: Before we can add your signature, you must validate your email address by clicking on the activation link in the confirmation e-mail. Sometimes our confirmation emails get flagged as spam and are moved to your spam folder.<br/>
If you haven't received an email from us, check your spam folder, it might have been wrongly classified.<br/>
{/if}
{if $status_id eq 5}
<p>You have already signed this petition.</p>
{/if}

{if $status_id neq 2}{* if asked to confirm the email, focus on that and don't put additional messages *}
{include file="CRM/Campaign/Page/Petition/SocialNetwork.tpl" petition_id=$survey_id}
{/if}
