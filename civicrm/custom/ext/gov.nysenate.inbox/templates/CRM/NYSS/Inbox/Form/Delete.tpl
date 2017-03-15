{* HEADER *}
<h3>Remove this message from Bluebird?</h3>
<div class="description">This message will be removed permanently. Are you sure?</div>
<p></p>

<table>
  <tr>
    <th>Sender</th>
    <th>Email</th>
    <th>Subject</th>
    <th>Date Forwarded</th>
    <th>Forwarded By</th>
  </tr>
  <tr id="message-{$details.id}">
    <td>{$details.sender_name}</td>
    <td>{$details.sender_email}</td>
    <td>{$details.subject}</td>
    <td>{$details.date_forwarded}</td>
    <td>{$details.forwarded_by}</td>
  </tr>
</table>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
