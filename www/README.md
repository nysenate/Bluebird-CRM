# www/

This directory holds the landing page for the bare CRM domain in a
multi-instance Bluebird environment.

Each Bluebird instance is served by Drupal (the `drupal/` directory) at its own
hostname: `<instance>.<base.domain>`, where `base.domain` is set in
`bluebird.cfg`, for example `sd99.crm.domain.gov`. The bare domain itself
(`crm.domain.gov`) isn't tied to any instance. Point that virtual host's
`DocumentRoot` at this directory so visitors get a simple landing page there.

## Contents

- `index.html`: the landing page.
- `.htaccess`: denies every request except `index.html` (and the directory
  root, which serves `index.html`). The virtual host needs `AllowOverride
  AuthConfig Indexes` (or `All`) for it to take effect. If you can't rely on
  `.htaccess`, put the same rules in the vhost's `<Directory>` block. The
  `.htaccess` doesn't set `Options -Indexes`, because that would also require
  `AllowOverride Options`. Leave `Indexes` off in the vhost so that a missing
  `index.html` gives a 403 instead of a file listing.

Keep this directory to static content only. Don't add scripts, tools or data
files here: the `.htaccess` blocks them anyway, and anything served from the bare
domain is public.
