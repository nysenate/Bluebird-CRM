$Id: README.txt,v 1.4.4.2 2009/11/03 04:23:10 thehunmonkgroup Exp $

****************************************************
User Protect Module -- README

written by Chad Phillips: thehunmonkgroup at yahoo dot com
****************************************************

This module provides various editing protection for users. The protections can
be specific to a user, or applied to all users in a role. The following protections
are supported:

  -- username
  -- e-mail address
  -- password
  -- status changes
  -- roles
  -- deletion
  -- OpenID identities (both adding and deleting) 
  -- all edits (any accessed via user/X/edit)

When a protection is enabled for a specified user (or the protection is enabled
because the user belongs to a role that has the protection), it prevents the
editing operation in question that anyone might try to perform on the
user -- unless an administrator who is permitted to bypass the protection is
editing the specified user. The module will protect fields by disabling them at
user/X/edit.

User administrators may be configured to bypass specified protections, on
either a global or per-administrator basis.

These protections are valid both when trying to edit the user directly from
their user/X/edit page, or using the mass user editing operations.

The module also provides protection at the paths user/X/edit and user/X/delete,
should anyone try to visit those paths directly.

Note: this module is compatible with the RoleAssign module.

SETTINGS:
At administer -> user management -> userprotect, you'll find the settings for the
module. When the module is initially enabled, the default settings are such:

  -- User administrators bypass all protections.
  -- The root user specifically bypasses all protections.
  -- The anonymous user (uid 0) and root user (uid 1) are protected from all
     edits, deletion, and OpenID operations.
  -- All role protections are disabled.
  -- The 'change own e-mail', 'change own password', and 'change own openid'
     permissions are enabled for authenticated users in the userprotect section
     at administer -> user management -> access control.

This effectively amounts to no protections. It is suggested that you turn off
as many default administrator bypass settings as possible, and set bypass
settings for specific user administrators--this allows you to take advantage
of the status, roles, deletion, openid and edit protections in a meaningful
way. Because of the per-user bypass/protection settings for the anonymous and
root user, this will also begin protecting those users, without compromising
the root user's access to the entire site.

Important note: In order to protect a user from deletion (by visiting
user/X/delete directly) and/or OpenID edits (by visiting user/X/openid
directly), you must enable the 'delete' and/or 'openid' protection specifically.
Enabling 'all account edits' does not enable these protections!

Also note that this module only provides protection against actions via the
website interface--operations that a module takes directly are not protected!
This module should play well with other contributed modules, but there is no
guarantee that all protections will remain intact if you install modules outside
of the drupal core installation.

ADDING PROTECTIONS FOR A SINGLE USER:
This is done at administer -> user management -> userprotect -> protected users.
Any time a user is added for protection, they will initially receive the default
protections enabled at 
administer -> user management -> userprotect -> protection defaults.

ADDING PROTECTIONS FOR ROLES:
This is done at administer -> user management -> userprotect -> protected roles.
Be cautious about adding protections by role, or you can lock out users from
things unintentionally!

In particular, note the if you enable role protections for a specific role, and you
have no bypasses enabled, you've effectively locked out any role editing for
that role by anybody, unless you come back to the settings page and disable
the role protection!

ADDING ADMINISTRATOR BYPASS RULES:
One of the more powerful features of the module are the administrator bypass
settings. Any user that has been granted the 'administer users' permission can
be configured to bypass any protection, either via the default administrator
bypass settings at 
administer -> user management -> userprotect -> protection defaults, or via
a per-administrator setting at 
administer -> user management -> userprotect -> administrator bypass. If a
bypass is enabled for a user administrator, they will be given editing rights on
that protection regardless if it is enabled for a single user or an entire role.

Note that the per-administrator bypass settings override the default bypass
settings.

DEFAULT PROTECTION SETTINGS:
Set the default protections for newly protected users at
administer -> user management -> userprotect -> protection defaults. In
addition, you can enable the auto-protect feature, which will automatically
add the default protections to any newly created user accounts, and set default
bypass options for all user administrators.

HOW THE MODULE DETERMINES A PROTECTION:
In order to properly use User Protect, it's important to understand how the
module determines if a specified field is to be protected. Here is the basic
logic:

  -- If the current user is a user administrator, check if they have per-administrator
     bypass settings. If so, then check to see if they are allowed to bypass the
     protection. If so, then stop the checks and allow editing of the field.

  -- If not, then if the current user is a user administrator, check if the default
     administrator bypass is enabled for the protection in question. If so, then
     stop the checks and allow editing of the field.

  -- If not, check if the user is editing their own account. If so, determine the
     protections for e-mail, password and openid by examining the userprotect
     permissions for 'change own e-mail', 'change own password' and 'change own
     openid', then continue with the rest of the checks below.

  -- If not, check if the protection is set for the individual user being edited.
     If so, then stop the checks here, and prevent editing of the field (this
     effectively means that individual protections override role protections).

  -- If not, then examine all the roles for the user being edited. If any of those
     roles have the protection enabled, then prevent editing of the field.

  -  If not, then allow the field to be edited.

Note: If a user is editing their own account, they are never protected from editing
their own username, e-mail, password or OpenID. Administrators can still limit the
ability of users to change their username via the role-based permission at
administer -> user management -> access control.
