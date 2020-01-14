CRM.$(function($) {
  $('.crm-mailing-approve-form-block-approval_status').before('<tr class="crm-mailing-approve-form-block-count"><td class="label"><label>Recipients</label></td><td>Estimated recipient count: ' + CRM.vars.NYSS.mailingCount + '<br /><a href="' + CRM.vars.NYSS.reviewUrl + '" class="crm-hover-button action-item crm-popup" crm-icon="fa-users"><i class="crm-i fa-users"></i> Preview Recipients</a></td></tr>');
});
