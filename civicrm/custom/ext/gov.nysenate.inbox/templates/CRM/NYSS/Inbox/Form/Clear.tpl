{* HEADER *}
<h3>Clear this message from Bluebird?</h3>
<div class="description">This message will be cleared from the message list. It will not be deleted from Bluebird.</div>
<p></p>

<table>
  <tr>
    <th>Sender</th>
    <th>Email</th>
    <th>Subject</th>
    <th>Date Updated</th>
    <th>Forwarded By</th>
  </tr>
  <tr id="message-{$details.id}">
    <td>{$details.sender_name}</td>
    <td>{$details.sender_email}</td>
    <td>{$details.subject}</td>
    <td>{$details.updated_date}</td>
    <td>{$details.forwarded_by}</td>
  </tr>
</table>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
