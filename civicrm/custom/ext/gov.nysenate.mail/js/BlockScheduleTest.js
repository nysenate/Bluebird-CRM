CRM.$(function($) {
  $('#send-test').click(function() {
    var email = $('#schedule-test-email').val();

    if (email.trim()) {
      CRM.api3('Mailing', 'send_test', {
        "mailing_id": CRM.vars.NYSS.mailingId,
        "test_email": email
      }).then(function(result) {
        CRM.alert('Test email has been sent', 'Send Test Email', 'success');
      }, function(error) {
        CRM.alert('There was a problem sending the test email.', 'Send Test Email', 'error');
      });
    }
  });
});
