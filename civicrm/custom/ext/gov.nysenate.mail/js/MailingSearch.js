CRM.$(function($) {
  // NYSS 17220 encourage use of mailing templates
  $('a[title="Copy Mailing"]').click(function(e) {
    e.preventDefault();
    var copyLink = this;
    CRM.confirm({message:ts("Using a <em>Mailing Template</em> is the recommended way to reuse layout and boilerplate content.<br/>Copying a mailing is not recommended unless you're sending almost the exact same message again.<p>Would you like to use a Mailing Template instead?</p>"),
                 options: {yes: ts("Yes, Use Mailing Template"), no: ts("No, Copy It")}})
      .on('crmConfirm:yes', function() {
        window.location.href = '/civicrm/mosaico-template-list';
      })
      .on('crmConfirm:no', function() {
        window.location.href = copyLink.href;
      });
  });
});