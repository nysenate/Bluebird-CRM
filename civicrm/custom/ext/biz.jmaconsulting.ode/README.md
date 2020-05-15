biz.jmaconsulting.ode
=====================

Outbound Domain Enforcement for CiviCRM
---------------------------------------

This extension is designed to preserve the email reputation of your server and its IP by ensuring that all outbound 
email is sent from an address with the same domain as the site which CiviCRM is hosted. (e.g. A site hosted on http://www.outbound-ode.com will permit sending emails only from addresses like user@outbound-ode.com. Emails such as user@gmail.com will be suppressed).

The need for this extension
---------------------------

It is fairly safe to assume that whoever initially configures CiviCRM will set up a From Email Address in the 
System-generated Mail Settings that is not barred from sending from CiviCRM's server. However, staff users authorized to
send mail may inadvertently have a different domain used in their primary email and not realize using it will create
problems. Similarly, they may create and use another From Email Address. 

The details
-----------

By default, CiviCRM uses the primary email address of a logged in user when sending an email, which may be configured to
a different domain than the From Email Address just mentioned. It also allows From email addresses with other domains to 
be configured at Administer > Communications > From Email Addresses 
(civicrm/admin/options/from_email_address?group=from_email_address&reset=1), and at Administer > CiviMail > 
From Email Addresses (civicrm/admin/options/from_email_address?reset=1).

If the server running CiviCRM is not authorized to send mail on behalf of a From address, perhaps because of a very 
tight SPF policy, it can lead to mail not being delivered and the server, the domain and / or its IP being blacklisted as a spammer. 

This extension filters the From Email Address options provided to users as they are about to send an email. Only ones 
that have the same domain as the domain under which the site is currently hosted are available, with the others 
suppressed.

When a From Email option is suppressed, the following message is displayed to users: 'The Outbound Domain Enforcement 
extension has prevented the following From Email Address option(s) from being used as it uses a different domain than 
the current active domain and Contact Info: email.with@different.domain.org, email.with@different.domain2.org.'

NB: Here are the forms that allow users to select From emails that will be filtered and/or validated:
*  The 3rd step of the CiviMail wizard
*  Individual emails being sent from Contact Summary page, Actions > Send email.
*  Individual emails being sent from Contact Summary page Activities tab, and for new activity select Send an email. 
*  Search results page, after selecting contacts with emails, changing action to Send Email to Contacts and clicking Go.
*  Validate From Address fields on create or updates of the Manage Contribution Pages, Thank you and receipting tab.
*  Validate From Address fields on Manage Events > Online Registration tab.
*  If the Grant Application extension is installed, then the From Address fields on the Receipting tab.
*  From Address field on Add/Edit Schedule Reminder.
*  If Send Receipt is selected on Batch Entry for Contribution & Batch Entry for Membership.   
*  When notification emails are selected for Profiles.
*  When TO and CC emails are selected in email delivery settings.
*  The Organization Address and Contact Info form
*  Various backend component creations. registrations, signups etc.

In case you have configured your server's SPF policy to allow certain email addresses from other domains, under Administer > Communications > From Email Addresses (civicrm/admin/options/from_email_address?group=from_email_address&reset=1), you can navigate to Administer > Communications > ODE Settings and whitelist them. This will also add the organization email address at civicrm/admin/domain?action=update&reset=1 to the whitelist.


Installation
============

1. As part of your general CiviCRM installation, you should set a CiviCRM Extensions Directory at Administer >> System Settings >> Directories.
2. As part of your general CiviCRM installation, you should set an Extension Resource URL at Administer >> System Settings >> Resource URLs.
3. Navigate to Administer >> System Settings >> Manage Extensions.
4. Beside Outbound Domain Enforcement, click Install.
