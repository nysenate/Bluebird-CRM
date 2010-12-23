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
{* Confirmation of contribution deletes  *}
<div class="messages status">
  <div class="icon inform-icon"></div>
      {include file="CRM/Contribute/Form/Task.tpl"}
</div>
<div id="help">
    {ts}You may choose to Email receipts to Contributers OR Download a PDF file containing one receipt per page to your local computer by clicking <strong>Process Receipt(s)</strong>. Your browser may display the file for you automatically, or you may need to open it for printing using any PDF reader (such as Adobe&reg; Reader).{/ts}
</div>

  {$form.output.pdf_receipt.html}
  {$form.output.email_receipt.html}
<div class="spacer"></div>
<div class="form-item">
 {$form.buttons.html}
</div>
